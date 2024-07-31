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
        $templateUrl =  filter_var(get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true), FILTER_VALIDATE_URL)
                        ? get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true)
                        : get_option('rahmentemplate_settings_input_default_field');

        if (empty($templateUrl)) {
            echo 'Keine Template-URL gefunden. Standard Template im Plugin oder Template im Beitrag hinterlegen.';
            exit;
        }

        $templates = get_option('rahmentemplate_settings_input_templates_field', []);
        $templateDetails = [];

        foreach ($templates as $template) {
            if ($template['url'] === $templateUrl) {
                $templateDetails = $template;
                break;
            }
        }

        $client = new Client([
            'auth' => ['test', 'test'],
        ]);

        try {
            $transient_key = $templateDetails['title'] . '_transient';

            $cachedTemplate = get_transient($transient_key);

            if ($cachedTemplate) {
                $template = $cachedTemplate;
            } else {
                $templateRequest = $client->request('GET', $templateUrl);
                $template['body'] = $templateRequest->getBody()->getContents();
                $template['createdAt'] = current_time('timestamp');

                set_transient($transient_key, $template, 60 * 60 * 48);
            }

            $dom = new DOMDocument();
            @$dom->loadHTML($template['body']);

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