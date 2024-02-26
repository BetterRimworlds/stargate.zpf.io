<?php declare(strict_types=1);

namespace App\Models;

use App\Exceptions\InvalidStargateAddressException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class Stargate
{
    // From https://thestargateproject.com/address-book/
    public const GLYPHS = [
        // Outer Row
        /*  1 */ 'f', /** Monoceros */
        /*  2 */ 'M', /** Aquila */
        /*  3 */ 'S', /** Pegasus */
        /*  4 */ 'V', /** Andromeda */
        /*  5 */ 'G', /** Serpens Capu */
        /*  6 */ 'X', /** Aries */
        /*  7 */ 'F', /** Libra */
        /*  8 */ 'c', /** Eridanus */
        /*  9 */ 'A', /** Leo */
        /* 10 */ 'h', /** Hydra */
        /* 11 */ 'L', /** Sagittarius */
        /* 12 */ 'k', /** Sextans */
        /* 13 */ 'K', /** Scutum */
        /* 14 */ 'U', /** Pisces */
        /* 15 */ 'C', /** Virgo */
        /* 16 */ 'D', /** Boötes */
        /* 17 */ 'T', /** Sculptor */
        /* 18 */ 'I', /** Scorpius */
        /* 19 */ 'E', /** Centaurus */

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
        'daQZcT',
        'jeNZJd',
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

    public static function assertIsValidStargateAddress(string $address, bool $isIntergalactic = false): bool
    {
        $LENGTH = $isIntergalactic === false ? 6 : 7;
        // 1. Too short.
        if (strlen($address) < $LENGTH) {
            throw new InvalidStargateAddressException("Not a valid Stargate address: Too short (req: $LENGTH symbols).");
        }

        // 2. Too long.
        if (strlen($address) > $LENGTH) {
            throw new InvalidStargateAddressException("Not a valid Stargate address: Too long (req: $LENGTH symbols).");
        }

        // 3. Ensure only valid characters.
        $tmpGate = str_replace(self::GLYPHS, '', $address);
        if ($tmpGate !== '') {
            throw new InvalidStargateAddressException("Not a valid Stargate address: Too long (Invalid symbols).");
        }

        // 4. Ensure no duplicate glyph symbols.
        $letterCounts = array_count_values(str_split($address));
        rsort($letterCounts, SORT_NUMERIC);
        if (($letterCounts[0] ?? null) !== 1) {
            throw new InvalidStargateAddressException("Not a valid Stargate address: A symbol was repeated {$letterCounts[0]} times.");
        }

        return true;
    }

    /**
     * @param string $address
     * @return void
     * @throws FileNotFoundException
     * @throws InvalidStargateAddressException
     */
    public static function publish(string $address): void
    {
        self::assertIsValidStargateAddress($address);

        $path = storage_path('gates');

        // See if the gate is registered.
        if (!file_exists("$path/registered/$address.txt"))
        {
            throw new FileNotFoundException("That Stargate is not registered");
        }

        $knownGatesFile = "$path/known.json";
        $knownGates = json_decode(file_get_contents($knownGatesFile));

        if (($index = array_search($address, $knownGates)) !== false) {
            $index += 1;
            throw new \InvalidArgumentException("That Stargate has already been published (#$index).");
        }

        $knownGates[] = $address;
        file_put_contents($knownGatesFile, json_encode($knownGates));
    }
}
