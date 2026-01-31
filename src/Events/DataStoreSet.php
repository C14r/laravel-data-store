<?php

namespace C14r\DataStore\Events;

class DataStoreSet extends DataStoreEvent
{
    public function __construct(
        ?string $storableType,
        ?int $storableId,
        ?string $namespace,
        string $key,
        public readonly mixed $value,
        public readonly ?int $ttl,
    ) {
        parent::__construct($storableType, $storableId, $namespace, $key);
    }
}
