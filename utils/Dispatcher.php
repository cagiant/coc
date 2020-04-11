<?php

namespace coc\utils;


class Dispatcher
{
    public static $routes = [];

    public function addRouter($requestType, $urlPath, $class, $functionName = 'index')
    {
        if (!isset(self::$routes[$requestType])) {
            self::$routes[$requestType] = [];
        }

        self::$routes[$requestType][$urlPath] = [
            $class,
            $functionName,
        ];
    }

    public function dispatch()
    {
        $requestType = strtolower($_SERVER['REQUEST_METHOD']);
        $urlPath     = $_SERVER['REQUEST_URI'];
        if (!isset(self::$routes[$requestType][$urlPath])) {
            Response::displayPage(ROOT . DIRECTORY_SEPARATOR . "frontend" . DIRECTORY_SEPARATOR . "errors" . DIRECTORY_SEPARATOR . "404.html");
        }
        $class    = self::$routes[$requestType][$urlPath][0];
        $function = self::$routes[$requestType][$urlPath][1];
        (new $class())->$function();
    }
}