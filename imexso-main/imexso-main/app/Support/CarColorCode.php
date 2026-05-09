<?php

namespace App\Support;

/**
 * Maps XML code_couleur values (numeric strings) to stable group keys used for filtering and i18n.
 *
 * Empirical mapping from cars.xml samples:
 * 0 SANDSTONE, 1 BLANC, 2 NOIR, 3 GRIS, 4 BLEU, 5 ROUGE, 6 VERT (green tones), 8 JAUNE, 12 BEIGE.
 * Gaps (7, 9–11, 13–15) follow common French listing conventions where a code was not present in the feed.
 */
final class CarColorCode
{
    /**
     * @var array<string, string> numeric code => group key
     */
    private const CODE_TO_GROUP = [
        '0' => 'other',
        '1' => 'white',
        '2' => 'black',
        '3' => 'grey',
        '4' => 'blue',
        '5' => 'red',
        '6' => 'green',
        '7' => 'yellow',
        '8' => 'yellow',
        '9' => 'orange',
        '10' => 'brown',
        '11' => 'purple',
        '12' => 'beige',
        '13' => 'silver',
        '14' => 'gold',
        '15' => 'other',
    ];

    /**
     * Display order for filter UI (keys only).
     *
     * @var list<string>
     */
    private const GROUP_ORDER = [
        'white',
        'black',
        'grey',
        'silver',
        'blue',
        'red',
        'green',
        'yellow',
        'orange',
        'brown',
        'beige',
        'gold',
        'purple',
        'pink',
        'other',
    ];

    /**
     * @var array<string, string> group key => CSS hex (swatches)
     */
    private const GROUP_HEX = [
        'white' => '#F5F5F5',
        'black' => '#1A1A1A',
        'grey' => '#9E9E9E',
        'silver' => '#B0BEC5',
        'blue' => '#1E88E5',
        'red' => '#E53935',
        'green' => '#43A047',
        'yellow' => '#FDD835',
        'orange' => '#FB8C00',
        'brown' => '#6D4C41',
        'beige' => '#D7CCC8',
        'gold' => '#C9A227',
        'purple' => '#8E24AA',
        'pink' => '#EC407A',
        'other' => '#BDBDBD',
    ];

    /**
     * @var array<string, list<string>> group key => keywords matched against normalized color label (fallback when {@code color_code} is missing)
     */
    private const LABEL_KEYWORDS = [
        'white' => ['blanc', 'white', 'bianco', 'bianca', 'weiss', 'weiß', 'blanco'],
        'black' => ['noir', 'black', 'nero', 'nera', 'schwarz', 'negro'],
        'grey' => ['gris', 'grey', 'gray', 'grigio', 'grigia', 'grau', 'ecotronic grey'],
        'silver' => ['argent', 'silver', 'silber', 'plata', 'argenté'],
        'blue' => ['bleu', 'blue', 'blu', 'blau', 'azul'],
        'red' => ['rouge', 'red', 'rosso', 'rossa', 'rot', 'rojo'],
        'green' => ['vert', 'green', 'verde', 'grün', 'gruen', 'kaki', 'mangrove'],
        'yellow' => ['jaune', 'yellow', 'giallo', 'gialla', 'gelb', 'amarillo'],
        'orange' => ['orange', 'arancione', 'arancio', 'naranja'],
        'brown' => ['marron', 'brown', 'brun', 'marrone', 'braun', 'marrón'],
        'beige' => ['beige', 'crème', 'creme', 'cream', 'ivoire', 'ivory', 'safari'],
        'gold' => ['or', 'gold', 'oro', 'doré', 'dorado'],
        'purple' => ['violet', 'purple', 'viola', 'violett', 'lila', 'morado'],
        'pink' => ['rose', 'pink', 'rosa'],
    ];

    public static function hexForGroup(string $groupKey): string
    {
        return self::GROUP_HEX[$groupKey] ?? self::GROUP_HEX['other'];
    }

    /**
     * @return list<string>
     */
    public static function orderedGroupKeys(): array
    {
        return self::GROUP_ORDER;
    }

    public static function resolveGroupKey(?string $colorCode, ?string $colorLabel): string
    {
        $trimmedCode = $colorCode !== null ? trim($colorCode) : '';

        if ($trimmedCode !== '' && isset(self::CODE_TO_GROUP[$trimmedCode])) {
            return self::CODE_TO_GROUP[$trimmedCode];
        }

        if ($trimmedCode !== '' && ctype_digit($trimmedCode)) {
            return 'other';
        }

        return self::groupKeyFromColorName($colorLabel ?? '');
    }

    public static function groupKeyFromColorName(string $color): string
    {
        $normalized = mb_strtolower(trim($color));

        foreach (self::LABEL_KEYWORDS as $group => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return $group;
                }
            }
        }

        return 'other';
    }
}
