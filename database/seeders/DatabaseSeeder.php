<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Fill the wall with sample quotes and likes, so it isn't empty on a fresh install.
     * Run it with:  php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // An account you can log in with: test@example.com / password
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // People who will "like" the sample quotes
        $readers = User::factory(45)->create();

        $samples = [
            ['Lujain', 'The best way to predict the future is to invent it.', 'Work', null, 'Alan Kay', 23, 2],
            ['Sara', 'Simplicity is the soul of efficiency.', 'Work', 'The Eye of Osiris', 'R. Austin Freeman', 41, 12],
            ['Reem', 'Small steps every day still count as moving forward.', 'Habits', null, null, 8, 1],
            ['Faisal', 'Ask one more question than you think you should.', 'Learning', null, null, 9, 0],
            ['Hind', 'Even the longest night is only waiting for the sun.', 'Hope', null, null, 19, 3],
            ['Nora', 'Read slowly. Some lines are meant to stay with you for years.', 'Learning', null, null, 11, 0],
        ];

        foreach ($samples as $position => [$name, $text, $topic, $sourceTitle, $sourceAuthor, $likes, $likedYesterday]) {
            $author = User::factory()->create(['name' => $name]);

            $quote = $author->quotes()->create([
                'text' => $text,
                'topic' => $topic,
                'source_title' => $sourceTitle,
                'source_author' => $sourceAuthor,
            ]);
            // Spread the sample quotes over the last few days
            $quote->forceFill(['created_at' => now()->subDays(6 - $position)])->save();

            // The first few likes happened yesterday (they decide the quote of the day), the rest earlier
            $readers->take($likes)->values()->each(function (User $reader, int $index) use ($quote, $likedYesterday) {
                $likedAt = $index < $likedYesterday ? now()->subDay()->setTime(12, 0) : now()->subDays(3);

                $quote->likers()->attach($reader, ['created_at' => $likedAt, 'updated_at' => $likedAt]);
            });
        }

        // A few reposts: Hind's quote was reposted this morning, so it sits at the top of the wall
        $hindsQuote = User::firstWhere('name', 'Hind')->quotes()->first();
        $hindsQuote->reposters()->attach($readers->take(3), ['created_at' => now()->startOfDay()->addHours(8)]);
    }
}
