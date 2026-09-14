<?php

namespace App\Support;

/**
 * Helpers for question and answer text that carries inline LaTeX.
 *
 * The text is stored exactly as the author typed it - "What is $\sqrt{x}$?" -
 * and typeset in the browser by KaTeX, so nothing here renders maths. What it
 * does is keep previews honest: cutting a string at 80 characters can slice a
 * formula in half and leave a stray delimiter that would swallow the rest of
 * the sentence into one big broken equation.
 */
class Latex
{
    /** A single-line, length-capped preview that never splits a formula. */
    public static function preview($text, int $limit = 80): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        if ($limit <= 0 || mb_strlen($text) <= $limit) {
            return static::balance($text);
        }

        return static::balance(mb_substr($text, 0, $limit)) . '…';
    }

    /** Drops a trailing unclosed formula so no lone "$" survives a truncation. */
    public static function balance(string $text): string
    {
        // Escaped dollars (\$) are literal currency, not delimiters.
        if (preg_match_all('/(?<!\\\\)\$/u', $text) % 2 === 0) {
            return $text;
        }

        $last = mb_strrpos($text, '$');

        return $last === false ? $text : rtrim(mb_substr($text, 0, $last));
    }

    /** True when the text has at least one complete inline formula. */
    public static function hasMath($text): bool
    {
        return preg_match('/(?<!\\\\)\$[^$]+\$/u', (string) $text) === 1;
    }
}
