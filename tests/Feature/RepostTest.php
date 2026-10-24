<?php

use App\Models\Quote;
use App\Models\User;

test('reposting a quote saves it and returns the new count', function () {
    $quote = Quote::factory()->create();
    $reader = User::factory()->create();

    $this->actingAs($reader)
        ->postJson("/quotes/{$quote->id}/repost")
        ->assertOk()
        ->assertExactJson(['reposted' => true, 'reposts' => 1]);

    $this->assertDatabaseHas('reposts', ['user_id' => $reader->id, 'quote_id' => $quote->id]);
});

test('reposting again takes the repost back', function () {
    $quote = Quote::factory()->create();
    $reader = User::factory()->create();
    $quote->reposters()->attach($reader);

    $this->actingAs($reader)
        ->postJson("/quotes/{$quote->id}/repost")
        ->assertExactJson(['reposted' => false, 'reposts' => 0]);
});

test('you cannot repost your own quote', function () {
    $quote = Quote::factory()->create();

    $this->actingAs($quote->user)->postJson("/quotes/{$quote->id}/repost")->assertForbidden();

    $this->assertDatabaseCount('reposts', 0);
});

test('visitors must log in to repost', function () {
    $quote = Quote::factory()->create();

    $this->postJson("/quotes/{$quote->id}/repost")->assertUnauthorized();
});

test('a repost brings an older quote back to the top of the wall, with who reposted it', function () {
    $older = Quote::factory()->create(['text' => 'Older quote.', 'created_at' => now()->subDays(3)]);
    Quote::factory()->create(['text' => 'Newer quote.', 'created_at' => now()->subDay()]);

    $older->reposters()->attach(User::factory()->create(['name' => 'Hind']));

    $this->get('/')->assertSeeInOrder(['Hind reposted', 'Older quote.', 'Newer quote.']);
});
