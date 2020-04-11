<?php


namespace coc\controller;


use coc\config\SyncOptionConfig;
use coc\utils\CocException;
use coc\utils\Logger;

class CliController
{
    public function syncOptDispatch($opts)
    {
        $opt = empty($opts[1]) ? '' : $opts[1];

        $optConfig = SyncOptionConfig::getConfig($opt);

        if (empty($optConfig)) {
            throw new CocException('不支持的参数类型');
        }

        $class     = $optConfig[0];
        $function  = $optConfig[1];
        $startTime = microtime(true);

        (new $class())->$function();

        Logger::log(sprintf("all done. total time: %.2f s, memory use: %.2f MB", round((microtime(true) - $startTime), 2), memory_get_usage() / (1024 * 1024)));
    }
}