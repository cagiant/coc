<?php


namespace coc\controller;



use coc\services\FetchData;
use coc\utils\Response;

class DataController
{
    /**
     * 获取当前赛季的部落战报表
     * author: guokaiqiang
     * date: 2020/8/16 13:09
     */
    public function getCurrentSeasonClanWarData()
    {
        $a = new FetchData();
        $result = $a->getCurrentSeasonClanWarData();

        Response::jsonRes($result);
    }

    /**
     * 获取前端可以选择的部落信息
     * author: guokaiqiang
     * date: 2020/8/16 13:09
     */
    public function getClanInfo()
    {
        $a = new FetchData();
        $result = $a->getCurrentWarClanOptionInfo();

        Response::jsonRes($result);
    }

    public function getCurrentSeasonLeagueWarClanInfo()
    {
        $a = new FetchData();
        $result = $a->getCurrentSeasonLeagueWarClanInfo();

        Response::jsonRes($result);
    }
}