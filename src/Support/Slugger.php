<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Builds URL slugs ([a-z0-9-]) from Ukrainian/English text.
 * Transliteration follows the official Ukrainian standard (Cabinet of Ministers Resolution No. 55, 2010).
 */
final class Slugger
{
    // Letters transliterated differently at the start of a word.
    private const WORD_START = ['є' => 'ye', 'ї' => 'yi', 'й' => 'y', 'ю' => 'yu', 'я' => 'ya'];

    private const MAP = [
        // "зг" is a separate rule so it does not read as "zh" (ж).
        'зг' => 'zgh',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd', 'е' => 'e',
        'є' => 'ie', 'ж' => 'zh', 'з' => 'z', 'и' => 'y', 'і' => 'i', 'ї' => 'i', 'й' => 'i',
        'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch', 'ь' => '', 'ю' => 'iu', 'я' => 'ia',
    ];

    private const APOSTROPHES = ['\'', '’', 'ʼ'];

    public static function slugify(string $text): string
    {
        // Apostrophes are dropped first: a letter after one is not a word start ("Знам'янка" -> "znamianka").
        $text = str_replace(self::APOSTROPHES, '', mb_strtolower($text, 'UTF-8'));
        $text = preg_replace_callback(
            '/(?<!\p{L})[єїйюя]/u',
            static fn (array $m): string => self::WORD_START[$m[0]],
            $text,
        );
        // strtr() with an array tries the longest keys first, so "зг" wins over "з".
        $text = strtr($text, self::MAP);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        return trim($text, '-');
    }
}
