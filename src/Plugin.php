<?php

namespace Rahmentemplate;

use GuzzleHttp\Exception\GuzzleException;

class Plugin
{
    public function init(): void
    {
        $settingsPage = new SettingsPage();
        $settingsPage->initSettingsPage();

        $metaBox = new MetaBox();
        $metaBox->registerField();

        add_filter( 'the_content', [$this, 'handleTemplateAfterContentLoaded'] );
    }

    /**
     * @throws GuzzleException
     */
    public function handleTemplateAfterContentLoaded($content) {

        if(!is_admin() && !wp_is_json_request()) {
            $templateHandler = new TemplateHandler();
            $templateHandler->initTemplateHandler($content);
        }
    }
}
