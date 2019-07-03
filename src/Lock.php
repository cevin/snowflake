<?php

namespace Cevin\Snowflake;

use Swoole\Lock as SwooleLock;

class Lock
{
    private $lock;

    public function __construct()
    {

        if (extension_loaded('swoole'))
        {
            $this->lock = new SwooleLock(SWOOLE_MUTEX);
        }

    }

    public function lock()
    {
        return $this->lock ? $this->lock->lock() : true;
    }

    public function unlock()
    {
        return $this->lock ? $this->lock->unlock() : true;
    }
}