{{--
    Renders question or answer text that may contain inline LaTeX.

    The raw text is printed escaped; KaTeX typesets the $…$ spans in the browser
    once the element is picked up by the [data-math] renderer in partials/math.

    <x-math-text :value="$question->question_text" :limit="75" fallback="Untitled" />
--}}
@props(['value' => null, 'limit' => null, 'fallback' => ''])

@php
    $text = $limit
        ? \App\Support\Latex::preview($value, (int) $limit)
        : trim(strip_tags((string) $value));
@endphp

@php $shown = $text !== '' ? $text : $fallback; @endphp
<span {{ $attributes->merge(['class' => 'math-text']) }} data-math data-math-src="{{ $shown }}">{{ $shown }}</span>
