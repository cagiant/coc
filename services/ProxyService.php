<?php


namespace coc\services;


use coc\constants\Constants;
use coc\dao\MyDB;
use coc\utils\CocException;
use coc\utils\TimeUtil;

class ProxyService
{
    public $client;

    public $params;

    public $authService;

    public function __construct(iHttpClient $client, iAuth $authService, ProxyParamters $params)
    {
        $this->client = $client;
        $this->params = $params;
        $this->authService = $authService;
    }

    public function doProxy()
    {
        if (!$this->authService->checkSignature($this->params)) {
            throw new CocException("签名校验失败");
        }
        $url = $this->getUrlByParams();
        $result = $this->client->execute($url);
        $result['url']  = $url;

        if (count($result) < 5) {
            return $this->getFromLatestLocal($result);
        }

        return $this->postProcess($result);
    }

    private function getUrlByParams()
    {
        $option = $this->params->option ?? '';
        $tag = $this->params->tag ?? '';
        if (empty($option) || empty($tag)) {
            throw new CocException("需要同时设置请求选项和请求tag");
        }

        return Constants::getUrl($option, $tag);
    }

    private function postProcess($result)
    {
        foreach ($result as $key => &$v) {
            if (strpos(strtolower($key), 'time') !== false) {
                $v = TimeUtil::convertUTC2LocalTime(substr($v, 0, -5));
            }
        }

        $sql = sprintf("insert into coc_proxy_results (`option`, tag, result) values ('%s', '%s', '%s')", $this->params->option, $this->params->tag, json_encode($result));
        MyDB::db()->insert($sql);

        return $result;
    }

    private function getFromLatestLocal($result)
    {
        $sql = sprintf("select result from coc_proxy_results where `option` = '%s' and tag = '%s' order by create_time desc limit 1", $this->params->option, $this->params->tag);
        $sqlRes = MyDB::db()->getOne($sql);
        if (!empty($sqlRes)) {
            $result = json_decode($sqlRes, true);
        }

        return $result;
    }
}