<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Util;

use function http_build_query;
use function parse_str;

use const PHP_QUERY_RFC3986;

final class UrlHelper
{
    /**
     * @param array<string, mixed> $params
     */
    public static function buildQuery(array $params): string
    {
        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @return array<string, string|array<string>>
     */
    public static function parseQuery(string $query): array
    {
        $params = [];
        parse_str($query, $params);

        /** @var array<string, string|array<string>> $params */
        return $params;
    }
}
