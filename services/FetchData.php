<?php


namespace coc\services;


use coc\config\Config;
use coc\constants\Constants;
use coc\dao\MyDB;
use coc\utils\Math;
use coc\utils\MsgUtil;

class FetchData
{
    private static function processInfos($infos)
    {
        $infoExtend = [];
        $infoProcessed = [];
        foreach ($infos as $info) {
            $name = $info['name'];
            if (!isset($infoExtend[$name])) {
                $infoExtend[$name] = 1;
            }
            if (empty($info['detail_type'])) {
                continue;
            }
            $info['msg'] = MsgUtil::generateDetailMsg($info['detail_type'], $info['name'], $info['stars'], $info['destruction_percentage']);
            if ($info['detail_type'] == 'attack') {
                $infoExtend[$name] = 0;
            }
            $infoProcessed[] = $info;
        }

        foreach ($infoExtend as $name => $v) {
            if (empty($v)) {
                continue;
            }
            $infoTmp = [
                'name' => $name,
                'msg' => $name . '大佬尚未出手',
                'is_zero_attack' => true,
            ];
            $infoProcessed[] = $infoTmp;
        }

        return $infoProcessed;
    }

    public function leagueGroupSummaryData()
    {
        $season      = date('Y-m');
        $attackInfo  = $this->getAttackInfo($season);
        $defenseInfo = $this->getDefenseInfo($season);

        return $this->summaryData($attackInfo, $defenseInfo);
    }

    public function leagueGroupWarDetailData()
    {
        return $this->getLeagueGroupWarDetail();
    }

    /**
     * 进攻时，如果没有打，算打了零星，总进攻次数按照上场次数来算。
     * @param $season
     * @return bool
     * author: guokaiqiang
     * date: 2020/4/6 13:35
     */
    private function getAttackInfo($season)
    {
        $sql = sprintf("
            SELECT 
                SUM(war_detail.`stars`) AS `total_star`,
                SUM(war_detail.`destruction_percentage`) AS `destruction_percentage`,
                COUNT(war_clan_member_info.id) AS `total_attack`,
                base_clan_member_info.`name`,
                base_clan_member_info.`tag`
            FROM
                `coc_league_group_wars` war
                    JOIN
                `coc_league_group` league_info ON war.`league_group_id` = league_info.`id`
                    JOIN
                `coc_league_group_war_clan_member_info` war_clan_member_info ON war_clan_member_info.`war_tag` = war.`tag`
                    JOIN
                `coc_league_group_clan_members` base_clan_member_info ON base_clan_member_info.`tag` = war_clan_member_info.`member_tag`
                    JOIN
                `coc_league_group_clans` base_clan_info ON base_clan_info.`league_group_id` = league_info.`id`
                    AND base_clan_info.`id` = base_clan_member_info.`league_group_clan_id`
                    LEFT JOIN
                `coc_league_group_war_deails` war_detail ON war_detail.`war_tag` = war.`tag`
                    AND war_detail.`attacker_tag` = base_clan_member_info.`tag`
            WHERE
                base_clan_info.`tag` = '%s'
                    AND league_info.`season` = '%s'
                    AND war.`state` IN ('%s' , '%s')
            GROUP BY base_clan_member_info.`tag` ",
            Config::$myClanTag,
            $season,
            Constants::WAR_STATE_IN_WAR,
            Constants::WAR_STATE_WAR_END
        );

        return MyDB::db()->getAll($sql);
    }

    /**
     * 防守时，如果没被打，跳过该次计算。上场次数不计入总被打次数。
     * @param $season
     * @return bool
     * author: guokaiqiang
     * date: 2020/4/6 13:35
     */
    private function getDefenseInfo($season)
    {
        $sql = sprintf("
            SELECT 
                 sum(war_detail.`stars`) as `defense_total_star`,
                 sum(war_detail.`destruction_percentage`) as `defense_destruction_percentage`,
                 count(war_detail.id) as `defense_total_attack`,
                 base_clan_member_info.`name`,
                 base_clan_member_info.`tag`
            from `coc_league_group_wars` war 
                join `coc_league_group` league_info on war.`league_group_id`= league_info.`id` 
                join `coc_league_group_war_deails` war_detail on war.`tag`= war_detail.`war_tag` 
                join `coc_league_group_war_clan_info` war_clan_info on war_clan_info.`clan_tag`= '%s' and war_clan_info.`war_tag`= war.`tag` 
                join `coc_league_group_war_clan_member_info` war_clan_member_info on war_clan_member_info.`clan_tag`= war_clan_info.`clan_tag` and war_clan_member_info.`member_tag`= war_detail.`defender_tag`  and war_clan_member_info.`war_tag`= war.`tag`
                join `coc_league_group_clan_members` base_clan_member_info on base_clan_member_info.`tag`= war_clan_member_info.`member_tag`
            where 
                league_info.`season`= '%s'
            group by base_clan_member_info.`tag` ",
            Config::$myClanTag,
            $season
        );

        return MyDB::db()->getAll($sql);
    }

    /**
     * 汇总信息，然后按照攻防比从高到低排序。攻防比= 平均进攻星星/平均防守星星
     * @param $attackInfo
     * @param $defenseInfo
     * @return array
     * author: guokaiqiang
     * date: 2020/4/6 13:33
     */
    private function summaryData($attackInfo, $defenseInfo)
    {
        $attackTagHash  = [];
        $defenseTagHash = [];

        $baseInfoAttack = [
            'total_star'             => 0,
            'destruction_percentage' => 0,
            'avg_attack_star'        => 0,
            'avg_attack_percent'     => 0,
            'adr'                    => 0,
        ];

        $baseInfoDefense = [
            'defense_total_star'             => 0,
            'defense_destruction_percentage' => 0,
            'avg_defense_star'               => 0,
            'avg_defense_percent'            => 0,
            'adr'                            => 0,
        ];

        foreach ($attackInfo as $attack) {
            $attack['total_star']             = (int)$attack['total_star'];
            $attack['avg_attack_star']        = Math::division($attack['total_star'], $attack['total_attack']);
            $attack['avg_attack_percent']     = Math::division($attack['destruction_percentage'], $attack['total_attack']);
            $attack['destruction_percentage'] = (int)$attack['destruction_percentage'];
            $attackTagHash[$attack['tag']]    = $attack;
        }

        foreach ($defenseInfo as $defense) {
            $defense['defense_total_star']             = (int)$defense['defense_total_star'];
            $defense['avg_defense_star']               = Math::division($defense['defense_total_star'], $defense['defense_total_attack']);
            $defense['avg_defense_percent']            = Math::division($defense['defense_destruction_percentage'], $defense['defense_total_attack']);
            $defense['defense_destruction_percentage'] = (int)$defense['defense_destruction_percentage'];
            $defenseTagHash[$defense['tag']]           = $defense;
        }

        $result = [];

        foreach ($attackTagHash as $tag => $attackInfo) {
            if (!isset($defenseTagHash[$tag])) {
                $defenseInfo = $baseInfoDefense;
            } else {
                $defenseInfo        = $defenseTagHash[$tag];
                $defenseInfo['adr'] = Math::division($attackInfo['avg_attack_star'], $defenseInfo['avg_defense_star']);
            }

            $result[] = array_merge($attackInfo, $defenseInfo);
            unset($attackTagHash[$tag]);
            unset($defenseTagHash[$tag]);
        }

        foreach ($defenseTagHash as $tag => $defenseInfo) {
            $result[] = array_merge($defenseInfo, $baseInfoAttack);
        }

        $result = Math::arraySort($result, 'adr', SORT_DESC);

        return $result;
    }

    /**
     * 如果在联赛中，展示联赛信息
     * 否则展示最近一场战争的信息
     * author: guokaiqiang
     * date: 2020/4/12 14:30
     */
    public function currentWarData()
    {
        $isInLeague = $this->isInLeague();

        if ($isInLeague) {
            $result = $this->leagueGroupWarInfos();
        } else {
            $result = $this->lastClanWarInfo();
        }

        return $result;
    }

    private function isInLeague()
    {
        $season = date('Y-m');
        $sql    = sprintf("select * from coc_league_group where season = '%s' and state != 'ended'", $season);
        $result = MyDB::db()->getOne($sql);

        return !empty($result);
    }

    public function leagueGroupWarInfos()
    {
        $summaryData = $this->leagueGroupSummaryData();
        $detailData  = $this->leagueGroupWarDetailData();

        return [
            $summaryData,
            $detailData,
        ];
    }

    private function lastClanWarInfo()
    {
        $summaryData = $this->clanWarSummaryData();
        $detailData  = $this->clanWarDetailData();

        return [
            $summaryData,
            $detailData,
        ];
    }

    public function clanWarSummaryData()
    {
        $attackSummary  = $this->clanWarAttackSummary();
        $defenseSummary = $this->clanWarDefenseSummary();

        return $this->summaryData($attackSummary, $defenseSummary);
    }

    private function clanWarAttackSummary()
    {
        $attackSql = sprintf("
            SELECT 
                IFNULL(SUM(war_detail.`stars`), 0) AS `total_star`,
                IFNULL(SUM(war_detail.`destruction_percentage`), 0) AS `destruction_percentage`,
                2 AS `total_attack`,
                member.name,
                member.tag
            FROM
                `coc_clan_wars` war
                    JOIN
                `coc_clan_war_members` war_member ON war_member.`war_id` = war.`id`
                    LEFT JOIN
                `coc_clan_war_details` war_detail ON war_detail.`war_id` = war.`id`AND war_member.`tag` = war_detail.`attacker_tag`
                    JOIN
                `coc_clan_members` member ON member.`tag` = war_member.`tag`
            WHERE
                war.`clan_tag` = '%s'
                    AND war.`end_time` = (SELECT 
                        MAX(end_time)
                    FROM
                        `coc_clan_wars`) 
            GROUP BY member.tag
        ", Config::$myClanTag);

        return MyDB::db()->getAll($attackSql);
    }

    private function clanWarDefenseSummary()
    {
        $defenseSql = sprintf("
            SELECT 
                SUM(war_detail.`stars`) AS `defense_total_star`,
                SUM(war_detail.`destruction_percentage`) AS `defense_destruction_percentage`,
                count(war_detail.id) AS `defense_total_attack`,
                member.name,
                member.tag
            FROM
                `coc_clan_wars` war
                    JOIN
                `coc_clan_war_members` war_member ON war_member.`war_id` = war.`id`
                    JOIN
                `coc_clan_war_details` war_detail ON war_detail.`war_id` = war.`id`AND war_member.`tag` = war_detail.`defender_tag`
                    JOIN
                `coc_clan_members` member ON member.`tag` = war_member.`tag`
            WHERE
                war.`clan_tag` = '%s'
                    AND war.`end_time` = (SELECT 
                        MAX(end_time)
                    FROM
                        `coc_clan_wars`) 
            GROUP BY member.tag
        ", Config::$myClanTag);

        return MyDB::db()->getAll($defenseSql);
    }

    private function clanWarDetailData()
    {
        $sql = sprintf("
            SELECT 
                member.`name`,
                war_detail.stars,
                if (war_detail.attacker_tag is null, '', if (war_detail.attacker_tag = war_member.tag, 'attack', 'defense')) as `detail_type`,
                war_detail.`destruction_percentage`,
                IFNULL(war_detail.`attack_order`, 0) AS `attack_order`
            FROM
                `coc_clan_wars` war
                    JOIN
                `coc_clan_war_members` war_member ON war_member.`war_id` = war.`id`
                    LEFT JOIN
                `coc_clan_war_details` war_detail ON war_detail.`war_id` = war.`id`
                    AND (war_detail.`attacker_tag` = war_member.`tag` or war_detail.`defender_tag` = war_member.`tag`)
                    JOIN
                `coc_clan_members` member ON war_member.`tag` = member.`tag`
            WHERE
                war.`clan_tag` = '%s'
                AND war.`end_time` = (SELECT 
                        MAX(end_time)
                    FROM
                        `coc_clan_wars`)
            ORDER BY war_detail.attack_order DESC
        ", Config::$myClanTag);

        $infos = MyDB::db()->getAll($sql);

        return self::processInfos($infos);
    }


    public function getLeagueGroupWarDetail()
    {
        $sql = sprintf("
            SELECT 
                base_member_info.`name`,
                war_details.stars,
                if (war_details.attacker_tag is null, '', if (war_details.attacker_tag = war_member.member_tag, 'attack', 'defense')) as `detail_type`,
                war_details.`destruction_percentage`,
                IFNULL(war_details.`attack_order`, 0) AS `attack_order`
            FROM
                `coc_league_group_war_clan_info` war_clan_relation
                    JOIN
                `coc_league_group_wars` wars ON war_clan_relation.`war_tag` = wars.`tag`
                    JOIN
                `coc_league_group_war_clan_member_info` war_member ON war_member.`war_tag` = wars.`tag`
                    AND war_member.`clan_tag` = war_clan_relation.`clan_tag`
                    LEFT JOIN
                `coc_league_group_war_deails` war_details ON war_details.`war_tag` = wars.`tag`
                    AND (war_details.`attacker_tag` = war_member.`member_tag` or war_details.defender_tag = war_member.member_tag)
                    JOIN
                `coc_league_group_clan_members` base_member_info ON war_member.`member_tag` = base_member_info.`tag`
            WHERE
                war_clan_relation.`clan_tag` = '%s'
                    AND wars.`state` = '%s'
            ORDER BY `attack_order` DESC
        ",
            Config::$myClanTag,
            Constants::WAR_STATE_IN_WAR);

        $infos = MyDB::db()->getAll($sql);

        return self::processInfos($infos);
    }
}