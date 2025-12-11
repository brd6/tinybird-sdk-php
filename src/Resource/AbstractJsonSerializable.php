<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

use JsonSerializable;

use function array_filter;
use function count;
use function get_object_vars;
use function in_array;
use function is_countable;
use function json_decode;
use function json_encode;

use const ARRAY_FILTER_USE_BOTH;

abstract class AbstractJsonSerializable implements JsonSerializable
{
    private const EXCLUDED_KEYS = ['ignoreEmptyValue', 'rawData'];

    protected bool $ignoreEmptyValue = false;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(
            get_object_vars($this),
            fn ($value, string $key) => $this->canBeSerialized($value, $key),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $ignoreEmptyValue = false): array
    {
        $this->ignoreEmptyValue = $ignoreEmptyValue;

        /** @var array<string, mixed> $data */
        $data = json_decode((string) json_encode($this), true);

        return $data;
    }

    protected function canBeSerialized(mixed $value, string $key): bool
    {
        if (in_array($key, self::EXCLUDED_KEYS, true)) {
            return false;
        }

        if (!$this->ignoreEmptyValue) {
            return true;
        }

        if ($value === null || $value === '') {
            return false;
        }

        if (is_countable($value) && count($value) === 0) {
            return false;
        }

        return true;
    }
}
