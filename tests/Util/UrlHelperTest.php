<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Util;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\Util\UrlHelper;

class UrlHelperTest extends TestCase
{
    public function testBuildQuerySimple(): void
    {
        $result = UrlHelper::buildQuery(['foo' => 'bar', 'baz' => '123']);
        $this->assertSame('foo=bar&baz=123', $result);
    }

    public function testBuildQueryEmpty(): void
    {
        $this->assertSame('', UrlHelper::buildQuery([]));
    }

    public function testBuildQueryEncodesSpecialChars(): void
    {
        $result = UrlHelper::buildQuery(['q' => 'SELECT * FROM test']);
        $this->assertSame('q=SELECT%20%2A%20FROM%20test', $result);
    }

    public function testBuildQueryWithArray(): void
    {
        $result = UrlHelper::buildQuery(['scope' => ['read', 'write']]);
        $this->assertStringContainsString('scope', $result);
    }

    public function testParseQuerySimple(): void
    {
        $result = UrlHelper::parseQuery('foo=bar&baz=123');
        $this->assertSame(['foo' => 'bar', 'baz' => '123'], $result);
    }

    public function testParseQueryEmpty(): void
    {
        $this->assertSame([], UrlHelper::parseQuery(''));
    }

    public function testParseQueryDecodesSpecialChars(): void
    {
        $result = UrlHelper::parseQuery('q=SELECT%20%2A%20FROM%20test');
        $this->assertSame(['q' => 'SELECT * FROM test'], $result);
    }

    public function testRoundtrip(): void
    {
        $original = ['name' => 'test', 'limit' => '100'];
        $encoded = UrlHelper::buildQuery($original);
        $decoded = UrlHelper::parseQuery($encoded);
        $this->assertSame($original, $decoded);
    }
}
