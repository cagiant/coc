<?php
require "autoload.php";
use coc\utils\Dispatcher;

date_default_timezone_set("Asia/Shanghai");
define("ROOT", __DIR__);
$dispatcher = new Dispatcher();
$dispatcher->addRouter('get', '/', 'coc\controller\WelcomeController');
$dispatcher->addRouter('post', '/proxy', 'coc\controller\ProxyController');
$dispatcher->addRouter('get', '/currentWarData', 'coc\controller\DataController', 'getCurrentSeasonClanWarData');
$dispatcher->addRouter('get', '/leagueGroupWarDataClanInfo', 'coc\controller\DataController', 'getCurrentSeasonLeagueWarClanInfo');
$dispatcher->addRouter('post', '/currentWarData', 'coc\controller\DataController', 'getCurrentSeasonClanWarData');
$dispatcher->addRouter('get', '/currentWarDataClanInfo', 'coc\controller\DataController', 'getClanInfo');
$dispatcher->dispatch();