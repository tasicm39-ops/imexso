<?php

namespace App\Services;

use App\Enums\CarHistoryStatus;
use App\Models\Car;
use App\Models\CarHistory;
use App\Models\User;

class CarHistoryService
{
    public const RESULT_CREATED = 'created';

    public const RESULT_UPDATED = 'updated';

    public const RESULT_SKIPPED = 'skipped';

    /**
     * @param  array<string, mixed>|null  $buyerInfo
     */
    public function record(Car $car, string $status, ?array $buyerInfo = null): bool
    {
        return $this->recordSold($car, $buyerInfo, $status) === self::RESULT_CREATED;
    }

    /**
     * @param  array<string, mixed>|null  $buyerInfo
     */
    public function recordSold(
        Car $car,
        ?array $buyerInfo = null,
        string $status = CarHistoryStatus::Sold->value,
    ): string {
        $existing = CarHistory::query()
            ->where('car_id', $car->id)
            ->where('status', $status)
            ->first();

        if ($existing === null) {
            CarHistory::query()->create([
                'car_id' => $car->id,
                'status' => $status,
                'buyer_info' => $buyerInfo,
                'created_at' => now(),
            ]);

            return self::RESULT_CREATED;
        }

        $merged = $this->mergeBuyerInfo($existing->buyer_info, $buyerInfo);

        if (! $this->buyerInfoEquals($existing->buyer_info, $merged)) {
            $existing->update(['buyer_info' => $merged]);

            return self::RESULT_UPDATED;
        }

        return self::RESULT_SKIPPED;
    }

    public function recordSoldFromOnlineOrder(Car $car, User $user, ?string $notes = null): string
    {
        return $this->recordSold($car, $this->buildOnlineOrderBuyerInfo($user, $notes));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOnlineOrderBuyerInfo(User $user, ?string $notes): array
    {
        $info = array_filter([
            'source' => 'online_order',
            'user_id' => $user->id,
            'client_id' => $user->legacy_client_id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'company_name' => $user->company_name,
            'ordered_at' => now()->toIso8601String(),
            'notes' => $notes !== null && $notes !== '' ? $notes : null,
        ], fn ($value) => $value !== null && $value !== '');

        return $info;
    }

    /**
     * @param  array<string, mixed>|null  $existing
     * @param  array<string, mixed>|null  $incoming
     * @return array<string, mixed>|null
     */
    private function mergeBuyerInfo(?array $existing, ?array $incoming): ?array
    {
        if ($incoming === null || $incoming === []) {
            return $existing;
        }

        if ($existing === null || $existing === []) {
            return $incoming;
        }

        return array_merge(
            $existing,
            array_filter($incoming, fn ($value) => $value !== null && $value !== ''),
        );
    }

    /**
     * @param  array<string, mixed>|null  $a
     * @param  array<string, mixed>|null  $b
     */
    private function buyerInfoEquals(?array $a, ?array $b): bool
    {
        return json_encode($this->normalizeBuyerInfo($a))
            === json_encode($this->normalizeBuyerInfo($b));
    }

    /**
     * @param  array<string, mixed>|null  $info
     * @return array<string, mixed>
     */
    private function normalizeBuyerInfo(?array $info): array
    {
        if ($info === null) {
            return [];
        }

        ksort($info);

        return $info;
    }
}
