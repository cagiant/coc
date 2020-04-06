<?php


namespace coc\utils;


class Math
{
    public static function division($number, $divisor, $precision = 2)
    {
        return number_format($number/$divisor, $precision);
    }

    public static  function arraySort($arr, $key, $order=SORT_ASC)
    {
        $newArr = [];
        $sortAbleArr = [];
        $keyMap = [];
        $index = 'tag';
        if (count($arr) > 0) {
            foreach ($arr as $k => $v) {
                if (is_int($k)) {
                    $nk = $index . $k;
                } else {
                    $nk = $k;
                }
                $keyMap[$nk] = $k;
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $key) {
                            $sortAbleArr[$nk] = $v2;
                        }
                    }
                } else {
                    $sortAbleArr[$nk] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortAbleArr);
                    break;
                case SORT_DESC:
                    arsort($sortAbleArr);
                    break;
            }

            foreach ($sortAbleArr as $k => $v) {
                $newArr[] = $arr[$keyMap[$k]];
            }
        }

        return $newArr;
    }
}