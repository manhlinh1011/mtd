<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WebhookConfig extends BaseConfig
{
    public string $domain = 'https://mqzcil.datadex.vn';

    public function getWebhookUrl(string $endpoint): string
    {
        return rtrim($this->domain, '/') . '/' . ltrim($endpoint, '/');
    }
}
