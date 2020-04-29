<?php


namespace coc\utils;


class MsgUtil
{

    public static function generateDetailMsg($type, $name, $star, $percent)
    {
        return sprintf("%s全力%s， %s了 %d 星， %.2f 摧毁率",
            $name,
            $type == 'defense' ? '防守' : '进攻',
            $type == 'defense' ? '被打' : '打',
            $star,
            $percent
        );
    }
}