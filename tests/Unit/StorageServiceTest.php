<?php

use C14r\DataStore\Services\StorageService;
use C14r\DataStore\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('can normalize namespaces from arrays', function () {
    $service = new StorageService();
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('normalizeNamespace');
    $method->setAccessible(true);
    
    expect($method->invoke($service, ['level1', 'level2', 'level3']))->toBe('level1.level2.level3');
    expect($method->invoke($service, 'simple'))->toBe('simple');
    expect($method->invoke($service, null))->toBeNull();
    expect($method->invoke($service, ''))->toBeNull();
});

it('can normalize keys from arrays', function () {
    $service = new StorageService();
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('normalizeKey');
    $method->setAccessible(true);
    
    expect($method->invoke($service, ['key1', 'key2', 'key3']))->toBe('key1.key2.key3');
    expect($method->invoke($service, 'simple.key'))->toBe('simple.key');
});

it('builds nested arrays correctly', function () {
    $service = new StorageService();
    
    $entries = collect([
        (object)['key' => 'user.123.name', 'value' => 'John'],
        (object)['key' => 'user.123.age', 'value' => 30],
        (object)['key' => 'user.456.name', 'value' => 'Jane'],
    ]);
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('buildNestedArray');
    $method->setAccessible(true);
    
    $result = $method->invoke($service, $entries);
    
    expect($result['user']['123']['name'])->toBe('John');
    expect($result['user']['123']['age'])->toBe(30);
    expect($result['user']['456']['name'])->toBe('Jane');
});

it('can chain namespace and storable methods', function () {
    $service = new StorageService();
    
    $withNamespace = $service->inNamespace('test');
    expect($withNamespace->getNamespace())->toBe('test');
    
    $withNestedNamespace = $service->inNamespace(['level1', 'level2']);
    expect($withNestedNamespace->getNamespace())->toBe('level1.level2');
});
