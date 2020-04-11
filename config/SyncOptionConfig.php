<?php


namespace coc\config;


class SyncOptionConfig
{
    public static $optionMap = [
        'league_group_base_info' => [
            'coc\services\SyncLeagueGroupBaseInfo',
            'syncInfo',
        ],
        'league_group_war_info' => [
            'coc\services\SyncLeagueGroupWarInfo',
            'syncInfo',
        ],
        'clan_base_info' => [
            'coc\services\SyncClanInfo',
            'syncInfo',
        ],
        'clan_war_info' => [
            'coc\services\SyncClanWarInfo',
            'syncInfo',
        ],
    ];

    public static function getConfig($args)
    {
        $result = [];
        if (isset(self::$optionMap[$args])) {
            $result = self::$optionMap[$args];
        }

        return $result;
    }
}