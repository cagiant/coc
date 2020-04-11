<?php


namespace coc\services;


use coc\config\Config;
use coc\dao\MyDB;
use coc\utils\CocException;
use coc\utils\Logger;

class SyncClanInfo extends AbstractSyncInfo
{
    public function syncInfo()
    {
        Logger::log("开始获取部落基础信息");
        $url = sprintf(Config::$apiGetClanInfo, urlencode(Config::$myClanTag));
        $data = $this->getData($url);
        Logger::log("获取部落基础信息完毕");

        if (empty($data)) {
            throw new \Exception('获取数据失败');
        }

        if (empty($data['tag'])) {
            throw new \Exception('数据格式不正确');
        }

        Logger::log("正在保存部落基础信息");
        $this->saveInfo($data);
        Logger::log("保存部落信息完毕");

        Logger::log("正在保存部落成员信息");
        $this->saveMemberInfo($data['memberList'], $data['tag']);
        Logger::log("保存部落成员信息结束");
    }

    private function saveInfo(array $data)
    {
        $sql = sprintf("
            INSERT INTO `coc_clans`(
            `tag` , `name` ,`details` ,`created`,`updated`
            )values(
                '%s', '%s', '%s', now(), now()
            ) ON DUPLICATE KEY UPDATE `details`  = '%s', `updated`  = now(); 
        ",
            $data['tag'],
            $data['name'],
            json_encode($data),
            json_encode($data)
        );
        MyDB::db()->exec($sql);
    }

    /**
     * 保存部落的成员信息
     * @param $memberList
     * @param $clanTag
     * @throws CocException
     * author: guokaiqiang
     * date: 2020/4/12 08:49
     */
    private function saveMemberInfo($memberList, $clanTag)
    {
        if (empty($memberList)) {
            throw new CocException("成员信息不可以为空");
        }
        $str = '';
        foreach ($memberList as $member) {
            $str .= ",(" . sprintf("'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', now(), now()",
                    $clanTag,
                    $member['tag'],
                    $member['name'],
                    $member['role'],
                    $member['expLevel'],
                    $member['trophies'],
                    $member['versusTrophies'],
                    $member['clanRank'],
                    $member['previousClanRank'],
                    $member['donations'],
                    $member['donationsReceived']
            ) . ")";
        }

        $str = substr($str, 1);

        $sql = sprintf("
            INSERT INTO `coc_clan_members`
                (`clan_tag` ,`tag` ,`name` ,`role` ,`exp_level` ,`trophies` ,`versus_trophies` ,`clan_rank` ,`previous_clan_rank` ,`donations` ,`donations_received`, `created` ,`updated`)
                values %s ON DUPLICATE KEY UPDATE
                `name` = values(`name`),
                `role` = values(`role`),
                `exp_level` = values(`exp_level`),
                `trophies` = values(`trophies`),
                `versus_trophies` = values(`versus_trophies`),
                `clan_rank` = values(`clan_rank`),
                `previous_clan_rank` = values(`previous_clan_rank`),
                `donations` = values(`donations`),
                `donations_received` = values(`donations_received`),
                `updated` = now()",
                $str
        );
        MyDB::db()->exec($sql);
    }


}