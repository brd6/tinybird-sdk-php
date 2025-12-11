<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Util;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\Util\StringHelper;

class StringHelperTest extends TestCase
{
    public function testCamelCaseToSnakeCase(): void
    {
        $this->assertSame('hello_world', StringHelper::camelCaseToSnakeCase('helloWorld'));
        $this->assertSame('user_id', StringHelper::camelCaseToSnakeCase('userId'));
        $this->assertSame('created_at', StringHelper::camelCaseToSnakeCase('createdAt'));
    }

    public function testCamelCaseToSnakeCasePreservesAllUppercase(): void
    {
        // All uppercase strings without lowercase are preserved
        $this->assertSame('HTTPRequest', StringHelper::camelCaseToSnakeCase('HTTPRequest'));
        // Standard camelCase works
        $this->assertSame('http_request', StringHelper::camelCaseToSnakeCase('httpRequest'));
    }

    public function testCamelCaseToSnakeCaseSingleWord(): void
    {
        $this->assertSame('name', StringHelper::camelCaseToSnakeCase('name'));
        $this->assertSame('id', StringHelper::camelCaseToSnakeCase('id'));
    }

    public function testCamelCaseToSnakeCaseWithNumbers(): void
    {
        $input = 'outputFormatJsonQuote64bitIntegers';
        $expected = 'output_format_json_quote_64bit_integers';
        $this->assertSame($expected, StringHelper::camelCaseToSnakeCase($input));
    }

    public function testSnakeCaseToCamelCase(): void
    {
        $this->assertSame('HelloWorld', StringHelper::snakeCaseToCamelCase('hello_world'));
        $this->assertSame('UserId', StringHelper::snakeCaseToCamelCase('user_id'));
        $this->assertSame('CreatedAt', StringHelper::snakeCaseToCamelCase('created_at'));
    }

    public function testSnakeCaseToCamelCaseSingleWord(): void
    {
        $this->assertSame('Name', StringHelper::snakeCaseToCamelCase('name'));
        $this->assertSame('Id', StringHelper::snakeCaseToCamelCase('id'));
    }

    public function testSnakeCaseToCamelCaseWithCustomSeparator(): void
    {
        $this->assertSame('HelloWorld', StringHelper::snakeCaseToCamelCase('hello-world', '-'));
    }
}
