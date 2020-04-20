<?php

namespace lspbupt\feishu;

use lspbupt\feishu\core\App;

//企业内建应用使用该实例

class CorpApp extends App
{
    public function getToken(&$expire = 3600)
    {
    }

    public function getAppToken(&$expire = 3600)
    {
    }
}
