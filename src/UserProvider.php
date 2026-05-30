<?php

namespace Vulnerar\Agent;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class UserProvider
{
    public static function user(?Authenticatable $user = null): ?Authenticatable
    {
        $user ??= auth()->user();

        return $user instanceof Model ? $user : null;
    }

    public static function details(?Authenticatable $user = null): ?array
    {
        $user = static::user($user);

        if (! $user) return null;

        return [
            'id' => (string) $user->getAuthIdentifier(),
            'type' => get_class($user),
            'name' => $user->name ?? null,
            'login' => $user->login ?? $user->username ?? $user->email ?? null,
        ];
    }
}