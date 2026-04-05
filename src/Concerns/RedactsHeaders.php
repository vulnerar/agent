<?php

namespace Vulnerar\Agent\Concerns;

use RuntimeException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Throwable;

use function array_map;
use function explode;
use function implode;
use function in_array;
use function str_contains;
use function strlen;
use function strtolower;
use function trim;

/**
 * @copyright Copyright (c) Taylor Otwell
 */
trait RedactsHeaders
{
    private function redactHeaders(HeaderBag $headers): HeaderBag
    {
        $keys = ['Authorization', 'Cookie', 'Proxy-Authorization', 'X-XSRF-TOKEN'];

        foreach ($keys as $key) {
            if (! $headers->has($key)) {
                continue;
            }

            $headers->set($key, array_map(fn ($value) => match (strtolower($key)) {
                'authorization', 'proxy-authorization' => $this->redactAuthorizationHeaderValue((string) $value), // @phpstan-ignore cast.string
                'cookie' => $this->redactCookieHeaderValue((string) $value), // @phpstan-ignore cast.string
                default => $this->redactHeaderValue((string) $value), // @phpstan-ignore cast.string
            }, $headers->all($key)));
        }

        return $headers;
    }

    private function redactHeaderValue(string $value): string
    {
        return '['.strlen($value).' bytes redacted]';
    }

    private function redactAuthorizationHeaderValue(string $value): string
    {
        if (! str_contains($value, ' ')) {
            return $this->redactHeaderValue($value);
        }

        [$type, $remainder] = explode(' ', $value, 2);

        if (in_array(strtolower($type), [
            'basic',
            'bearer',
            'concealed',
            'digest',
            'dpop',
            'gnap',
            'hoba',
            'mutual',
            'negotiate',
            'oauth',
            'privatetoken',
            'scram-sha-1',
            'scram-sha-256',
            'vapid',
        ], true)) {
            return $type.' '.$this->redactHeaderValue($remainder);
        }

        return $this->redactHeaderValue($value);
    }

    private function redactCookieHeaderValue(string $value): string
    {
        $cookies = explode(';', $value);

        try {
            return implode('; ', array_map(function ($cookie) {
                if (! str_contains($cookie, '=')) {
                    throw new RuntimeException('Invalid cookie format.');
                }

                [$name, $value] = explode('=', $cookie, 2);

                return trim($name).'='.$this->redactHeaderValue($value);
            }, $cookies));
        } catch (Throwable) {
            return $this->redactHeaderValue($value);
        }
    }
}
