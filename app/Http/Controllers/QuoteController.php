<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The wall of quotes: show them all, share a new one, edit or delete your own.
 */
class QuoteController extends Controller
{
    /**
     * The home page: the quote of the day and the wall.
     */
    public function index(Request $request): View
    {
        return view('welcome', [
            'page' => 'Home',
            'quotes' => Quote::forTheWall($request->user())->get(),
            'quoteOfTheDay' => Quote::ofTheDay(),
            'topics' => Quote::TOPICS,
            // ?edit=5 opens quote 5 for editing (only shown if it's yours)
            'editingId' => $request->integer('edit'),
        ]);
    }

    /**
     * One quote on its own page, with related quotes underneath.
     */
    public function show(Request $request, Quote $quote): View
    {
        $quote = Quote::withCardDetails($request->user())->findOrFail($quote->id);

        return view('quotes.show', [
            'page' => 'Quote',
            'quote' => $quote,
            'relatedQuotes' => $quote->related($request->user()),
        ]);
    }

    /**
     * Turn a quote into a phone wallpaper (the drawing happens in the browser, in app.js).
     */
    public function wallpaper(Quote $quote): View
    {
        return view('quotes.wallpaper', [
            'page' => 'Wallpaper',
            'quote' => $quote->load('user:id,name'),
        ]);
    }

    /**
     * Share a new quote.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->user()->quotes()->create($this->validateQuote($request, 'share'));

        return redirect()->to(route('home').'#wall')->with('status', 'Your quote is on the wall!');
    }

    /**
     * Save changes to one of your own quotes.
     */
    public function update(Request $request, Quote $quote): RedirectResponse
    {
        Gate::authorize('update', $quote);

        $quote->update($this->validateQuote($request, 'edit'));

        return redirect()->to(route('home').'#wall')->with('status', 'Your quote was updated.');
    }

    /**
     * Delete one of your own quotes.
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        Gate::authorize('delete', $quote);

        $quote->delete();

        return redirect()->to(route('home').'#wall')->with('status', 'Your quote was deleted.');
    }

    /**
     * The same rules for sharing and editing.
     * $errorBag keeps the errors of the "share" pop-up apart from the inline edit form.
     *
     * @return array{text: string, topic: string, source_title: ?string, source_author: ?string}
     */
    private function validateQuote(Request $request, string $errorBag): array
    {
        return $request->validateWithBag($errorBag, [
            'text' => ['required', 'string', 'min:3', 'max:155'],
            'topic' => ['required', Rule::in(Quote::TOPICS)],
            'source_title' => ['nullable', 'string', 'max:120'],
            'source_author' => ['nullable', 'string', 'max:80'],
        ], [
            'text.min' => 'A quote needs at least 3 characters.',
            'text.max' => 'Keep it short: 155 characters at most.',
            'topic.required' => 'Choose a topic.',
        ]);
    }
}
