<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CarImportRequest;
use App\Http\Resources\CarImportResource;
use App\Services\CarXmlImportService;
use App\Services\VenduXmlImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Admin-only XML stock import via {@see CarXmlImportService}.
 * HTTP route uses `auth:sanctum` and `admin` middleware (see `routes/api.php`).
 */
class CarImportController extends Controller
{
    public function __construct(private CarXmlImportService $importService) {}

    public function store(CarImportRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $xmlContent = file_get_contents($file->getRealPath());

        if ($xmlContent === false || $xmlContent === '') {
            return response()->json([
                'message' => 'Failed to read the uploaded file.',
            ], 422);
        }

        try {
            $import = $this->importService->import([
                'content' => $xmlContent,
                'filename' => $file->getClientOriginalName(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'Import completed successfully.',
                'data' => new CarImportResource($import),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed for one or more items in the XML.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function storeVendu(VenduXmlImportService $importService): JsonResponse
    {
        try {
            $stats = $importService->import();

            return response()->json([
                'message' => 'Vendu import completed successfully.',
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
