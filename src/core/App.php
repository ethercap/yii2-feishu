<?php

namespace lspbupt\feishu\core;
use Yii;
use yii\web\UnauthorizedHttpException;

class App extends HttpClient
{
    //当需要用户认证时，使用这个
    private $openId;

    public $tokenUrl;
    public $appTokenUrl;
    public $excludeActions = [
        '/open-apis/authen/v1/refresh_access_token',
        '/open-apis/authen/v1/access_token'
    ];

    public $apptoken_cache_key = 'apptoken_cache_key:';
    public $usertoken_cache_key = 'usertoken_cache_key:';

    public function init()
    {
        $this->excludeActions[] = $this->tokenUrl;
        $this->excludeActions[] = $this->appTokenUrl;
        parent::init();
    }
    
    protected function afterCurl($output)
    {
        $this->openId = "";
        return parent::afterCurl($output);
    }

    public function setUserAuthor($openId)
    {
        $this->openId = $openId;
        return $this;
    }


    public function getTokenFromCache()
    {
        if($this->openId) {
            return $this->getUserTokenFromCache(); 
        }
        return parent::getTokenFromCache(); 
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
            return $token;
        }
        return '';
    }

    public function getUserToken($code)
    {
        $ret = $this->setPostJson()
            ->httpExec("/open-apis/authen/v1/access_token", [
                'app_access_token' => $this->getAppTokenfromCache(),
                'code' => $code,
                'grant_type' => 'authorization_code',
            ]);
        if($ret['code'] === 0) {
            $this->saveUserToken($ret['data']);
        }
        return $ret;
    }

    public function refreshUserToken($refreshToken)
    {
        $ret = $this->setPostJson()
            ->httpExec("/open-apis/authen/v1/refresh_access_token", [
                'app_access_token' => $this->getAppTokenfromCache(),
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);
        if($ret['code'] === 0) {
            $this->saveUserToken($ret['data']);
        }
        return $ret;
    }

    public function getUserTokenFromCache()
    {
        $key = md5($this->usertoken_cache_key.$this->appId.":".$this->openId);
        $data = $this->cache->get($key, []); 
        if(empty($data)) {
            Yii::$app->user->logout();
            throw new UnauthorizedHttpException(Yii::t('feishu', 'user need relogin'));
        }
        $ts = time();
        $before = $data['ts'];
        //token is not expire
        if($before + $data['expires_in'] > $ts) {
            return $data['access_token'];
        }
        if($before + $data['expires_in'] <= $ts && $before + $data['refresh_expires_in'] > $ts) {
            $data = $this->refreshUserToken($data['refresh_token']);
            return $data['data']['access_token'] ?? "";
        }
        if($before + $data['refresh_expires_in'] <= $ts) {
            Yii::$app->user->logout();
            throw new UnauthorizedHttpException(Yii::t('feishu', 'user need relogin'));
        }
    }

    public function saveUserToken($data)
    {
        $openId = $data['open_id'] ?? "";
        $key = md5($this->usertoken_cache_key.$this->appId.":".$openId);
        $data['ts'] = time();
        $this->cache->set($key, $data, 0);    
    }
}
