<?php

namespace Rahmentemplate;

use GuzzleHttp\Exception\GuzzleException;
use Rahmentemplate\Template\TemplateParts;

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

        add_filter( 'wp_head', [$this, 'handleTemplateInHeader'] );
        add_filter( 'the_content', [$this, 'handelTemplateInContent'] );
        add_filter( 'wp_footer', [$this, 'handleTemplateInFooter']);
    }

    /**
     * @throws GuzzleException
     */
    public function handleTemplateInHeader($content) {
        if(!is_admin() && !wp_is_json_request()) {
            $templateHandler = new TemplateHandler();
            wp_enqueue_style('rahmen-template', plugin_dir_url(__FILE__) . 'css/reset.css');
            echo $templateHandler->initTemplateHandler($content, 'header');
        }

        return $content;
    }

    /**
     * @throws GuzzleException
     */
    public function handelTemplateInContent($content) {

        if(!is_admin() && !wp_is_json_request()
            && ( is_single() || is_page() ) && in_the_loop() && is_main_query()
        ) {
            $templateHandler = new TemplateHandler();
            return $templateHandler->initTemplateHandler($content);
        }

        return $content;
    }

    /**
     * @throws GuzzleException
     */
    public function handleTemplateInFooter($content) {
        if(!is_admin() && !wp_is_json_request()) {
            $templateHandler = new TemplateHandler();
            echo $templateHandler->initTemplateHandler($content, 'footer');
        }

        return $content;
    }
}
