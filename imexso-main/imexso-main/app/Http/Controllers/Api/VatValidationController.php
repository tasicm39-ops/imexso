<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VatValidationRequest;
use App\Services\VatValidationService;
use Illuminate\Http\JsonResponse;

class VatValidationController extends Controller
{
    public function __construct(private VatValidationService $vatValidationService) {}

    public function validate(VatValidationRequest $request): JsonResponse
    {
        $result = $this->vatValidationService->validate(
            $request->validated('country_code'),
            $request->validated('vat_number'),
        );

        return response()->json($result);
    }
}
