<?php

namespace Core;

class Cache {
    private array $cachedData;
    private static \Core\Cache $instance;
    private int $limit_time_in_seconds = 1;
    private function __construct() {
        $this->cachedData = [];
    }
    public static function getInstance(): self {
        return self::$instance ??= new \Core\Cache();
    }
    public function get(string $key) {
        if (!$this->has($key)) return null;
        return $this->cachedData[$key]['data'];
    }
    public function set(string $key, $value) {
        if ($this->has($key)) return;
        $this->cachedData[$key]['data'] = $value;
        $this->cachedData[$key]['time'] = time();
    }

    public function unset(string $key) {
        unset($this->cachedData[$key]);
    }
    public function has(string $key) {

        return isset($this->cachedData[$key])
            && time() - $this->cachedData[$key]['time'] < $this->limit_time_in_seconds;
    }

    public function use(string $key, callable $callback) {
        if ($this->has($key))
            return $this->get($key);

        $this->set($key, $callback());
        return $this->get($key);
    }
}
