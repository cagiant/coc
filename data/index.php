<?php
require "../autoload.php";

use coc\services\GetData;
use coc\constants\Constants;

$a           = new GetData();
$summaryData = $a->getSummaryData();
if (!empty($summaryData)) {
    echo json_encode(
        [
            'code' => Constants::API_RETURN_CODE_OK,
            'data' =>$summaryData,
        ]
    );
} else {
    echo json_encode(
        [
            'msg' => '暂无数据',
        ]
    );
}
exit();
