<?php

use App\Models\Quote;
use App\Models\User;

describe('reading the wall', function () {
    test('visitors can read everyone\'s quotes with the author, date, topic and source', function () {
        Quote::factory()
            ->for(User::factory()->state(['name' => 'Lujain']))
            ->create([
                'created_at' => '2026-10-12 09:00:00',
                'text' => 'Simplicity is the soul of efficiency.',
                'topic' => 'Work',
                'source_title' => 'The Eye of Osiris',
                'source_author' => 'R. Austin Freeman',
            ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Simplicity is the soul of efficiency.')
            ->assertSee('Lujain')
            ->assertSee('The Eye of Osiris · R. Austin Freeman')
            ->assertSee('Oct 12, 2026');
    });

    test('visitors do not get edit or delete buttons', function () {
        Quote::factory()->create();

        $this->get('/')->assertDontSee('Edit quote')->assertDontSee('Delete quote');
    });

    test('quote text is escaped, so nobody can inject HTML', function () {
        Quote::factory()->create(['text' => '<script>alert(1)</script>']);

        $this->get('/')->assertDontSee('<script>alert(1)</script>', false);
    });
});

describe('sharing a quote', function () {
    test('a logged-in user can share a quote with a topic and a source', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/quotes', [
            'text' => 'Read slowly.',
            'topic' => 'Learning',
            'source_title' => 'Notes',
            'source_author' => 'Nora',
        ])->assertRedirect();

        $this->assertDatabaseHas('quotes', [
            'user_id' => $user->id,
            'text' => 'Read slowly.',
            'topic' => 'Learning',
            'source_title' => 'Notes',
            'source_author' => 'Nora',
        ]);
    });

    test('the source is optional', function () {
        $this->actingAs(User::factory()->create())
            ->post('/quotes', ['text' => 'Read slowly.', 'topic' => 'Learning'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('quotes', ['text' => 'Read slowly.', 'source_title' => null]);
    });

    test('visitors must log in before sharing', function () {
        $this->post('/quotes', ['text' => 'Read slowly.', 'topic' => 'Learning'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('quotes', 0);
    });

    test('invalid quotes are rejected', function (array $input, string $field) {
        $this->actingAs(User::factory()->create())
            ->post('/quotes', ['text' => 'Read slowly.', 'topic' => 'Learning', ...$input])
            ->assertSessionHasErrorsIn('share', $field);

        $this->assertDatabaseCount('quotes', 0);
    })->with([
        'text too short' => [['text' => 'Hi'], 'text'],
        'text too long' => [['text' => str_repeat('a', 156)], 'text'],
        'unknown topic' => [['topic' => 'Gossip'], 'topic'],
        'source title too long' => [['source_title' => str_repeat('a', 121)], 'source_title'],
    ]);
});

describe('editing and deleting', function () {
    test('the owner can edit their quote', function () {
        $quote = Quote::factory()->create(['text' => 'Old words.']);

        $this->actingAs($quote->user)->put("/quotes/{$quote->id}", [
            'text' => 'New words.',
            'topic' => 'Hope',
        ])->assertRedirect();

        expect($quote->fresh())->text->toBe('New words.')->topic->toBe('Hope');
    });

    test('the owner can delete their quote', function () {
        $quote = Quote::factory()->create();

        $this->actingAs($quote->user)->delete("/quotes/{$quote->id}")->assertRedirect();

        $this->assertModelMissing($quote);
    });

    test('nobody else can edit or delete someone\'s quote', function () {
        $quote = Quote::factory()->create(['text' => 'Mine.']);
        $someoneElse = User::factory()->create();

        $this->actingAs($someoneElse)
            ->put("/quotes/{$quote->id}", ['text' => 'Changed!', 'topic' => 'Hope'])
            ->assertForbidden();
        $this->actingAs($someoneElse)->delete("/quotes/{$quote->id}")->assertForbidden();

        expect($quote->fresh()->text)->toBe('Mine.');
    });

    test('the owner sees edit and delete buttons on their own quote only', function () {
        $mine = Quote::factory()->create();
        Quote::factory()->create();

        $page = $this->actingAs($mine->user)->get('/');

        expect(substr_count($page->content(), 'aria-label="Edit quote"'))->toBe(1);
    });
});

describe('quote of the day', function () {
    test('it is the quote with the most likes yesterday', function () {
        $popularEarlier = Quote::factory()->create(['text' => 'Popular last week.']);
        $popularYesterday = Quote::factory()->create(['text' => 'Popular yesterday.']);

        $popularEarlier->likers()->attach(User::factory(5)->create(), ['created_at' => now()->subWeek()]);
        $popularYesterday->likers()->attach(User::factory(2)->create(), ['created_at' => now()->subDay()]);

        expect(Quote::ofTheDay()->is($popularYesterday))->toBeTrue();
    });

    test('without likes yesterday it is the most liked quote overall', function () {
        $quiet = Quote::factory()->create();
        $loved = Quote::factory()->create();
        $loved->likers()->attach(User::factory(3)->create(), ['created_at' => now()->subWeek()]);

        expect(Quote::ofTheDay()->is($loved))->toBeTrue()
            ->and(Quote::ofTheDay()->is($quiet))->toBeFalse();
    });

    test('the home page works when there are no quotes yet', function () {
        $this->get('/')->assertOk()->assertSee('The wall is waiting for its first line.');
    });
});
