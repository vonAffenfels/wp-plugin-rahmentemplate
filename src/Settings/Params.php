<?php

namespace WP\Plugin\Rahmentemplate\Settings;

use VAF\WP\Framework\Setting\EnvAwareSetting;

#[AsSettingContainer('connection', [
    self::FIELD_ENDPOINT => '',
])]
class Params extends EnvAwareSetting
{
    public const FIELD_ENDPOINT = 'endpoint';

    public function getEndpoint(): string
    {
        return $this->get(self::FIELD_ENDPOINT);
    }

    public function setEndpoint(string $value): self
    {
        $this->set($value, self::FIELD_ENDPOINT, false);
        return $this;
    }

    protected function parseEnv(): array
    {
        $envData = [];

        $endpoint = getenv('SEARCH_API_ENDPOINT') ?: '';

        return $envData;
    }
}