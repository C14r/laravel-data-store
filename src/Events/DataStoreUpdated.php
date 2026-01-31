<?php

namespace C14r\DataStore\Events;

class DataStoreUpdated extends DataStoreEvent
{
    public function __construct(
        ?string $storableType,
        ?int $storableId,
        ?string $namespace,
        string $key,
        public readonly mixed $oldValue,
        public readonly mixed $newValue,
    ) {
        parent::__construct($storableType, $storableId, $namespace, $key);
    }
}
