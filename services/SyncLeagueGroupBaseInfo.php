<?php

namespace coc\services;

use coc\dao\MyDB;
use coc\config\Config;
use coc\utils\Logger;

class SyncLeagueGroupBaseInfo extends AbstractSyncInfo
{
    public function syncInfo()
    {
        Logger::log("开始获取联赛基础信息");
        $url = sprintf(Config::$apiGetLeagueGroupInfoUrl, urlencode(Config::$myClanTag));
        $data = $this->getData($url);
        Logger::log("获取联赛基础信息完毕");

        if (empty($data)) {
            throw new \Exception('获取数据失败');
        }

        if (empty($data['season'])) {
            throw new \Exception('数据格式不正确');
        }

        Logger::log("正在保存联赛基础信息");
        $this->saveInfo($data);
        Logger::log("保存联赛基础信息完毕");
    }

    private function saveClanInfo($clans, $leagueGroupId)
    {
        foreach ($clans as $clan) {
            $sql = sprintf("
                INSERT INTO `coc_league_group_clans`(
                `league_group_id` , 
                `tag`, 
                `name`, 
                `level`, 
                `created`, 
                `updated`) VALUES (
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    now(), 
                    now()) 
                ON DUPLICATE KEY UPDATE `updated` = now()
            ",
                $leagueGroupId,
                $clan['tag'],
                $clan['name'],
                $clan['clanLevel']
            );
            $leagueGroupClanId = MyDB::db()->insert($sql);
            $this->saveClanMember($clan['members'], $leagueGroupClanId);
        }
    }

    private function saveRoundInfo($rounds, $leagueGroupId)
    {
        $str = '';
        foreach ($rounds as $key =>  $round) {
            $warTags = $round['warTags'];
            foreach ($warTags as $tag) {
                $str .= ',(' . sprintf("%d, %d, '%s', now(), now()", $leagueGroupId, $key + 1,$tag) . ')';
            }
        }
        $str = substr($str, 1);
        $sql = sprintf("
            INSERT INTO `coc_league_group_wars`(`league_group_id` , `round_index` , `tag`, `created` , `updated`)
            VALUES %s
            ON DUPLICATE KEY UPDATE 
                `updated` = now()
            ", $str);

        MyDB::db()->insert($sql);
    }

    private function saveInfo(array $data)
    {
        $leagueGroupId = $this->saveLeagueGroupInfo($data);

        $clans  = $data['clans'];
        $rounds = $data['rounds'];
        $this->saveClanInfo($clans, $leagueGroupId);
        $this->saveRoundInfo($rounds, $leagueGroupId);
    }

    private function saveLeagueGroupInfo(array $data)
    {
        $state  = $data['state'];
        $season = $data['season'];

        $sql = sprintf("INSERT INTO 
            `coc_league_group`(
                `season` , 
                `state`, 
                `created`, 
                `updated`
                ) 
                VALUES 
                (
                '%s', 
                '%s', 
                now(), 
                now()
                ) on DUPLICATE KEY UPDATE  
                `state` = '%s',
                `updated` = now()",
            $season,
            $state,
            $state
        );
        return MyDB::db()->insert($sql);
    }

    private function saveClanMember($members, $leagueGroupClanId)
    {
        $str = '';
        foreach ($members as $member) {
            $str .= ',(' . sprintf("%s, '%s', '%s', '%s', now(), now()", $leagueGroupClanId, $member['tag'], $member['name'], $member['townHallLevel']) . ')';
        }
        $str = substr($str, 1);
        $sql = sprintf("
            INSERT INTO `coc_league_group_clan_members`
            (`league_group_clan_id` , `tag` , `name` , `town_hall_level` , `created` , `updated` ) 
            VALUES %s
            ON DUPLICATE KEY UPDATE
            `updated` = now() 
        ", $str);

        MyDB::db()->insert($sql);
    }
}