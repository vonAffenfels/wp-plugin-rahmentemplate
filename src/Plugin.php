<?php

namespace Rahmentemplate;

class Plugin
{
    public function init()
    {
        $settingsPage = new SettingsPage();
        $settingsPage->initSettingsPage();

        $metaBox = new MetaBox();
        $metaBox->registerField();

        add_filter( 'the_content', [$this, 'mytheme_content_filter'] );
    }

    public function mytheme_content_filter( $content ) {

        if(is_front_page()) {
            $templateHandler = new TemplateHandler();
            $templateHandler->initTemplateHandler($content);
        }

        return $content;
    }
}
