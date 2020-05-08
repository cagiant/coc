<?php


namespace coc\utils;


class MsgUtil
{
    public static $adjs = [
        'defense' => [
            '天菜一号'  => '轻轻地',
            'Seven' => '漫不经心地',
        ],
        'attack'  => [
            '天菜一号' => '用屁股',
            '天菜二号' => '用十分之一的力气',
        ],
    ];

    public static function generateDetailMsg($type, $name, $star, $percent)
    {
        $adj = self::getAdj($type, $name);
        return sprintf("%s %s %s， %s了 %d 星， %.2f 摧毁率",
            $name,
            $adj,
            $type == 'defense' ? '防守' : '进攻',
            $type == 'defense' ? '被打' : '打',
            $star,
            $percent
        );
    }

    private static function getAdj($type, $name)
    {
        $adj = '全力';
        if (isset(self::$adjs[$type][$name])) {
            $adj = self::$adjs[$type][$name];
        }

        return $adj;
    }
}