<?php

namespace lspbupt\feishu\core;

use lspbupt\curl\CurlHttp;
use yii\caching\Cache;
use yii\di\Instance;

class HttpClient extends CurlHttp
{
    public $appId = '';
    public $appSecret = '';
    public $cache = 'cache';

    // 不会被截获的url请求，一般为不需要认证的接口及获取token的接口
    public $excludeActions = [];
    //主要的token, 一般为tenant token
    public $tokenCacheKey = 'token_cache_key:';

    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::className());
        $this->beforeRequest = function ($params, $curlhttp) {
            $action = trim($curlhttp->getAction());
            if (!in_array($action, $curlhttp->excludeActions)) {
                $ch = clone $curlhttp;
                $token = $ch->getTokenFromCache();
                $curlhttp->setBaAuthor($token);
            }
            return $params;
        };
        $this->afterRequest = function ($output, $curlhttp) {
            $data = json_decode($output, true);
            if (empty($output) || empty($data)) {
                return [
                    'code' => 1,
                    'msg' => yii::t('feishu', 'network error!'),
                ];
            }
            return $data;
        };
    }

    public function setBaAuthor($token)
    {
        $this->setHeader('Authorization', 'Bearer '.$token);
    }

    public function getTokenfromCache()
    {
        $key = $this->tokenCacheKey.$this->appId;
        $token = $this->cache->get($key, '');
        if ($token) {
            return $token;
        }
        $token = $this->getToken($expire);
        if ($token) {
            empty($expire) && $expire = 3600;
            $this->cache->set($key, $token, $expire - 10);
        }
        return '';
    }

    // 需要上层覆盖
    public function getToken(&$expire = 0)
    {
        return '';
    }
}
