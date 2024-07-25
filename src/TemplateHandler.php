<?php

namespace Rahmentemplate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TemplateHandler
{
    /**
     * @throws GuzzleException
     */
    function initTemplateHandler($content)
    {
        $client = new Client([
            'auth' => ['test', 'test'],
        ]);

        $template = get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true);

        $result = $client->get($template, [
            'headers' => [
                'Accept' => 'gzip',
            ],
        ]);
        echo $result->getBody();

    

        return $content;
    }

}