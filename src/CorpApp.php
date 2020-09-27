<?php

namespace lspbupt\feishu;

use lspbupt\feishu\core\App;

//企业内建应用使用该实例

class CorpApp extends App
{
    public $tokenUrl = '/open-apis/auth/v3/tenant_access_token/internal/';
    public $appTokenUrl = '/open-apis/auth/v3/app_access_token/internal/';

    public function getToken(&$expire = 3600)
    {
        $data = $this->setPostJson()
            ->httpExec($this->tokenUrl, [
                'app_id' => $this->appId,
                'app_secret' => $this->appSecret,
            ]);
        if (isset($data['code']) && $data['code'] == 0) {
            $expire = $data['expire'];
            return $data['tenant_access_token'];
        }
        return '';
    }

    public function getAppToken(&$expire = 3600)
    {
        $data = $this->setPostJson()
            ->httpExec($this->appTokenUrl, [
                'app_id' => $this->appId,
                'app_secret' => $this->appSecret,
            ]);
        if (isset($data['code']) && $data['code'] == 0) {
            $expire = $data['expire'];
            return $data['app_access_token'];
        }
        return '';
    }
}
