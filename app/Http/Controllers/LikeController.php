<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Warmth: liking and unliking quotes.
 */
class LikeController extends Controller
{
    /**
     * Like the quote if you haven't yet, or take the like back if you have.
     * The page's JavaScript asks for JSON so it can update the card without reloading.
     */
    public function toggle(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $result = $request->user()->likedQuotes()->toggle($quote);
        $liked = $result['attached'] !== [];
        $likes = $quote->likers()->count();

        if ($request->expectsJson()) {
            return response()->json([
                'liked' => $liked,
                'likes' => $likes,
                'warmth' => Quote::warmthFor($likes),
            ]);
        }

        return back();
    }
}
