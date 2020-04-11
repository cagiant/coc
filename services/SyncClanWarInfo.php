<?php


namespace coc\services;


use coc\config\Config;
use coc\dao\MyDB;
use coc\utils\CocException;
use coc\utils\Logger;
use coc\utils\TimeUtil;

class SyncClanWarInfo extends AbstractSyncInfo
{

    public function syncInfo()
    {
        Logger::log("开始获取当前对战信息");
        $url  = sprintf(Config::$apiGetClanCurrentWar, urlencode(Config::$myClanTag));
        $data = $this->getData($url);
        Logger::log("获取当前对战信息完毕");

        if (empty($data)) {
            throw new CocException('获取数据失败');
        }

        if (empty($data['state'])) {
            throw new CocException('数据格式不正确');
        }

        Logger::log("正在保存战争基础信息");
        $warId = $this->saveWarInfo($data);
        Logger::log("保存战争基础信息完毕");
        Logger::log("正在保存战争详细信息");
        $this->saveWarMemberInfo($data, $warId);
        Logger::log("保存战争详细信息结束");
    }

    private function saveWarInfo(array $data)
    {
        $data = $this->transferTime($data);
        $sql  = sprintf("
            INSERT INTO `coc_clan_wars`(`clan_tag` ,`team_size` ,`state` ,`preperation_start_time` ,`start_time` ,`end_time` ,`opponent_clan_tag` ,`snap_shot` ,`created` ,`updated` )
            VALUES('%s', '%s','%s','%s','%s','%s','%s','%s', now(), now())
            ON DUPLICATE KEY UPDATE `state` = values(`state`),
            `snap_shot` = values(`snap_shot`),
            `updated` = now()",
            $data['clan']['tag'],
            $data['teamSize'],
            $data['state'],
            $data['preparationStartTime'],
            $data['startTime'],
            $data['endTime'],
            $data['opponent']['tag'],
            json_encode($data)
        );

        return MyDB::db()->insert($sql);
    }

    private function transferTime($data)
    {
        foreach ($data as $key => &$v) {
            if (strpos(strtolower($key), 'time') !== false) {
                $v = TimeUtil::convertUTC2LocalTime(substr($v, 0, -5));
            }
        }

        return $data;
    }

    private function saveWarMemberInfo($data, $warId)
    {
        $members         = $data['clan']['members'];
        $clanTag         = $data['clan']['tag'];
        $opponentMembers = $data['opponent']['members'];
        $attacks         = [];
        $memberStr       = '';
        foreach ($members as $member) {
            $memberStr .= ",(" . sprintf("'%s', '%s', '%s', '%s', '%s', now(), now()", $warId, $clanTag, $member['tag'], $member['mapPosition'], $member['opponentAttacks']) . ")";
            if (!empty($member['attacks'])) {
                $attacks   = array_merge($attacks, $member['attacks']);
            }
        }
        $memberStr = substr($memberStr, 1);
        $sql = sprintf("
            INSERT INTO `coc_clan_war_members`
            (`war_id` ,`clan_tag` ,`tag`,`map_position`,`opponent_attacks`,`created` ,`updated`)
            VALUES %s ON DUPLICATE KEY UPDATE
            `opponent_attacks` = values(`opponent_attacks`),
            `updated` = now()
            ", $memberStr);
        MyDB::db()->exec($sql);

        foreach ($opponentMembers as $member) {
            if (!empty($member['attacks'])) {
                $attacks   = array_merge($attacks, $member['attacks']);
            }
        }

        if (!empty($attacks)) {
            $attackStr = '';
            foreach ($attacks as $attack) {
                $attackStr .= ",(" . sprintf("'%s', '%s','%s', '%s', %.2f, '%s', now(), now()", $warId, $attack['attackerTag'], $attack['defenderTag'], $attack['stars'], $attack['destructionPercentage'], $attack['order']) . ")";
            }
            $attackStr = substr($attackStr, 1);
            $sql = sprintf("
                INSERT INTO `coc_clan_war_details`
                (`war_id`,`attacker_tag` ,`defender_tag` ,`stars` ,`destruction_percentage` ,`attack_order` ,`created` ,`updated` )
                VALUES %s ON DUPLICATE KEY UPDATE
                `updated` =  now() 
            ", $attackStr);
            MyDB::db()->exec($sql);
        }
    }
}