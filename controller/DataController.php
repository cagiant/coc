<?php


namespace coc\controller;


use coc\constants\Constants;
use coc\services\FetchData;
use coc\services\SyncClanInfo;
use coc\services\SyncClanWarInfo;
use coc\services\SyncLeagueGroupWarInfo;
use coc\utils\Response;

class DataController
{
    public function getCurrentSeasonClanWarData()
    {
        $a = new FetchData();
        $result = $a->getCurrentSeasonClanWarData();

        Response::jsonRes($result);
    }

    public function getClanInfo()
    {
        $a = new FetchData();
        $result = $a->getCurrentWarClanOptionInfo();

        Response::jsonRes($result);
    }

    public function getLeagueGroupWarInfos()
    {
        $a           = new FetchData();
        $result      = $a->leagueGroupWarInfos();
        $this->displayWarData($result);
    }

    /**
     * 刷新当前战争信息，用于给前端实时同步
     * author: guokaiqiang
     * date: 2020/4/11 20:31
     */
    public function refreshCurrentWarInfo()
    {
        $a = new SyncLeagueGroupWarInfo();
        $a->syncOnce();
        $b = new SyncClanWarInfo();
        $b->syncInfo();
        if (date("i") % 10 == 0) {
            $c = new SyncClanInfo();
            $c->syncInfo();
        }
    }

    public function currentWarData()
    {
        $a = new FetchData();
        $result = $a->currentWarData();

        $this->displayWarData($result);
    }

    private function displayWarData(array $result)
    {
        $summaryData = $result[0];
        $detailData  = $result[1];
        if (!empty($summaryData)) {
            $result = [
                'code' => Constants::API_RETURN_CODE_OK,
                'data' => [
                    'summary' => $summaryData,
                    'detail'  => $detailData,
                ]
            ];
        } else {
            $result = [
                'msg' => '暂无数据',
            ];
        }

        Response::jsonRes($result);
    }
}