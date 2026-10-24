{{--
    The fields of a quote: the text, a topic, and an optional source.
    Shared by the "Share a quote" pop-up (bag="share") and the edit form (bag="edit").
    "bag" keeps each form's error messages separate.
--}}
@props(['quote' => null, 'bag' => 'default'])

@php
    $errorsHere = $errors->getBag($bag);
    // After a failed save show what the person typed; otherwise the saved quote (when editing)
    $value = fn (string $field) => $errorsHere->any() ? old($field) : $quote?->{$field};
    $chosenTopic = $value('topic') ?? App\Models\Quote::TOPICS[0];
    $id = fn (string $field) => "{$bag}-{$field}";
@endphp

<div>
    <label for="{{ $id('text') }}" class="label {{ $quote ? 'text-on-ember' : '' }}">Your quote</label>
    <textarea id="{{ $id('text') }}" name="text" rows="4" required minlength="3" maxlength="155"
        placeholder="Write your quote…"
        class="input peer resize-none font-serif text-xl italic user-invalid:border-red-500">{{ $value('text') }}</textarea>
    @if ($errorsHere->has('text'))
        <p class="mt-1 text-xs font-semibold text-red-600 night:text-red-400">{{ $errorsHere->first('text') }}</p>
    @else
        <p class="hint {{ $quote ? 'text-on-ember/80' : '' }}">3–155 characters.</p>
    @endif
</div>

<fieldset>
    <legend class="label {{ $quote ? 'text-on-ember' : '' }}">Topic</legend>
    <div class="flex flex-wrap gap-2">
        @foreach (App\Models\Quote::TOPICS as $topic)
            <label class="cursor-pointer">
                <input type="radio" name="topic" value="{{ $topic }}" class="peer sr-only" @checked($chosenTopic === $topic)>
                <span class="inline-flex h-9 items-center rounded-full border border-line bg-field px-4 text-sm font-semibold text-ink transition peer-checked:border-gold peer-checked:bg-gold peer-checked:text-dusk peer-focus-visible:ring-4 peer-focus-visible:ring-gold/40">
                    {{ $topic }}
                </span>
            </label>
        @endforeach
    </div>
    @if ($errorsHere->has('topic'))
        <p class="mt-1 text-xs font-semibold text-red-600 night:text-red-400">{{ $errorsHere->first('topic') }}</p>
    @endif
</fieldset>

<fieldset class="rounded-2xl border border-line p-4 {{ $quote ? 'border-on-ember/40' : '' }}">
    <legend class="px-1 text-sm font-semibold {{ $quote ? 'text-on-ember' : '' }}">
        Source <span class="font-normal opacity-75">(optional)</span>
    </legend>
    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label for="{{ $id('source_title') }}" class="mb-1 block text-xs {{ $quote ? 'text-on-ember/90' : 'text-muted' }}">Book or work</label>
            <input id="{{ $id('source_title') }}" name="source_title" type="text" maxlength="120"
                value="{{ $value('source_title') }}" class="input py-2 text-sm">
        </div>
        <div>
            <label for="{{ $id('source_author') }}" class="mb-1 block text-xs {{ $quote ? 'text-on-ember/90' : 'text-muted' }}">Author</label>
            <input id="{{ $id('source_author') }}" name="source_author" type="text" maxlength="80"
                value="{{ $value('source_author') }}" class="input py-2 text-sm">
        </div>
    </div>
    <p class="mt-2 text-xs {{ $quote ? 'text-on-ember/80' : 'text-muted' }}">Shown in small text under your quote.</p>
</fieldset>
