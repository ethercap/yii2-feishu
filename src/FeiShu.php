<?php

namespace lspbupt\feishu;

class FeiShu extends BaseFeiShu
{
    public $appId = '';
    public $appSecret = '';

    public $tokenUrl = '/auth/v3/app_access_token/internal/';

    public function getToken()
    {
        $data = $this->setPostJson()
            ->httpExec($this->tokenUrl, [
                'app_id' => $this->appId,
                'app_secret' => $this->appSecret,
            ]);
    }

    public function getTenantToken()
    {
        $data = $this->setPostJson()
            ->httpExec($this->tenantTokenUrl, [
                'app_id' => $this->appId,
                'app_secret' => $this->appSecret,
            ]);
    }
}
