<?php

namespace App\Classes;

use Illuminate\Support\Str;
use InvalidArgumentException;
use App\Enums\MobileNumberEnum;

class MobileNumber
{
    protected string $rawNumber;
    protected string $normalized;

    protected ?MobileNumberEnum $provider = null;

    public function __construct(string $number)
    {
        $this->rawNumber = $number;
        $this->normalized = $this->normalize($number);
        $this->provider = $this->detectProvider();
    }

    /** Create instance */
    public static function make(string $number): static
    {
        return new static($number);
    }

    /** Normalize number to local format (09xx...) */
    protected function normalize(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        if (Str::startsWith($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        } elseif (Str::startsWith($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        if (strlen($digits) !== 11) {
//            throw new InvalidArgumentException("Invalid PH mobile number: {$number}");
            return $digits == null ? 'N/A' : "Invalid PH mobile number: {$number}";
        }

        return $digits;
    }

    /** Detect provider (using your existing logic) */
    protected function detectProvider(): ?MobileNumberEnum
    {
        $providers = [
            MobileNumberEnum::SMART->value => ['0900', '0908', '0911', '0913', '0914', '0918', '0919', '0920', '0921', '0928', '0940', '0951', '0960', '0961', '0964', '0968', '0969', '0970', '0971', '0980', '0981'],
            MobileNumberEnum::TNT->value => ['0907', '0909', '0910', '0912', '0929', '0930', '0938', '0939', '0946', '0947', '0948', '0949', '0950', '0963', '0989'],
            MobileNumberEnum::SUN->value => ['0922', '0923', '0924', '0925', '0931', '0932', '0933', '0934', '0941', '0942', '0943', '0944', '0952', '0962', '0972', '0973', '0974'],
            MobileNumberEnum::GLOBE->value => ['0817', '0915', '0917', '0978', '0979', '0995', '09173', '09175', '09176', '09178', '09253', '09255', '09256', '09257', '09258'],
            MobileNumberEnum::GLOBE_TM->value => ['0904', '0905', '0906', '0916', '0926', '0927', '0935', '0936', '0945', '0955', '0956', '0957', '0958', '0959', '0965', '0966', '0967', '0975', '0977', '0997'],
            MobileNumberEnum::CHERRY->value => ['0996'],
            MobileNumberEnum::GOMO->value => ['0976'],
            MobileNumberEnum::DITO->value => ['0895', '0896', '0897', '0898', '0991', '0992', '0993', '0994'],
        ];

        foreach ($providers as $provider => $prefixes) {
            usort($prefixes, fn($a, $b) => strlen($b) - strlen($a));

            foreach ($prefixes as $prefix) {
                if (Str::startsWith($this->normalized, $prefix)) {
                    return MobileNumberEnum::from($provider);
                }
            }
        }

        return null;
    }

    /** Format: 0994 615 2760 */
    public function formatted(): string
    {
        return Str::of($this->normalized)
            ->replaceMatches('/^(\d{4})(\d{3})(\d{4})$/', '$1 $2 $3')
            ->toString();
    }

    /** +63 994 615 2760 */
    public function international(): string
    {
        return '+63 ' . substr($this->normalized, 1, 3) . ' ' . substr($this->normalized, 4, 3) . ' ' . substr($this->normalized, 7, 4);
    }

    /** Return provider enum */
    public function provider(): ?MobileNumberEnum
    {
        return $this->provider;
    }

    /** Return plain 09946152760 */
    public function raw(): string
    {
        return $this->normalized;
    }
}
