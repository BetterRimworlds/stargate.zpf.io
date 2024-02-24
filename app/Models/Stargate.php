<?php declare(strict_types=1);

namespace App\Models;

class Stargate
{
    // From https://thestargateproject.com/address-book/
    public const GLYPHS = [
        // Outer Row
        /*  1 */ 'f',
        /*  2 */ 'M',
        /*  3 */ 'S',
        /*  4 */ 'V',
        /*  5 */ 'G',
        /*  6 */ 'X',
        /*  7 */ 'F',
        /*  8 */ 'c',
        /*  9 */ 'l',
        /* 10 */ 'h',
        /* 11 */ 'L',
        /* 12 */ 'k',
        /* 13 */ 'K',
        /* 14 */ 'U',
        /* 15 */ 'C',
        /* 16 */ 'D',
        /* 17 */ 'T',
        /* 18 */ 'I',
        /* 19 */ 'E',

        // Inner Row
        /* 20 */ 'e',
        /* 21 */ 'O',
        /* 22 */ 'i',
        /* 23 */ 'd',
        /* 24 */ 'P',
        /* 25 */ 'b',
        /* 26 */ 'J',
        /* 27 */ 'g',
        /* 28 */ 'm',
        /* 29 */ 'Z',
        /* 30 */ 'W',
        /* 31 */ 'R',
        /* 32 */ 'N',
        /* 33 */ 'Q',
        /* 34 */ 'B',
        /* 35 */ 'Y',
        /* 36 */ 'j',
        /* 37 */ 'H',
        /* 38 */ 'a',
    ];

    public const KNOWN_GATES = [
        'feWLPB',
        'MOWLBC',
        'bZEjKc', /* Earth  */
        'aGOfLd', /* Abydos */
        'lbOiCS', /* NID    */
        'IBWOkT', /* Chulak */
        'FgakKR', /* Tollan */
        'CfPHJL', /* Destroyer of Worlds */
    ];

    public static function randomAddress(): string
    {
        $result = [];
        $arrayLength = count(self::GLYPHS);

        $pickedIndices = [];

        for ($a = 0; $a < 6; ++$a) {
            // Generate a random index
            $randomIndex = random_int(0, $arrayLength - 1);

            // Check if the index has already been used
            if (!in_array($randomIndex, $pickedIndices)) {
                $result[] = self::GLYPHS[$randomIndex]; // Add the item at the random index to the result
                $pickedIndices[] = $randomIndex; // Remember the index to avoid repetition
            }
        }

        return implode($result);
    }

    public static function registerStargate(): string
    {
        // Regenerate addresses until we find one that doesn't exist (probable) or is older than 30 days.
        do {
            $gateAddress = Stargate::randomAddress();
            $path = storage_path('gates');
            $filepath = "$path/registered/$gateAddress.txt";
        } while (file_exists($filepath) && filemtime($filepath) <= time() + 3600 * 24 * 30);

        file_put_contents($filepath, $gateAddress);
        //touch($filepath);

        return $gateAddress;
    }
}
