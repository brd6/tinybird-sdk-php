<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Util;

use Generator;

use function array_is_list;
use function explode;
use function implode;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function trim;

use const JSON_THROW_ON_ERROR;

final class NdjsonHelper
{
    /**
     * @param array<string, mixed>|array<array<string, mixed>> $data
     */
    public static function encode(array $data): string
    {
        if ($data === []) {
            return '';
        }

        if (!array_is_list($data)) {
            $data = [$data];
        }

        $lines = [];
        foreach ($data as $item) {
            $lines[] = json_encode($item, JSON_THROW_ON_ERROR);
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function decode(string $ndjson): array
    {
        return iterator_to_array(self::decodeStream($ndjson));
    }

    /**
     * @param iterable<array<string, mixed>> $items
     * @return Generator<string>
     */
    public static function encodeStream(iterable $items): Generator
    {
        foreach ($items as $item) {
            yield json_encode($item, JSON_THROW_ON_ERROR) . "\n";
        }
    }

    /**
     * @return Generator<array<string, mixed>>
     */
    public static function decodeStream(string $ndjson): Generator
    {
        foreach (explode("\n", $ndjson) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            yield json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        }
    }
}
