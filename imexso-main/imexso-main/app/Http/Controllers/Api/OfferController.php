<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendOfferRequest;
use App\Mail\OfferMail;
use App\Models\Car;
use App\Models\Offer;
use App\Models\User;
use App\Services\OfferPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OfferController extends Controller
{
    public function __construct(
        private OfferPdfService $pdfService,
    ) {}

    public function generatePdf(SendOfferRequest $request, Car $car): BinaryFileResponse
    {
        $user = $request->user();

        $offer = $this->createOffer($request, $car, $user, 'pdf');

        $pdfPath = $this->pdfService->generate($offer, $car, $user);
        $offer->update(['pdf_path' => $pdfPath]);

        $fullPath = Storage::disk('local')->path($pdfPath);

        return response()->download($fullPath, 'Devis_'.$car->make.'_'.$car->model.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function sendEmail(SendOfferRequest $request, Car $car): JsonResponse
    {
        $user = $request->user();

        $offer = $this->createOffer($request, $car, $user, 'email');

        $pdfPath = $this->pdfService->generate($offer, $car, $user);
        $offer->update(['pdf_path' => $pdfPath]);

        Mail::to($offer->client_email)->send(
            new OfferMail($offer, $car, $user, $pdfPath)
        );

        Mail::to($user->email)->send(
            new OfferMail($offer, $car, $user, $pdfPath)
        );

        return response()->json([
            'message' => 'Offer sent successfully.',
            'offer_id' => $offer->id,
        ]);
    }

    /**
     * @param  \Illuminate\Foundation\Auth\User&User  $user
     */
    private function createOffer(SendOfferRequest $request, Car $car, $user, string $deliveryType): Offer
    {
        return Offer::query()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'margin_type' => $request->validated('margin_type'),
            'margin_amount' => $request->validated('margin_amount'),
            'vat_rate' => $request->validated('vat_rate'),
            'validity_days' => $request->validated('validity_days'),
            'price_excl_vat' => $request->validated('price_excl_vat'),
            'price_incl_vat' => $request->validated('price_incl_vat'),
            'client_name' => $request->validated('client_name'),
            'client_email' => $request->validated('client_email'),
            'message' => $request->validated('message'),
            'setup_fees' => $request->validated('setup_fees', 0),
            'registration_fees' => $request->validated('registration_fees', 0),
            'admin_fees' => $request->validated('admin_fees', 0),
            'bonus_malus' => $request->validated('bonus_malus', 0),
            'ww_fees' => $request->validated('ww_fees', 0),
            'delivery_type' => $deliveryType,
        ]);
    }
}
