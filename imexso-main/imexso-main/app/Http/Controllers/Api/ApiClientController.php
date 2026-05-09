<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiClientController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $clients = ApiClient::query()->orderBy('name')->get();

        return JsonResource::collection($clients);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'description' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:50'],
            'allowed_abilities' => ['nullable', 'array'],
            'allowed_abilities.*' => ['string'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1'],
        ]);

        $client = ApiClient::query()->create($validated);

        $token = $client->createToken(
            $client->name,
            $client->allowed_abilities ?? ['*'],
        );

        return response()->json([
            'message' => 'API client created successfully.',
            'data' => [
                'client' => $client,
                'token' => $token->plainTextToken,
            ],
        ], 201);
    }

    public function show(ApiClient $apiClient): JsonResource
    {
        return new JsonResource($apiClient);
    }

    public function generateToken(Request $request, ApiClient $apiClient): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string'],
        ]);

        $token = $apiClient->createToken(
            $validated['name'] ?? $apiClient->name,
            $validated['abilities'] ?? $apiClient->allowed_abilities ?? ['*'],
        );

        return response()->json([
            'message' => 'Token generated successfully.',
            'data' => [
                'token' => $token->plainTextToken,
            ],
        ]);
    }

    public function revokeTokens(ApiClient $apiClient): JsonResponse
    {
        $count = $apiClient->tokens()->count();
        $apiClient->tokens()->delete();

        return response()->json([
            'message' => "Revoked {$count} token(s).",
        ]);
    }
}
