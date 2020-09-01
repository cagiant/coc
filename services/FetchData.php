<?php


namespace coc\services;


use coc\config\Config;
use coc\dao\MyDB;

class FetchData
{
    public function getCurrentSeasonClanWarData()
    {
        $tag = $_POST['tag'] ?: Config::$myClanTag;
        $season = $_POST['season'] ?: date("Y-m");
        $sql = sprintf("SELECT
            ccm.name,
            ccwm.attack_no_star_time AS at_no_star,
            ccwm.attack_one_star_time AS at_one_star,
            ccwm.attack_two_star_time AS at_two_star,
            ccwm.attack_three_star_time AS at_three_star,
            ccwm.defense_three_star_time AS df_three_star,
            ccwm.attack_time_left AS at_time_left,
            ccwm.attack_time_used AS at_time_used 
        FROM
            coc_report_clan_war_member ccwm 
            JOIN coc_clans cc on cc.tag = ccwm.clan_tag 
            JOIN coc_clan_members ccm ON ccwm.member_tag = ccm.tag 
        WHERE
            ccwm.clan_tag = '%s' 
            AND ccwm.season = '%s' 
        ORDER BY
            at_three_star DESC,
            at_two_star DESC,
            at_one_star DESC,
            at_no_star DESC,
            df_three_star ASC", $tag, $season);

        return [
            'season' => $season,
            'detail' => MyDB::db()->getAll($sql),
        ];
    }

    public function getCurrentWarClanOptionInfo()
    {
        $sql = sprintf("SELECT
            tag,name
            from coc_clans
            where provide_clan_war_report = 1
        ");

        $sqlSeason = sprintf("select season, season as 'name' from coc_report_clan_war_member group by season");

        return [
            'options' => MyDB::db()->getAll($sql),
            'seasonOptions' => MyDB::db()->getAll($sqlSeason),
        ];
    }
}