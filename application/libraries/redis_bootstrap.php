<?php

include 'redis.php';

Class RedisInstance {
    
    private static $redis = null;
    final private function __construct() {}
    final private function __clone() {}
    
    public static function get_redis()
    {
        if (self::$redis === null)
        {
            $conf = array(
                'key_prefix' => 'cicon',
                'hostname'  => 'localhost',
                'port'      => '6379',
            );
            
            self::$redis = new Redis($conf);
        }
        
        return self::$redis;
    }
    
}