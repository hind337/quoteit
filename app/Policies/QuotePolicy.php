<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

/**
 * Who may change a quote: only the person who shared it.
 */
class QuotePolicy
{
    /**
     * Only the owner can edit their quote.
     */
    public function update(User $user, Quote $quote): bool
    {
        return $quote->user()->is($user);
    }

    /**
     * Only the owner can delete their quote.
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $quote->user()->is($user);
    }
}
