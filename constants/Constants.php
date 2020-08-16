<?php


namespace coc\constants;


class Constants
{
    const WAR_STATE_IN_WAR = 'inWar';
    const WAR_STATE_WAR_END = 'warEnded';
    const WAR_STATE_PREPARATION = 'preparation';

    const API_RETURN_CODE_OK = 1;

    // url constants
    public static $apiGetLeagueGroupInfoUrl = "https://api.clashofclans.com/v1/clans/%s/currentwar/leaguegroup";
    public static $apiGetLeagueWarInfoUrl = "https://api.clashofclans.com/v1/clanwarleagues/wars/%s";
    public static $apiGetClanWarLog = "https://api.clashofclans.com/v1/clans/%s/warlog";
    public static $apiGetClanCurrentWar = "https://api.clashofclans.com/v1/clans/%s/currentwar";
    public static $apiGetClanMembers = 'https://api.clashofclans.com/v1/clans/%s/members';
    public static $apiGetPlayer = "https://api.clashofclans.com/v1/players/%s";
    public static $apiGetClanInfo = "https://api.clashofclans.com/v1/clans/%s";

    public static function getUrl($option, $tag)
    {
        $url = '';
        switch ($option) {
            case 'current_war':
                $url = sprintf(self::$apiGetClanCurrentWar, urlencode($tag));
                break;
            case 'clan_info':
                $url = sprintf(self::$apiGetClanInfo, urlencode($tag));
                break;
            case 'clan_member':
                $url = sprintf(self::$apiGetClanMembers, urlencode($tag));
                break;
            case 'clan_war_log':
                $url = sprintf(self::$apiGetClanWarLog, urlencode($tag));
                break;
            case 'player_info':
                $url = sprintf(self::$apiGetPlayer, urlencode($tag));
                break;
            case 'league_group_info':
                $url = sprintf(self::$apiGetLeagueGroupInfoUrl, urlencode($tag));
                break;
            case 'league_group_war':
                $url = sprintf(self::$apiGetLeagueWarInfoUrl, urlencode($tag));
                break;
            default:
        }

        return $url;
    }
}