<?php


namespace coc\utils;


class TimeUtil
{
    public static function convertUTC2LocalTime($utcStr)
    {
        $dt = new \DateTime($utcStr, new \DateTimeZone('UTC'));
        $tz = new \DateTimeZone('Asia/ShangHai');
        $dt->setTimezone($tz);

        return $dt->format('Y-m-d H:i:s');
    }
}