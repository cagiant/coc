<?php
require "../autoload.php";

use coc\services\SyncLeagueGroupWarInfo;

$a = new SyncLeagueGroupWarInfo();
$a->syncOnce();