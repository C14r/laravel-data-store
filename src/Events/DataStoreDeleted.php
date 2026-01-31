<?php

namespace C14r\DataStore\Events;

class DataStoreDeleted extends DataStoreEvent
{
    public function __construct(
        ?string $storableType,
        ?int $storableId,
        ?string $namespace,
        string $key,
        public readonly mixed $value,
    ) {
        parent::__construct($storableType, $storableId, $namespace, $key);
    }
}
