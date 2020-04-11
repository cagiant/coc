<?php

require "../autoload.php";

use coc\controller\CliController;

date_default_timezone_set("Asia/Shanghai");

$a = new CliController();
$a->syncOptDispatch($argv);
