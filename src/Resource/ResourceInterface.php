<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

interface ResourceInterface
{
    public function __construct();

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self;

    /**
     * @return array<string, mixed>
     */
    public function getRawData(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $ignoreEmptyValue = true): array;
}
