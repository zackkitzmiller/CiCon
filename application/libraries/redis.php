<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package     Fuel
 * @version     1.0
 * @author      Fuel Development Team
 * @license     MIT License
 * @copyright   2010 Dan Horrigan
 * @link        http://fuelphp.com
 */

/**
 * This code is based on Redisent, a Redis interface for the modest.
 *
 * It has been modified to work with Fuel and to improve the code slightly.
 *
 * @author Justin Poliey <jdp34@njit.edu>
 * @copyright 2009 Justin Poliey <jdp34@njit.edu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Redis_Exception extends Exception {}

if (!defined('CRLF')) define('CRLF', sprintf('%s%s', chr(13), chr(10)));

/**
 * Redisent, a Redis interface for the modest among us
 */
class Redis {

    protected $connection = false;
    protected $key_prefix = '';

    public function  __construct(array $config = array())
    {
        $this->connection = @fsockopen($config['hostname'], $config['port'], $errno, $errstr);

        if ( ! $this->connection)
        {
            throw new Redis_Exception($errstr, $errno);
        }
        
        $this->key_prefix = isset($config['key_prefix']) ? ($config['key_prefix'] . ':') : '';
    }

    public function  __destruct()
    {
        fclose($this->connection);
    }

    public function __call($name, $args)
    {
        $response = null;

        $name = strtoupper($name);

        $command = '*'.(count($args) + 1).CRLF;
        $command .= '$'.strlen($name).CRLF;
        $command .= $name.CRLF;
        
        // If there is at least one arg, assume that the first is the key, and push a prefix string
        if (isset($args[0])) $args[0] = $this->key_prefix . $args[0];
        
        foreach ($args as $arg)
        {
            $command .= '$'.strlen($arg).CRLF;
            $command .= $arg.CRLF;
        }

        fwrite($this->connection, $command);

        $reply = trim(fgets($this->connection, 512));

        switch (substr($reply, 0, 1))
        {
            // Error
            case '-':
                throw new Redis_Exception(substr(trim($reply), 4));
            break;

            // In-line reply
            case '+':
                $response = substr(trim($reply), 1);
            break;

            // Bulk reply
            case '$':
                if ($reply == '$-1')
                {
                    $response = null;
                    break;
                }
                $read = 0;
                $size = substr($reply, 1);
                do
                {
                    $block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
                    $response .= fread($this->connection, $block_size);
                    $read += $block_size;
                } while ($read < $size);
                fread($this->connection, 2);
            break;

            // Mult-Bulk reply
            case '*':
                $count = substr($reply, 1);
                if ($count == '-1')
                {
                    return null;
                }
                $response = array();
                for ($i = 0; $i < $count; $i++)
                {
                    $bulk_head = trim(fgets($this->connection, 512));
                    $size = substr($bulk_head, 1);
                    if ($size <= '0')
                    {
                        $response[] = null;
                    }
                    else
                    {
                        $read = 0;
                        $block = "";
                        do
                        {
                            $block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
                            $block .= fread($this->connection, $block_size);
                            $read += $block_size;
                        } while ($read < $size);
                        fread($this->connection, 2); /* discard crlf */
                        $response[] = $block;
                    }
                }
            break;

            // Integer Reply
            case ':':
                $response = substr(trim($reply), 1);
            break;

            // Don't know what to do?  Throw it outta here
            default:
                throw new Redis_Exception("invalid server response: {$reply}");
            break;
        }

        return $response;
    }

}
