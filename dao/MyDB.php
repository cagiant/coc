<?php
namespace coc\dao;

use coc\config\Config;

class MyDB
{
    public static $db;

    public static function db() {
         if (!self::$db) {
             self::$db = new LibPDO(Config::$dbConfig);
         }

         return self::$db;
    }
}