<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Offer;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class OfferPdfService
{
    public function generate(Offer $offer, Car $car, User $user): string
    {
        $car->load(['photos', 'options']);

        $logoBase64 = null;
        $logoMime = null;

        if ($user->logo && Storage::disk('public')->exists($user->logo)) {
            $logoBase64 = base64_encode(Storage::disk('public')->get($user->logo));
            $logoMime = Storage::disk('public')->mimeType($user->logo);
        }

        $frontendBase = config('imexso.frontend_url');
        if ($frontendBase === null || $frontendBase === '') {
            $frontendBase = config('app.url');
        }

        $data = [
            'offer' => $offer,
            'car' => $car,
            'user' => $user,
            'photos' => $car->photos,
            'options' => $car->options,
            'standardEquipment' => $car->standard_equipment ?? [],
            'supplementaryEquipment' => $car->supplementary_equipment ?? [],
            'logoBase64' => $logoBase64,
            'logoMime' => $logoMime,
            'frontendContactUrl' => rtrim((string) $frontendBase, '/').'/contact',
        ];

        $pdf = Pdf::loadView('pdf.offer', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'offers/'.$offer->id.'_'.time().'.pdf';

        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * @return array{price_excl_vat: float, price_incl_vat: float}
     */
    public static function calculatePrices(
        float $basePrice,
        ?string $marginType,
        ?float $marginAmount,
        float $vatRate
    ): array {
        $priceExclVat = $basePrice;

        if ($marginType && $marginAmount) {
            if ($marginType === 'percentage') {
                $priceExclVat += ($basePrice * $marginAmount / 100);
            } else {
                $priceExclVat += $marginAmount;
            }
        }

        $priceInclVat = $vatRate > 0
            ? $priceExclVat + ($priceExclVat * $vatRate / 100)
            : $priceExclVat;

        return [
            'price_excl_vat' => round($priceExclVat, 2),
            'price_incl_vat' => round($priceInclVat, 2),
        ];
    }
}
