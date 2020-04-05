<?php
require '../autoload.php';

use coc\services\SyncLeagueGroupBaseInfo;
use coc\services\SyncLeagueGroupWarInfo;
use coc\utils\Logger;

$startTime = microtime(true);
$a = new SyncLeagueGroupBaseInfo();
$a->syncInfo();

$a = new SyncLeagueGroupWarInfo();
$a->syncInfo();

Logger::log(sprintf("all done. total time: %.2f s, memory use: %.2f MB", round((microtime(true) - $startTime), 2), memory_get_usage()/ (1024*1024)));