<?php

use C14r\DataStore\Facades\DataStore;
use C14r\DataStore\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('can store and retrieve global data', function () {
    DataStore::set('test_key', 'test_value');
    
    expect(DataStore::get('test_key'))->toBe('test_value');
});

it('can store data with TTL', function () {
    DataStore::set('temp_key', 'temp_value', 1);
    
    expect(DataStore::get('temp_key'))->toBe('temp_value');
    
    sleep(2);
    
    expect(DataStore::get('temp_key'))->toBeNull();
});

it('can use nested keys', function () {
    DataStore::set(['level1', 'level2', 'key'], 'nested_value');
    
    expect(DataStore::get('level1.level2.key'))->toBe('nested_value');
    expect(DataStore::get(['level1', 'level2', 'key']))->toBe('nested_value');
});

it('can get nested structure', function () {
    DataStore::set('config.app.name', 'MyApp');
    DataStore::set('config.app.version', '1.0');
    DataStore::set('config.db.host', 'localhost');
    
    $nested = DataStore::nested();
    
    expect($nested)->toHaveKey('config');
    expect($nested['config']['app']['name'])->toBe('MyApp');
    expect($nested['config']['db']['host'])->toBe('localhost');
});

it('can get keys starting with prefix', function () {
    DataStore::set('user.1.name', 'Alice');
    DataStore::set('user.1.email', 'alice@example.com');
    DataStore::set('user.2.name', 'Bob');
    
    $keys = DataStore::keysStartingWith('user.1');
    
    expect($keys)->toHaveCount(2);
    expect($keys)->toContain('user.1.name');
    expect($keys)->toContain('user.1.email');
});

it('can get flat data starting with prefix', function () {
    DataStore::set('item.1.name', 'Item 1');
    DataStore::set('item.2.name', 'Item 2');
    
    $items = DataStore::startingWith('item');
    
    expect($items->count())->toBe(2);
    expect($items['item.1.name'])->toBe('Item 1');
});

it('can get nested data from prefix', function () {
    DataStore::set('user.123.profile.name', 'John');
    DataStore::set('user.123.profile.age', 30);
    DataStore::set('user.123.settings.theme', 'dark');
    
    $userData = DataStore::nestedFrom('user.123');
    
    expect($userData['profile']['name'])->toBe('John');
    expect($userData['settings']['theme'])->toBe('dark');
});

it('can increment and decrement values', function () {
    DataStore::set('counter', 0);
    
    DataStore::increment('counter');
    expect(DataStore::get('counter'))->toBe(1);
    
    DataStore::increment('counter', 5);
    expect(DataStore::get('counter'))->toBe(6);
    
    DataStore::decrement('counter', 2);
    expect(DataStore::get('counter'))->toBe(4);
});

it('can check if key exists', function () {
    DataStore::set('existing', 'value');
    
    expect(DataStore::has('existing'))->toBeTrue();
    expect(DataStore::has('non_existing'))->toBeFalse();
});

it('can delete keys', function () {
    DataStore::set('to_delete', 'value');
    
    expect(DataStore::has('to_delete'))->toBeTrue();
    
    DataStore::delete('to_delete');
    
    expect(DataStore::has('to_delete'))->toBeFalse();
});

it('can clear all data', function () {
    DataStore::set('key1', 'value1');
    DataStore::set('key2', 'value2');
    
    expect(DataStore::keys()->count())->toBe(2);
    
    DataStore::clear();
    
    expect(DataStore::keys()->count())->toBe(0);
});

it('can use namespaces', function () {
    $settings = DataStore::inNamespace('settings');
    $cache = DataStore::inNamespace('cache');
    
    $settings->set('theme', 'dark');
    $cache->set('theme', 'light');
    
    expect($settings->get('theme'))->toBe('dark');
    expect($cache->get('theme'))->toBe('light');
});
