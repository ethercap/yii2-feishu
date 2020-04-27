<?php

namespace lspbupt\feishu\core;

class App extends HttpClient
{
    public $tokenUrl;
    public $appTokenUrl;

    public $apptoken_cache_key = 'apptoken_cache_key:';

    public function init()
    {
        $this->excludeActions[] = $this->tokenUrl;
        $this->excludeActions[] = $this->appTokenUrl;
        parent::init();
    }

    //需要上层覆盖
    public function getAppToken(&$expire = 0)
    {
        return '';
    }

    public function getAppTokenfromCache()
    {
        $key = $this->apptoken_cache_key.$this->appId;
        $token = $this->cache->get($key, '');
        if ($token) {
            return $token;
        }
        $token = $this->getAppToken($expire);
        if ($token) {
            empty($expire) && $expire = 3600;
            $this->cache->set($key, $token, $expire - 10);
        }
        return '';
    }
}
