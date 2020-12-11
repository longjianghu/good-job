<?php

namespace App\Utils;

use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * SnowFlake
 *
 * @Bean()
 */
class SnowFlake
{
    private static $lastTimestamp = 0;
    private static $lastSequence  = 0;
    private static $sequenceMask  = 4095;
    private static $twepoch       = 1508945092000;

    /**
     * 生成随机编号
     *
     * @access public
     * @param int $dataCenterId 数据中心ID 0-31
     * @param int $workerId     任务进程ID 0-31
     * @return int 分布式ID
     */
    public static function make($dataCenterId = 0, $workerId = 0)
    {
        // 41bit timestamp + 5bit dataCenter + 5bit worker + 12bit
        $timestamp = self::timeGen();

        if (self::$lastTimestamp == $timestamp) {
            self::$lastSequence = (self::$lastSequence + 1) & self::$sequenceMask;

            if (self::$lastSequence == 0) {
                $timestamp = self::tilNextMillis(self::$lastTimestamp);
            }
        } else {
            self::$lastSequence = 0;
        }

        self::$lastTimestamp = $timestamp;

        return (($timestamp - self::$twepoch) << 22) | ($dataCenterId << 17) | ($workerId << 12) | self::$lastSequence;
    }

    /**
     * 反向解析
     *
     * @access public
     * @param int|float $snowFlakeId
     * @return stdClass
     */
    public static function unmake($snowFlakeId)
    {
        $binary = str_pad(decbin($snowFlakeId), 64, '0', STR_PAD_LEFT);

        $Object               = new \stdClass;
        $Object->timestamp    = bindec(substr($binary, 0, 42)) + self::$twepoch;
        $Object->dataCenterId = bindec(substr($binary, 42, 5));
        $Object->workerId     = bindec(substr($binary, 47, 5));
        $Object->sequence     = bindec(substr($binary, -12));

        return $Object;
    }

    /**
     * 等待下一毫秒的时间戳
     *
     * @access private
     * @param $lastTimestamp
     * @return float
     */
    private static function tilNextMillis($lastTimestamp)
    {
        $timestamp = self::timeGen();

        while ($timestamp <= $lastTimestamp) {
            $timestamp = self::timeGen();
        }

        return $timestamp;
    }

    /**
     * 获取毫秒级时间戳
     *
     * @access private
     * @return float
     */
    private static function timeGen()
    {
        return (float)sprintf('%.0f', microtime(true) * 1000);
    }
}