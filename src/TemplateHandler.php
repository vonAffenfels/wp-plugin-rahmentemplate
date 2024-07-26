<?php

namespace Rahmentemplate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TemplateHandler
{
    /**
     * @throws GuzzleException
     */
    function initTemplateHandler($content) : void
    {
        $client = new Client([
            'auth' => ['test', 'test'],
        ]);

        $templateUrl = get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true);

        if (empty($templateUrl) || !filter_var($templateUrl, FILTER_VALIDATE_URL)) {
            echo 'Invalid template URL.';
            exit;
        }

        try {
            $template = $client->request('GET', $templateUrl);
            $templateBody = $template->getBody()->getContents();

            $contentReplacedTemplate = str_replace('CONTENT', $content, $templateBody);

            echo $contentReplacedTemplate;
        } catch (\Exception $e) {
            echo 'An error occurred: ' . $e->getMessage();
        }

        exit;
    }

}