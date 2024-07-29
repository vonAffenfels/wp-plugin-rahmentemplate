<?php

namespace Rahmentemplate;

use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TemplateHandler
{
    /**
     * @throws GuzzleException
     */
    function initTemplateHandler($content) : void
    {
        $templateUrl = get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true);
        $client = new Client([
            'auth' => ['test', 'test'],
            'headers' => [
                'Origin' => $templateUrl,
                'Access-Control-Allow-Origin' => $templateUrl,
                'Access-Control-Allow-Methods' => 'GET',
                'Access-Control-Allow-Headers' => 'Content-Type',
            ]
        ]);

        if (empty($templateUrl) || !filter_var($templateUrl, FILTER_VALIDATE_URL)) {
            echo 'Invalid template URL.';
            exit;
        } 

        try {
            $template = $client->request('GET', $templateUrl);
            $templateBody = $template->getBody()->getContents();
            
            $dom = new DOMDocument();
            @$dom->loadHTML($templateBody);

            $parsedUrl = parse_url($templateUrl);
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            $baseUrl .=  '/';
          
            $tags = [
                'img' => 'src',
                'link' => 'href',
                'script' => 'src'
            ];

            foreach ($tags as $tag => $attribute) {
                $elements = $dom->getElementsByTagName($tag);
                foreach ($elements as $element) {
                    $url = $element->getAttribute($attribute);
                    if ($url && !parse_url($url, PHP_URL_SCHEME)) {
                        $element->setAttribute($attribute, $baseUrl . ltrim($url, '/'));
                    }
                }
            }

            $updatedTemplate = mb_convert_encoding($dom->saveHTML() , 'UTF-8', 'HTML-ENTITIES');

            echo $updatedTemplate;
        } catch (\Exception $e) {
            echo 'An error occurred: ' . $e->getMessage();
        }

        exit;
    }

}