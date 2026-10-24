<?php

namespace App\Models;

use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['text', 'topic', 'source_title', 'source_author'])]
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    /**
     * The topics a quote can belong to (used by the filter on the wall).
     *
     * @var list<string>
     */
    public const TOPICS = ['Habits', 'Learning', 'Work', 'Hope', 'Life'];

    /**
     * The person who shared the quote.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The people who liked the quote.
     *
     * @return BelongsToMany<User, $this>
     */
    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    /**
     * The people who reposted the quote, newest first.
     *
     * @return BelongsToMany<User, $this>
     */
    public function reposters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reposts')->withTimestamps()->latest('reposts.created_at');
    }

    /**
     * Warmth on the wall: 0 (no glow) to 3 (glowing), based on the number of likes.
     */
    public static function warmthFor(int $likes): int
    {
        return match (true) {
            $likes >= 40 => 3,
            $likes >= 20 => 2,
            $likes >= 10 => 1,
            default => 0,
        };
    }

    /**
     * Load what each card on the wall needs: the author, the like and repost counts,
     * who reposted it, and whether the visitor already liked or reposted it.
     *
     * @param  Builder<Quote>  $query
     */
    public function scopeWithCardDetails(Builder $query, ?User $visitor): void
    {
        $query->with(['user:id,name', 'reposters:users.id,name'])
            ->withCount(['likers', 'reposters'])
            ->withExists([
                'likers as liked_by_visitor' => fn (Builder $likers) => $likers->whereKey($visitor?->id),
                'reposters as reposted_by_visitor' => fn (Builder $reposters) => $reposters->whereKey($visitor?->id),
            ]);
    }

    /**
     * The wall: every quote once, newest activity first.
     * A fresh repost brings an older quote back to the top.
     *
     * @param  Builder<Quote>  $query
     */
    public function scopeForTheWall(Builder $query, ?User $visitor): void
    {
        $query->withCardDetails($visitor)
            ->withMax('reposters as last_reposted_at', 'reposts.created_at')
            ->orderByRaw('coalesce(last_reposted_at, quotes.created_at) desc')
            ->latest('id');
    }

    /**
     * Up to three related quotes: from the same book first, then the same author,
     * then the same topic; the most liked ones win ties.
     * Each one gets a "related_because" label to show on the card.
     *
     * @return Collection<int, Quote>
     */
    public function related(?User $visitor, int $limit = 3): Collection
    {
        $reasons = fn (Quote $other): array => array_filter([
            'Same book' => $this->source_title && $other->source_title === $this->source_title ? 3 : 0,
            'Same author' => $this->source_author && $other->source_author === $this->source_author ? 2 : 0,
            'Same topic' => $other->topic === $this->topic ? 1 : 0,
        ]);

        return static::query()
            ->withCardDetails($visitor)
            ->whereKeyNot($this->id)
            ->where(function (Builder $matches) {
                $matches->where('topic', $this->topic)
                    ->when($this->source_title, fn (Builder $q) => $q->orWhere('source_title', $this->source_title))
                    ->when($this->source_author, fn (Builder $q) => $q->orWhere('source_author', $this->source_author));
            })
            ->limit(50)
            ->get()
            ->sortByDesc(fn (Quote $other) => [array_sum($reasons($other)), $other->likers_count])
            ->take($limit)
            ->each(fn (Quote $other) => $other->setAttribute('related_because', array_key_first($reasons($other))))
            ->values();
    }

    /**
     * Quote of the day: the quote that got the most likes yesterday.
     * If nobody liked anything yesterday, the most liked quote overall.
     */
    public static function ofTheDay(): ?self
    {
        return static::query()
            ->with('user:id,name')
            ->withCount([
                'likers as likes_yesterday' => fn (Builder $likes) => $likes->whereBetween('likes.created_at', [
                    now()->subDay()->startOfDay(),
                    now()->subDay()->endOfDay(),
                ]),
                'likers',
            ])
            ->orderByDesc('likes_yesterday')
            ->orderByDesc('likers_count')
            ->latest('id')
            ->first();
    }
}
