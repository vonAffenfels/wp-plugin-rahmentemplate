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

        $clearCache = new RestRoutes\ClearCache();
        $clearCache->register_routes();

        add_filter( 'the_content', [$this, 'handleTemplateAfterContentLoaded'] );
        //add_action( 'wp_footer', function() { });
    }

    /**
     * @throws GuzzleException
     */
    public function handleTemplateAfterContentLoaded($content) {

        if(!is_admin() && !wp_is_json_request()
            && ( is_single() || is_page() ) && in_the_loop() && is_main_query()
        ) {
            $templateHandler = new TemplateHandler();
            return $templateHandler->initTemplateHandler($content);
        }

        return $content;
    }
}
