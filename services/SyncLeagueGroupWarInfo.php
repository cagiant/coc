<?php


namespace coc\services;


use coc\config\Config;
use coc\constants\Constants;
use coc\dao\MyDB;
use coc\utils\Logger;
use coc\utils\TimeUtil;

class SyncLeagueGroupWarInfo extends AbstractSyncInfo
{

    public function syncInfo()
    {

        $sql = sprintf("SELECT `tag` FROM `coc_league_group_wars` WHERE state = '%s' or (state = '%s' and end_time > updated)  order by created desc",
            Constants::WAR_STATE_IN_WAR,
            Constants::WAR_STATE_WAR_END
        );
        $warTags = MyDB::db()->getCol($sql);

        foreach ($warTags as $key => $warTag) {
            Logger::log(sprintf("开始获取战争信息，标签 %s", $warTag));
            $url = sprintf(Config::$apiGetWarInfoUrl , urlencode($warTag));
            $data = $this->getData($url);
            Logger::log(sprintf("获取战争信息结束，标签 %s", $warTag));

            if (empty($data)) {
                throw new \Exception('获取数据失败');
            }

            if (empty($data['state']) || empty($data['clan']) || empty($data['opponent'])) {
                throw new \Exception('数据格式不正确');
            }
            Logger::log(sprintf("______________________%d/%d, 开始保存战争信息______________________标签 %s", $key+ 1, count($warTags), $warTag));
            $this->saveWarData($data, $warTag);
            Logger::log(sprintf("______________________%d/%d, 保存战争信息结束______________________标签 %s", $key + 1, count($warTags), $warTag));
        }
    }

    private function saveWarData($data, $warTag)
    {
        Logger::log(sprintf("更新战争信息....标签 %s", $warTag));
        $this->updateWarInfo($data, $warTag);
        Logger::log(sprintf("更新战争信息结束....标签 %s", $warTag));
        Logger::log(sprintf("更新战争对战双方信息....标签 %s", $warTag));
        $this->updateWarClans($data, $warTag);
        Logger::log(sprintf("更新战争对战双方信息结束....标签 %s", $warTag));
    }

    private function updateWarInfo($data, $warTag)
    {
        $data = $this->transferTime($data);
        $sql = sprintf("
            UPDATE `coc_league_group_wars`
                SET    
                   `state`= '%s',
                   `team_size`= %d,
                   `preparation_start_time`= '%s',
                   `start_time`= '%s',
                   `end_time`= '%s',
                   `war_start_time`= '%s',
                   `clan_tag` = '%s',
                   `opponent_clan_tag` = '%s',
                   updated = now()
            WHERE `tag`= '%s'",
                $data['state'],
                $data['teamSize'],
                $data['preparationStartTime'],
                $data['startTime'],
                $data['endTime'],
                $data['warStartTime'],
                $data['clan']['tag'],
                $data['opponent']['tag'],
                $warTag
        );
        
        MyDB::db()->exec($sql);
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

    private function updateWarClans($data, $warTag)
    {
        $this->updateWarClanInfo($data['clan'], $warTag);
        $this->updateWarClanInfo($data['opponent'], $warTag);
    }

    private function updateWarClanInfo($clanInfo, $warTag)
    {
        $clanTag = $clanInfo['tag'];
        $level = $clanInfo['clanLevel'];
        $attacks = $clanInfo['attacks'];
        $stars = $clanInfo['stars'];
        $destructionPercentage = $clanInfo['destructionPercentage'];

        $sql = sprintf("
            INSERT INTO `coc_league_group_war_clan_info`(`war_tag` , `clan_tag` , `level` , `attacks` , `stars` , `destruction_percentage` , `created` , `updated` )
            VALUES (
                '%s',
                '%s',
                %d,
                %d,
                %d,
                '%.2f',
                now(),
                now()
            ) ON DUPLICATE KEY UPDATE 
                `level`= %d,
                `attacks`= %d,
                `stars`= %d,
                `destruction_percentage`  = %.2f,
                `updated`  = now()
        ",
            $warTag,
            $clanTag,
            $level,
            $attacks,
            $stars,
            $destructionPercentage,
            $level,
            $attacks,
            $stars,
            $destructionPercentage
        );

        MyDB::db()->exec($sql);

        $this->updateWarClanMembers($clanInfo['members'], $warTag, $clanTag);
    }

    private function updateWarClanMembers($members, $warTag, $clanTag)
    {
        foreach ($members as $member) {
            $this->updateWarClanMemberInfo($member, $warTag, $clanTag);
            if (!empty($member['attacks'])) {
                $this->saveWarDetail($member['attacks'], $warTag);
            }
        }
    }

    private function updateWarClanMemberInfo($member, $warTag, $clanTag)
    {
        $sql = sprintf("
            INSERT INTO `coc_league_group_war_clan_member_info`(`war_tag` , `clan_tag` , `member_tag` , `map_position` , `opponent_attacks`, `created`, `updated`)
            VALUES (
                '%s',
                '%s',
                '%s',
                %d,
                %d,
                now(),
                now()
            ) ON DUPLICATE KEY UPDATE
            `opponent_attacks` = %d,
            updated = now()
        ",
            $warTag,
            $clanTag,
            $member['tag'],
            $member['mapPosition'],
            $member['opponentAttacks'],
            $member['opponentAttacks']
        );

        MyDB::db()->exec($sql);
    }

    private function saveWarDetail($attacks, $warTag)
    {
        $str = '';
        foreach ($attacks as $attack) {
            $str .= ',(' . sprintf("'%s', '%s', '%s', %d, %.2f, %d, now(), now()", $warTag, $attack['attackerTag'], $attack['defenderTag'], $attack['stars'], $attack['destructionPercentage'], $attack['order']) . ')';
        }
        $str = substr($str, 1);
        $sql = sprintf("
            INSERT INTO `coc_league_group_war_deails`(`war_tag` , `attacker_tag` , `defender_tag` , `stars` , `destruction_percentage` , `attack_order` , `created` , `updated` )
            VALUES %s
            ON DUPLICATE KEY UPDATE
            `updated` = now() 
        ",
            $str
        );

        MyDB::db()->exec($sql);
    }
}