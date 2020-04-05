<?php


namespace coc\utils;


class Math
{
    public static function division($number, $divisor, $precision = 2)
    {
        return number_format($number/$divisor, $precision);
    }
}