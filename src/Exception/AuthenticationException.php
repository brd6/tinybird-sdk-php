<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Exception;

use Brd6\TinybirdSdk\Constant\TinybirdErrorCode;
use Brd6\TinybirdSdk\Enum\Region;
use Brd6\TinybirdSdk\Util\TokenHelper;

class AuthenticationException extends ApiException
{
    private ?string $tokenHost = null;

    /**
     * @param array<string, list<string>> $headers
     * @param array<string, mixed> $response
     */
    public function __construct(
        int $statusCode,
        array $headers,
        array $response,
        string $message,
        ?string $token = null,
    ) {
        if ($token !== null) {
            $this->tokenHost = TokenHelper::extractHost($token);
        }

        parent::__construct($statusCode, $headers, $response, $this->enhanceMessage($message));
    }

    public function getErrorCode(): string
    {
        return TinybirdErrorCode::UNAUTHORIZED;
    }

    public function getTokenHost(): ?string
    {
        return $this->tokenHost;
    }

    private function enhanceMessage(string $message): string
    {
        if (!$this->isRegionMismatch($message) || $this->tokenHost === null) {
            return $message;
        }

        $region = Region::fromTokenHost($this->tokenHost);

        if ($region === null) {
            return sprintf(
                "%s\n\nYour token is for host '%s'. Check that you're using the correct region.",
                $message,
                $this->tokenHost,
            );
        }

        if ($region === Region::LOCAL) {
            return sprintf(
                "%s\n\nYour token is for Tinybird Local. Use Client::local(\$token).",
                $message,
            );
        }

        return sprintf(
            "%s\n\nYour token is for region '%s'. Use Client::forRegion(Region::%s, \$token).",
            $message,
            $this->tokenHost,
            $region->name,
        );
    }

    private function isRegionMismatch(string $message): bool
    {
        $lowerMessage = strtolower($message);

        return str_contains($lowerMessage, 'workspace not found')
            || str_contains($lowerMessage, 'token host')
            || str_contains($lowerMessage, 'signature verification failed');
    }
}
