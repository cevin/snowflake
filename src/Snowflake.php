<?php

namespace Cevin\Snowflake;

use Exception;

class Snowflake
{
    const EPOCH = 1543223810238;
    const SEQUENCE_BITS = 12;
    const SEQUENCE_MAX = -1 ^ (-1 << self::SEQUENCE_BITS);
    const WORKER_BITS = 10;
    const WORKER_MAX = -1 ^ (-1 << self::WORKER_BITS);
    const TIME_SHIFT = self::WORKER_BITS + self::SEQUENCE_BITS;
    const WORKER_SHIFT = self::SEQUENCE_BITS;

    protected $timestamp;

    protected $workerId;

    protected $sequence;

    protected $lock;

    public function __construct($workerId)
    {
        if ($workerId < 0 || $workerId > self::WORKER_MAX) {
            throw new Exception("\$workerId out of range");
        }
        $this->timestamp = 0;
        $this->workerId = $workerId;
        $this->sequence = 0;
        $this->lock = new Lock();
    }

    /**
     * @return int
     */
    public function getId()
    {
        $this->lock->lock();
        $now = $this->now();
        if ($this->timestamp == $now) {
            $this->sequence++;
            if ($this->sequence > self::SEQUENCE_MAX) {
                while ($now <= $this->timestamp) {
                    $now = $this->now();
                }
            }
        } else {
            $this->sequence = 0;
        }
        $this->timestamp = $now;
        $id = (($now - self::EPOCH) << self::TIME_SHIFT) | ($this->workerId << self::WORKER_SHIFT) | $this->sequence;
        $this->lock->unlock();
        return $id;
    }

    /**
     * @return string
     */
    public function now()
    {
        return sprintf("%.0f", microtime(true) * 1000);
    }
}