<?php

namespace Vulnerar\Agent;

use Illuminate\Database\Eloquent\Model;

final class UserProvider
{
    public static function user(mixed $user = null): ?Model
    {
        $user ??= auth()->user();

        return $user instanceof Model ? $user : null;
    }

    public static function details(mixed $user = null): ?array
    {
        $user = static::user($user);

        if (! $user) return null;

        return [
            'id' => (string) $user->getKey(),
            'type' => get_class($user),
            'name' => $user->name ?? null,
            'login' => $user->login ?? $user->username ?? $user->email ?? null,
        ];
    }
}