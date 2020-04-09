<?php
require "../autoload.php";

use coc\services\GetData;
use coc\constants\Constants;

$a           = new GetData();
$summaryData = $a->getSummaryData();
$detailData  = $a->getDetailData();
if (!empty($summaryData)) {
    echo json_encode(
        [
            'code' => Constants::API_RETURN_CODE_OK,
            'data' => [
                'summary' => $summaryData,
                'detail'  => $detailData,
            ]
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
