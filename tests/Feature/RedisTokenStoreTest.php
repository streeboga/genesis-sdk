<?php

use Streeboga\Genesis\Session\Storage\RedisTokenStore;

class ArrayRedisMock
{
    private array $store = [];
    public function set($k, $v) { $this->store[$k] = $v; }
    public function get($k) { return $this->store[$k] ?? null; }
    public function del($k) { if (is_array($k)) { foreach($k as $key) { unset($this->store[$key]); } } else { unset($this->store[$k]); } }
    public function keys($pattern) { $prefix = str_replace('*', '', $pattern); $res = []; foreach ($this->store as $k => $v) { if (strpos($k, $prefix) === 0) $res[] = $k; } return $res; }
}

it('works with array redis mock', function () {
    $mock = new ArrayRedisMock();
    $store = new RedisTokenStore($mock, 'test:');

    $store->save('s1', ['access_token' => 'a', 'refresh_token' => 'r']);
    $data = $store->load('s1');
    expect($data['access_token'])->toBe('a');

    $ids = $store->listSessionIds();
    expect($ids)->toContain('s1');

    $store->delete('s1');
    expect($store->load('s1'))->toBeNull();
});






