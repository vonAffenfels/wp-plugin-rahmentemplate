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

            $dom = new \DOMDocument();
            @$dom->loadHTML($templateBody);

            $parsedUrl = parse_url($templateUrl);
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            $baseUrl .= rtrim(dirname($parsedUrl['path']), '/') . '/';

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

            $updatedTemplate = $dom->saveHTML();

            $contentReplacedTemplate = str_replace('CONTENT', $content, $updatedTemplate);

            echo $contentReplacedTemplate;
        } catch (\Exception $e) {
            echo 'An error occurred: ' . $e->getMessage();
        }

        exit;
    }

}