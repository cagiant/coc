<?php


namespace coc\utils;


class Logger
{
    public static function log($str)
    {
        echo sprintf("%s: %s", date("Y-m-d H:i:s"), $str) . "\n";
    }
}