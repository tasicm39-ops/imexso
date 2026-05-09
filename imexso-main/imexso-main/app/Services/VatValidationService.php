<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VatValidationService
{
    private const VIES_API_URL = 'https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number';

    public const VAT_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES',
        'FI', 'FR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
        'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
    ];

    /**
     * @return array{valid: bool, company_name: string|null, address: string|null, postal_code: string|null, city: string|null, country_code: string, vat_number: string|null}
     */
    public function validate(string $countryCode, string $vatNumber): array
    {
        $vatNumber = $this->sanitize($vatNumber);

        try {
            $response = Http::timeout(15)
                ->post(self::VIES_API_URL, [
                    'countryCode' => $countryCode,
                    'vatNumber' => $vatNumber,
                ]);

            if (! $response->successful()) {
                return $this->invalidResult($countryCode);
            }

            $data = $response->json();
            $parsed = $this->parseAddress($data['address'] ?? '');

            return [
                'valid' => $data['valid'] ?? false,
                'company_name' => $data['name'] ?? null,
                'address' => $parsed['street'],
                'postal_code' => $parsed['postal_code'],
                'city' => $parsed['city'],
                'country_code' => $data['countryCode'] ?? $countryCode,
                'vat_number' => $data['vatNumber'] ?? $vatNumber,
            ];
        } catch (ConnectionException $e) {
            Log::warning('VIES API connection failed', ['error' => $e->getMessage()]);

            return $this->invalidResult($countryCode);
        }
    }

    private function sanitize(string $vatNumber): string
    {
        return preg_replace('/[\.\-\s]/', '', trim($vatNumber));
    }

    /**
     * VIES addresses typically come as "STREET\nPOSTAL CITY" or "STREET POSTAL CITY".
     *
     * @return array{street: string|null, postal_code: string|null, city: string|null}
     */
    private function parseAddress(string $raw): array
    {
        $raw = trim(str_replace("\n", ', ', $raw));

        if ($raw === '' || $raw === '---') {
            return ['street' => null, 'postal_code' => null, 'city' => null];
        }

        if (preg_match('/^(.+?),?\s+(\d{4,6})\s+(.+)$/', $raw, $m)) {
            return [
                'street' => trim($m[1], ', '),
                'postal_code' => $m[2],
                'city' => trim($m[3]),
            ];
        }

        return ['street' => $raw, 'postal_code' => null, 'city' => null];
    }

    /**
     * @return array{valid: bool, company_name: null, address: null, postal_code: null, city: null, country_code: string, vat_number: null}
     */
    private function invalidResult(string $countryCode): array
    {
        return [
            'valid' => false,
            'company_name' => null,
            'address' => null,
            'postal_code' => null,
            'city' => null,
            'country_code' => $countryCode,
            'vat_number' => null,
        ];
    }
}
