<?php
namespace coc\services;

use coc\config\Config;

abstract class AbstractSyncInfo
{
    public function getData($url)
    {
        $data = json_decode($this->get($url), true);

        if (!is_array($data)) {
            $data = [];
        }

        return $data;
    }

    public function get($url)
    {
        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer " . Config::$token,
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60 * 3);//超时时间3分钟

        return curl_exec($ch);
    }

    public abstract function syncInfo();
}