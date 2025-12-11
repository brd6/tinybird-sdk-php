<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\RequestParameters;

use Brd6\TinybirdSdk\Resource\AbstractJsonSerializable;
use Brd6\TinybirdSdk\Util\StringHelper;
use DateTimeInterface;

use function is_bool;

abstract class AbstractRequestParameters extends AbstractJsonSerializable
{
    protected bool $ignoreEmptyValue = true;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $params = parent::jsonSerialize();
        $result = [];

        foreach ($params as $key => $value) {
            $snakeKey = StringHelper::camelCaseToSnakeCase($key);
            $result[$snakeKey] = $this->convertValue($value);
        }

        return $result;
    }

    protected function convertValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value;
    }
}
