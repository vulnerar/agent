<?php

namespace Vulnerar\Agent;

use Illuminate\Contracts\Auth\Authenticatable;

class Vulnerar
{
    protected static $userDetailsResolver;

    public static function user(callable $callback): void
    {
        static::$userDetailsResolver = $callback;
    }

    public static function resolveUserDetails(?Authenticatable $user): array
    {
        $callback = static::$userDetailsResolver !== null
            ? static::$userDetailsResolver
            : fn (?Authenticatable $user) => [
                'id' => $user?->getAuthIdentifier(),
                'name' => $user->name ?? null,
                'login' => $user->email ?? null,
            ];

        return call_user_func($callback, $user ?? auth()->user());
    }
}