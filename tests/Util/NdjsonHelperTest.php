<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk\Util;

use Brd6\Test\TinybirdSdk\TestCase;
use Brd6\TinybirdSdk\Util\NdjsonHelper;
use JsonException;

class NdjsonHelperTest extends TestCase
{
    public function testEncodeEmptyArray(): void
    {
        $this->assertSame('', NdjsonHelper::encode([]));
    }

    public function testEncodeSingleObject(): void
    {
        $data = ['uuid' => '123', 'name' => 'test'];

        $this->assertSame('{"uuid":"123","name":"test"}', NdjsonHelper::encode($data));
    }

    public function testEncodeMultipleObjects(): void
    {
        $data = [
            ['uuid' => '1', 'name' => 'first'],
            ['uuid' => '2', 'name' => 'second'],
        ];

        $expected = "{\"uuid\":\"1\",\"name\":\"first\"}\n{\"uuid\":\"2\",\"name\":\"second\"}";
        $this->assertSame($expected, NdjsonHelper::encode($data));
    }

    public function testDecodeEmptyString(): void
    {
        $this->assertSame([], NdjsonHelper::decode(''));
    }

    public function testDecodeWhitespaceOnly(): void
    {
        $this->assertSame([], NdjsonHelper::decode("  \n\n  "));
    }

    public function testDecodeSingleLine(): void
    {
        $ndjson = '{"uuid":"123","name":"test"}';

        $this->assertSame([['uuid' => '123', 'name' => 'test']], NdjsonHelper::decode($ndjson));
    }

    public function testDecodeMultipleLines(): void
    {
        $ndjson = "{\"uuid\":\"1\",\"name\":\"first\"}\n{\"uuid\":\"2\",\"name\":\"second\"}";

        $expected = [
            ['uuid' => '1', 'name' => 'first'],
            ['uuid' => '2', 'name' => 'second'],
        ];
        $this->assertSame($expected, NdjsonHelper::decode($ndjson));
    }

    public function testDecodeWithTrailingNewline(): void
    {
        $ndjson = "{\"uuid\":\"1\"}\n{\"uuid\":\"2\"}\n";

        $this->assertCount(2, NdjsonHelper::decode($ndjson));
    }

    public function testDecodeWithEmptyLines(): void
    {
        $ndjson = "{\"uuid\":\"1\"}\n\n{\"uuid\":\"2\"}\n";

        $this->assertCount(2, NdjsonHelper::decode($ndjson));
    }

    public function testDecodeThrowsOnInvalidJson(): void
    {
        $this->expectException(JsonException::class);

        NdjsonHelper::decode('invalid json');
    }

    public function testEncodeStreamYieldsLines(): void
    {
        $items = [
            ['uuid' => '1'],
            ['uuid' => '2'],
        ];

        $result = iterator_to_array(NdjsonHelper::encodeStream($items));

        $this->assertCount(2, $result);
        $this->assertSame("{\"uuid\":\"1\"}\n", $result[0]);
        $this->assertSame("{\"uuid\":\"2\"}\n", $result[1]);
    }

    public function testEncodeStreamWithGenerator(): void
    {
        $generator = (static function () {
            yield ['id' => 1];
            yield ['id' => 2];
            yield ['id' => 3];
        })();

        $result = iterator_to_array(NdjsonHelper::encodeStream($generator));

        $this->assertCount(3, $result);
    }

    public function testDecodeStreamYieldsItems(): void
    {
        $ndjson = "{\"uuid\":\"1\"}\n{\"uuid\":\"2\"}\n{\"uuid\":\"3\"}";

        $result = iterator_to_array(NdjsonHelper::decodeStream($ndjson));

        $this->assertCount(3, $result);
        $this->assertSame(['uuid' => '1'], $result[0]);
        $this->assertSame(['uuid' => '2'], $result[1]);
        $this->assertSame(['uuid' => '3'], $result[2]);
    }

    public function testDecodeStreamSkipsEmptyLines(): void
    {
        $ndjson = "{\"id\":1}\n\n\n{\"id\":2}\n";

        $result = iterator_to_array(NdjsonHelper::decodeStream($ndjson));

        $this->assertCount(2, $result);
    }

    public function testDecodeStreamEmptyString(): void
    {
        $result = iterator_to_array(NdjsonHelper::decodeStream(''));

        $this->assertSame([], $result);
    }

    public function testEncodeDecodeRoundtrip(): void
    {
        $original = [
            ['uuid' => '1', 'name' => 'first'],
            ['uuid' => '2', 'name' => 'second'],
        ];

        $encoded = NdjsonHelper::encode($original);
        $decoded = NdjsonHelper::decode($encoded);

        $this->assertSame($original, $decoded);
    }
}
