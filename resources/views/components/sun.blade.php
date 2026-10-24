{{--
    The QuoteIt sun character.
    Use it anywhere:  <x-sun />   or   <x-sun mood="awake" class="w-64" />
    Moods: asleep (default) · awake · shut · peek
--}}
@props(['mood' => 'asleep'])

@php
    /**
     * Builds the wavy flame-shaped rays around the sun as one SVG path.
     * Every second ray is longer, like the rays in the painting.
     */
    $buildRays = function (int $count, float $base, float $long, float $short, bool $shifted): string {
        $point = fn (float $radius, float $angle): string => round(200 + cos($angle) * $radius, 1) . ' ' . round(200 + sin($angle) * $radius, 1);
        $path = '';

        for ($i = 0; $i < $count; $i++) {
            $angle = ($i / $count) * 2 * M_PI + ($shifted ? M_PI / $count : 0);
            $tip = $i % 2 === 0 ? $long : $short;
            $middle = ($base + $tip) / 2;

            $path .= 'M' . $point($base, $angle - 0.17)
                . 'Q' . $point($middle, $angle - 0.02) . ' ' . $point($tip, $angle + 0.06)
                . 'Q' . $point($middle - 6, $angle + 0.2) . ' ' . $point($base, $angle + 0.17) . 'Z';
        }

        return $path;
    };

    $outerRays = $buildRays(12, 86, 196, 168, false);
    $innerRays = $buildRays(12, 85, 146, 134, true);
@endphp

<svg {{ $attributes->merge(['class' => 'sun']) }} data-mood="{{ $mood }}" viewBox="0 0 400 400" role="img"
    aria-label="The QuoteIt sun">
    <g class="sun-rays"><path d="{{ $outerRays }}" fill="#e2661a" /></g>
    <g class="sun-rays sun-rays-inner"><path d="{{ $innerRays }}" fill="#f29a2e" /></g>

    <g class="sun-face">
        {{-- Face --}}
        <circle cx="200" cy="200" r="95" fill="#e98a1f" />
        <circle cx="200" cy="200" r="88" fill="#f6b73c" />
        <circle cx="187" cy="183" r="63" fill="#f9c95a" opacity=".5" />

        {{-- Eyebrows --}}
        <path d="M151 171q16-9 32-1M217 169q16-7 32 1" fill="none" stroke="#b5520f" stroke-width="2.6"
            stroke-linecap="round" />

        {{-- Eyes: asleep (closed with lashes) --}}
        <g class="sun-part sun-asleep" fill="none" stroke="#5a3c0a" stroke-width="2.9" stroke-linecap="round">
            <path d="M152 194q14 10 29 0M218 194q14 10 29 0" />
            <path d="M156 198l-3 5M166 201v6M176 198l3 5M222 198l-3 5M232 201v6M242 198l3 5" stroke-width="1.8" />
        </g>

        {{-- Eyes: awake (open, looking right toward the form) --}}
        <g class="sun-part sun-awake">
            <ellipse cx="166" cy="194" rx="12" ry="9" fill="#fff4e0" />
            <ellipse cx="232" cy="194" rx="12" ry="9" fill="#fff4e0" />
            <circle cx="171" cy="195" r="5" fill="#3d2b08" />
            <circle cx="237" cy="195" r="5" fill="#3d2b08" />
            <circle cx="172.5" cy="193" r="1.6" fill="#ffffff" />
            <circle cx="238.5" cy="193" r="1.6" fill="#ffffff" />
            <path d="M153 188q13-9 26 0M219 188q13-9 26 0" fill="none" stroke="#5a3c0a" stroke-width="2.6"
                stroke-linecap="round" />
        </g>

        {{-- Eyes: shut tight (while typing a password) --}}
        <g class="sun-part sun-shut" fill="none" stroke="#5a3c0a" stroke-width="3.2" stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M153 198l13-7 13 7M219 198l13-7 13 7" />
        </g>

        {{-- Eyes: peek (one eye open when the password is shown) --}}
        <g class="sun-part sun-peek">
            <path d="M153 198l13-7 13 7" fill="none" stroke="#5a3c0a" stroke-width="3.2" stroke-linecap="round"
                stroke-linejoin="round" />
            <ellipse cx="232" cy="194" rx="12" ry="9" fill="#fff4e0" />
            <circle cx="237" cy="196" r="5" fill="#3d2b08" />
            <path d="M219 187q13-10 26 0" fill="none" stroke="#5a3c0a" stroke-width="2.6" stroke-linecap="round" />
        </g>

        {{-- Cheeks and nose --}}
        <circle class="sun-cheek" cx="157" cy="223" r="13" fill="#e8604a" opacity=".42" />
        <circle class="sun-cheek" cx="243" cy="223" r="13" fill="#e8604a" opacity=".42" />
        <path d="M200 199q-6 16 0 22q4 2 7-1.5" fill="none" stroke="#b5520f" stroke-width="2.2"
            stroke-linecap="round" />

        {{-- Mouths, one for each mood --}}
        <g fill="#c8452a" stroke="#a8361f" stroke-width="1.5" stroke-linejoin="round">
            <path class="sun-part sun-asleep" d="M189 237q11 7 23 0q-11 3-23 0Z" />
            <path class="sun-part sun-awake" d="M186 234q14 16 29 0q-14 4-29 0Z" />
            <path class="sun-part sun-shut" d="M192 239q8 3 17 0q-8 1.5-17 0Z" />
            <path class="sun-part sun-peek" d="M190 237q7 7 19 1.5q-9 1.5-19-1.5Z" />
        </g>
    </g>
</svg>
