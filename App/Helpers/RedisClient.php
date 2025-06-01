<?php

namespace App\Config;

class RedisClient {
    public static function connect(): \Redis {
        $redis = new \Redis();
        $redis->connect('localhost', 6379);
        return $redis;
    }
}
