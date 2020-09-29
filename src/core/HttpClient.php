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

    public $host = 'open.feishu.cn';
    public $protocol = 'https';

    // 不会被截获的url请求，一般为不需要认证的接口及获取token的接口
    public $excludeActions = [];
    //主要的token, 一般为tenant token
    public $tokenCacheKey = 'token_cache_key:';

    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::className());
     }
    
    protected function beforeCurl($params)
    {
        $action = trim($this->getAction());
        if (!in_array($action, $this->excludeActions)) {
            $ch = clone $this;
            $token = $ch->getTokenFromCache();
            $this->setBaAuthor($token);
        }
        return parent::beforeCurl($params);
    }

    protected function afterCurl($output)
    {
        $data = json_decode($output, true);
        if (empty($output) || empty($data)) {
            return [
                'code' => 1,
                'msg' => \yii::t('feishu', 'network error!'),
            ];
        }
        return parent::afterCurl($data);
    }


    public function setBaAuthor($token)
    {
        if($this->isDebug()) {
            echo "Authorization: Bearer ".$token."\n";
        }
        $this->setHeader('Authorization', 'Bearer '.$token);
    }

    public function getTokenFromCache()
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
        return $token;
    }

    // 需要上层覆盖
    public function getToken(&$expire = 0)
    {
        return '';
    }
}
