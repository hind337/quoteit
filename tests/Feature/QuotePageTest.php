<?php

use App\Models\Quote;
use App\Models\User;

test('a quote has its own page', function () {
    $quote = Quote::factory()->for(User::factory()->state(['name' => 'Nora']))->create(['text' => 'Read slowly.']);

    $this->get("/quotes/{$quote->id}")->assertOk()->assertSee('Read slowly.')->assertSee('Nora');
});

test('related quotes come from the same book first, then the same topic', function () {
    $quote = Quote::factory()->create(['topic' => 'Work', 'source_title' => 'The Eye of Osiris']);

    $sameBook = Quote::factory()->create(['text' => 'Same book quote.', 'topic' => 'Hope', 'source_title' => 'The Eye of Osiris']);
    $sameTopic = Quote::factory()->create(['text' => 'Same topic quote.', 'topic' => 'Work']);
    Quote::factory()->create(['text' => 'Unrelated quote.', 'topic' => 'Life']);

    $related = $quote->related(null);

    expect($related->pluck('id')->all())->toBe([$sameBook->id, $sameTopic->id])
        ->and($related->first()->related_because)->toBe('Same book')
        ->and($related->last()->related_because)->toBe('Same topic');

    $this->get("/quotes/{$quote->id}")
        ->assertSeeInOrder(['Related quotes', 'Same book quote.', 'Same topic quote.'])
        ->assertDontSee('Unrelated quote.');
});

test('related quotes never include the quote itself and stop at three', function () {
    $quote = Quote::factory()->create(['topic' => 'Hope']);
    Quote::factory(5)->create(['topic' => 'Hope']);

    $related = $quote->related(null);

    expect($related)->toHaveCount(3)
        ->and($related->pluck('id'))->not->toContain($quote->id);
});

test('anyone can open the wallpaper maker for a quote', function () {
    $quote = Quote::factory()->create(['text' => 'Small steps.', 'source_title' => 'Notes']);

    $this->get("/quotes/{$quote->id}/wallpaper")
        ->assertOk()
        ->assertSee('data-text="Small steps."', false)
        ->assertSee('Show the source under the quote');
});

test('a quote that does not exist shows a 404 page', function () {
    $this->get('/quotes/999')->assertNotFound();
});
