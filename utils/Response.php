<?php


namespace coc\utils;


class Response
{
    public static function jsonRes(array $res)
    {
        header('Content-Type: application/json');
        echo json_encode($res);
        exit();
    }

    public static function displayPage($filePath)
    {
        if (!file_exists($filePath)) {
            $filePath = ROOT . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . '404.html';
        }
        require $filePath;
        exit();
    }
}