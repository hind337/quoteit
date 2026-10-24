<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Reposting: share someone else's quote with your name on it,
 * which brings it back to the top of the wall.
 */
class RepostController extends Controller
{
    /**
     * Repost the quote, or take the repost back if you already did.
     */
    public function toggle(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        // Reposting your own quote makes no sense
        abort_if($quote->user()->is($request->user()), 403, 'You cannot repost your own quote.');

        $result = $request->user()->repostedQuotes()->toggle($quote);
        $reposted = $result['attached'] !== [];
        $reposts = $quote->reposters()->count();

        if ($request->expectsJson()) {
            return response()->json(['reposted' => $reposted, 'reposts' => $reposts]);
        }

        return back();
    }
}
