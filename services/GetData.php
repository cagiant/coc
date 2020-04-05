<?php


namespace coc\services;


use coc\config\Config;
use coc\dao\MyDB;
use coc\utils\Math;

class GetData
{
    public function getSummaryData()
    {
        $season      = date('Y-m');
        $attackInfo  = $this->getAttackInfo($season);
        $defenseInfo = $this->getDefenseInfo($season);

        return $this->summaryData($attackInfo, $defenseInfo);
    }

    private function getAttackInfo($season)
    {
        $sql = sprintf("
            SELECT 
                sum(war_detail.`stars`) as `total_star`,
                sum(war_detail.`destruction_percentage`) as `destruction_percentage`,
                count(war_detail.id) as `total_attack`,
                base_clan_member_info.`name`,
                base_clan_member_info.`tag`
            from `coc_league_group_wars` war join `coc_league_group` league_info on war.`league_group_id`= league_info.`id` 
                join `coc_league_group_war_deails` war_detail on war.`tag`= war_detail.`war_tag` 
                join `coc_league_group_war_clan_info` war_clan_info on war_clan_info.`clan_tag`= '%s' and war_clan_info.`war_tag`= war.`tag` 
                join `coc_league_group_war_clan_member_info` war_clan_member_info on war_clan_member_info.`clan_tag`= war_clan_info.`clan_tag`and war_clan_member_info.`member_tag`= war_detail.`attacker_tag` and war_clan_member_info.`war_tag`= war.`tag` 
                join `coc_league_group_clan_members` base_clan_member_info on base_clan_member_info.`tag`= war_clan_member_info.`member_tag`
            where 
                league_info.`season`= '%s'
            group by base_clan_member_info.`tag` ",
            Config::$myClanTag,
            $season
        );

        return MyDB::db()->getAll($sql);
    }

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
            $attack['avg_attack_star']     = Math::division($attack['total_star'], $attack['total_attack']);
            $attack['avg_attack_percent']  = Math::division($attack['destruction_percentage'], $attack['total_attack']);
            $attackTagHash[$attack['tag']] = $attack;
        }

        foreach ($defenseInfo as $defense) {
            $defense['avg_defense_star']     = Math::division($defense['defense_total_star'], $defense['defense_total_attack']);
            $defense['avg_defense_percent']  = Math::division($defense['defense_destruction_percentage'], $defense['defense_total_attack']);
            $defenseTagHash[$defense['tag']] = $defense;
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

        return $result;
    }
}