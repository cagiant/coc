<?php
require "autoload.php";
use coc\utils\Dispatcher;

date_default_timezone_set("Asia/Shanghai");
define("ROOT", __DIR__);
$dispatcher = new Dispatcher();
$dispatcher->addRouter('get', '/', 'coc\controller\WelcomeController');
$dispatcher->addRouter('get', '/leagueGroupData', 'coc\controller\DataController', 'getLeagueGroupWarInfos');
$dispatcher->addRouter('get', '/refreshCurrentWarInfo', 'coc\controller\DataController', 'refreshCurrentWarInfo');
$dispatcher->addRouter('get', '/currentWarData', 'coc\controller\DataController', 'getCurrentSeasonClanWarData');
$dispatcher->addRouter('post', '/currentWarData', 'coc\controller\DataController', 'getCurrentSeasonClanWarData');
$dispatcher->addRouter('get', '/currentWarDataClanInfo', 'coc\controller\DataController', 'getClanInfo');
$dispatcher->dispatch();