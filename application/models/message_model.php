<?php

class Message_model extends CI_Model {
    public function __construct() {}
    
    public function create($message)
    {
        $redis = RedisInstance::get_redis();
        
        // first find the ID that we're going to use for posts
        $post_id = $redis->incr("global:nextMessageId");
        
        $message_key = "messages:{$post_id}";
        $redis->hset($message_key, 'message', $message);
        $redis->hset($message_key, 'created_at', date('Y-m-d H:i:s'));
        
        // then add this onto the timeline
        $redis->lpush("messages:recent", $post_id);
        $redis->ltrim("messages:recent", 0, 9);
    }
    
    public function get_messages()
    {
        $redis = RedisInstance::get_redis();
        
        $ids = $redis->lrange("messages:recent", 0, 9);
        $posts = array();
        foreach ($ids as $id)
        {
            $_post = $redis->hgetall("messages:{$id}");

            $posts[$id] = Message_model::_flat_to_assc($_post);
        }        
        return $posts;
    }
    
    public function get_message($id)
    {
        $redis = RedisInstance::get_redis();
        
        $_post = $redis->hgetall("messages:{$id}");
        $post = Message_model::_flat_to_assc($_post);
        $post['id'] = $id;
        
        return $post;
    }
    
    public static function _flat_to_assc($array)
    {
	    $cnt = count($array);
        if ($cnt % 2 > 0) return false; // expects an even number of els
        
        $_n = array();
        
        for ($i = 0; $i < $cnt; $i += 2)
        {
            if (isset($array[$i]) && isset($array[$i + 1])) $_n[$array[$i]] = $array[$i + 1];
        }
        
        return $_n;        
    }
}