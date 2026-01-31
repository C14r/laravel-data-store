<?php

namespace C14r\DataStore\Events;

class DataStoreCleared extends DataStoreEvent
{
    public function __construct(
        ?string $storableType,
        ?int $storableId,
        ?string $namespace,
        public readonly int $count,
    ) {
        parent::__construct($storableType, $storableId, $namespace, '*');
    }
}
