<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Resource;

abstract class AbstractResource extends AbstractJsonSerializable implements ResourceInterface
{
    /** @var array<string, mixed> */
    private array $rawData = [];

    final public function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        $resource = new static();
        $resource->setRawData($data);
        $resource->initialize();

        return $resource;
    }

    abstract protected function initialize(): void;

    /**
     * @return array<string, mixed>
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * @param array<string, mixed> $rawData
     */
    protected function setRawData(array $rawData): static
    {
        $this->rawData = $rawData;

        return $this;
    }
}
