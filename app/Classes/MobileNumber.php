<?php

namespace App\Classes;

use Illuminate\Support\Str;
use InvalidArgumentException;

class MobileNumber
{
    public static function identifyProvider(string $mobile_number): ?string
    {
        // Normalize the phone number
        $normalized = Str::of($mobile_number)
            ->trim()
            ->replace(['+', '-', ' ', '(', ')'], '');

        // Convert to 09xx format for easier matching
        if ($normalized->startsWith('63')) {
            $normalized = Str::of('0' . $normalized->substr(2));
        } elseif ($normalized->startsWith('9') && $normalized->length() === 10) {
            $normalized = Str::of('0' . $normalized);
        }
        // Convert to 09xx format for easier matching
        if ($normalized->startsWith('63')) {
            $normalized = '0' . $normalized->substr(2);
        } elseif ($normalized->startsWith('9') && $normalized->length() === 10) {
            $normalized = '0' . $normalized;
        }

        $normalizedStr = $normalized->toString();

        $providers = [
            'Smart' => ['0900', '0908', '0911', '0913', '0914', '0918', '0919', '0920', '0921', '0928', '0940', '0951', '0960', '0961', '0964', '0968', '0969','0970', '0971', '0980', '0981'],
            'TNT' => ['0907', '0909', '0910', '0912', '0929', '0930', '0938', '0939', '0946', '0947', '0948', '0949', '0950', '0963', '0989'],
            'Sun' => ['0922', '0923', '0924', '0925', '0931', '0932', '0933', '0934', '0941', '0942', '0943', '0944', '0952', '0962', '0972', '0973', '0974'],
            'Globe' => ['0817', '0915', '0917', '0978', '0979', '0995', '09173', '09175', '09176', '09178', '09253', '09255', '09256', '09257', '09258'],
            'Globe or TM' => ['0904', '0905', '0906', '0916', '0926', '0927', '0935', '0936', '0945', '0955', '0956', '0957', '0958', '0959', '0965', '0966', '0967', '0975', '0977', '0997'],
            'Cherry' => ['0996'],
            'GOMO' => ['0976'],
            'DITO' => ['0895', '0896', '0897', '0898', '0991', '0992', '0993', '0994',
            ]
        ];

        foreach ($providers as $provider => $prefixes) {
            // Sort prefixes by length (longest first) to prioritize 5-digit matches
            usort($prefixes, fn($a, $b) => strlen($b) - strlen($a));

            foreach ($prefixes as $prefix) {
                if (Str::startsWith($normalizedStr, $prefix)) {
                    return $provider;
                }
            }
        }

        return null; // Unknown provider
    }
    public static function formatPHMobileNumber(string $number): array
    {
        // Remove non-digits
        $digits = preg_replace('/\D/', '', $number);

        // Handle country code
        if (Str::startsWith($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        }
        elseif (Str::startsWith($digits, '0')) {
            // already local format
        } elseif (strlen($digits) === 10 && Str::startsWith($digits, '9')) {
            // Missing leading 0, assume local
            $digits = '0' . $digits;
        } else {
            return [
                'local' => $number,
                'international' => $number,
            ];
        }

        // Check length AFTER adding the leading 0
        if (strlen($digits) !== 11) {
            throw new InvalidArgumentException("Invalid Philippine mobile number.");
        }

        $local = substr($digits, 0, 4) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 4);
        $international = '+63 ' . substr($digits, 1, 3) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 4);

        return [
            'local' => $local,
            'international' => $international,
        ];
    }
}
