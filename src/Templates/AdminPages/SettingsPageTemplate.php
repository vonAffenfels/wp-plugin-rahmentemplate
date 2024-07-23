<?php

namespace WP\Plugin\Rahmentemplate\Templates\AdminPages;



use VAF\WP\Framework\Template\Template;
use WP\Plugin\Rahmentemplate\Settings\Params;

#[IsTemplate(templateFile: '@wp-plugin-rahmentemplate/adminpages/settings')]
#[UseScript(src: 'js/settings.min.js', deps: ['jquery'])]
class SettingsPageTemplate extends Template
{
    private Params $param;

    protected function getContextData(): array
    {
        return [

        ];
    }

    public function setEndpoint(Params $param): self
    {
        $this->param = $param;
        return $this;
    }

    protected function getJavascriptData(): false|array
    {
        return [

        ];
    }
}