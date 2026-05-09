<?php

namespace App\Services;

use App\Models\User;

class UserIdService
{
    /**
     * @param  string  $prefix  'U' for users, 'C' for clients
     * @param  string  $column  'legacy_user_id' or 'legacy_client_id'
     */
    private function generateNextId(string $prefix, string $column): string
    {
        $maxId = User::query()
            ->whereNotNull($column)
            ->where($column, 'like', $prefix.'%')
            ->get()
            ->map(fn (User $user) => (int) ltrim(substr($user->{$column}, strlen($prefix)), '0'))
            ->max();

        $nextNumber = ($maxId ?? 0) + 1;

        return $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function generateUserId(): string
    {
        return $this->generateNextId('U', 'legacy_user_id');
    }

    public function generateClientId(): string
    {
        return $this->generateNextId('C', 'legacy_client_id');
    }
}
