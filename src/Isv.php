<?php

namespace lspbupt\feishu;

use lspbupt\feishu\core\HttpClient;

class Isv extends HttpClient
{
    public $appTokenUrl = '/auth/v3/app_access_token/internal/';
    public $tenantTokenUrl = '';

    public function init()
    {
        parent::init();
        $this->excludeActions[] = $this->appTokenUrl;
        $this->excludeActions[] = $this->tenantTokenUrl;
    }

    public function getToken(&$expire = 0)
    {
        //先获取app_token
        $app_token = $this->getAppTokenFromCache();
        //再获取tenantTokenUrl
        $tenant_key = $this->getTenantKey();
        if (empty($tenant_key)) {
            return '';
        }
        $data = $this->setPostJson()
            ->httpExec($this->tenantTokenUrl, [
                'app_access_token' => $app_token,
                'tenant_key' => $tenant_key,
            ]);
    }
}
