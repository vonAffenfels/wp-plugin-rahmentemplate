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
        $templates = get_option('rahmentemplate_settings_input_templates_field', []);

        foreach ($templates as $template) {
            if ($template['url'] === $templateUrl) {
                $templateDetails = $template;
                break;
            }
        }

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
            $cache_key = 'template_body';
            $cache_group = 'template_body';

            $cachedTemplate = wp_cache_get($cache_key, $cache_group);

            if ($cachedTemplate) {
                $templateBody = $cachedTemplate;
            } else {
                $template = $client->request('GET', $templateUrl);
                $templateBody = $template->getBody()->getContents();

                wp_cache_add($cache_key, $templateBody, $cache_group, 60 * 60 * 24);
            }

            $dom = new DOMDocument();
            @$dom->loadHTML($templateBody);

            $parsedUrl = parse_url($templateUrl);
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            $baseUrl .=  '/';
          
            $UrlTags = [
                'img' => 'src',
                'link' => 'href',
                'script' => 'src'
            ];

            foreach ($UrlTags as $tag => $attribute) {
                $elements = $dom->getElementsByTagName($tag);
                foreach ($elements as $element) {
                    $url = $element->getAttribute($attribute);
                    if ($url && !parse_url($url, PHP_URL_SCHEME)) {
                        $element->setAttribute($attribute, $baseUrl . ltrim($url, '/'));
                    }
                }
            }

            $htmlTags = ['<p>', '<div>', '<span>'];
            $updatedTemplate = mb_convert_encoding($dom->saveHTML() , 'UTF-8', 'HTML-ENTITIES');
            
            foreach ($htmlTags as $tag) {
                $closeTag = str_replace('<', '</', $tag);
                $replace = $tag . (!empty($templateDetails['replace']) ? $templateDetails['replace'] : 'CONTENT') . $closeTag;

                $updatedTemplate  = str_replace($replace, $content, $updatedTemplate);
            }

            $contentReplacedTemplate = $updatedTemplate;

            echo $contentReplacedTemplate;
        } catch (\Exception $e) {
            echo 'An error occurred: ' . $e->getMessage();
        }

        exit;
    }

}