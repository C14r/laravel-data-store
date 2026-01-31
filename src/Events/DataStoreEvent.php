<?php

namespace C14r\DataStore\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DataStoreEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ?string $storableType,
        public readonly ?int $storableId,
        public readonly ?string $namespace,
        public readonly string $key,
    ) {}
}
