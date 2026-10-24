<?php

use App\Models\Quote;
use App\Models\User;

test('liking a quote saves the like and returns the new count and warmth', function () {
    $quote = Quote::factory()->create();
    $quote->likers()->attach(User::factory(9)->create());
    $reader = User::factory()->create();

    $this->actingAs($reader)
        ->postJson("/quotes/{$quote->id}/like")
        ->assertOk()
        ->assertExactJson(['liked' => true, 'likes' => 10, 'warmth' => 1]);

    $this->assertDatabaseHas('likes', ['user_id' => $reader->id, 'quote_id' => $quote->id]);
});

test('liking again takes the like back', function () {
    $quote = Quote::factory()->create();
    $reader = User::factory()->create();
    $quote->likers()->attach($reader);

    $this->actingAs($reader)
        ->postJson("/quotes/{$quote->id}/like")
        ->assertExactJson(['liked' => false, 'likes' => 0, 'warmth' => 0]);

    $this->assertDatabaseCount('likes', 0);
});

test('visitors must log in to like', function () {
    $quote = Quote::factory()->create();

    $this->postJson("/quotes/{$quote->id}/like")->assertUnauthorized();

    $this->assertDatabaseCount('likes', 0);
});

test('warmth grows with the number of likes', function (int $likes, int $warmth) {
    expect(Quote::warmthFor($likes))->toBe($warmth);
})->with([
    'no glow' => [9, 0],
    'warm' => [10, 1],
    'warmer' => [20, 2],
    'glowing' => [40, 3],
]);

test('the wall shows which quotes you already liked', function () {
    $quote = Quote::factory()->create();
    $reader = User::factory()->create();
    $quote->likers()->attach($reader);

    $this->actingAs($reader)->get('/')->assertSee('aria-pressed="true"', false);
});
