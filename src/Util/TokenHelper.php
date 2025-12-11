<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Util;

use Brd6\TinybirdSdk\Enum\Region;

/**
 * Token format: p.<base64_payload>.<signature>
 * Payload contains: {"u": "user_id", "id": "token_id", "host": "gcp-europe-west2"}
 */
class TokenHelper
{
    public static function extractHost(string $token): ?string
    {
        $payload = self::decodePayload($token);

        return $payload['host'] ?? null;
    }

    public static function getRegion(string $token): ?Region
    {
        $host = self::extractHost($token);

        return $host !== null ? Region::fromTokenHost($host) : null;
    }

    public static function isLocalToken(string $token): bool
    {
        return self::extractHost($token) === 'local';
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decodePayload(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3 || $parts[0] !== 'p') {
            return null;
        }

        $payload = base64_decode($parts[1], true);

        if ($payload === false) {
            return null;
        }

        $data = json_decode($payload, true);

        return is_array($data) ? $data : null;
    }
}
