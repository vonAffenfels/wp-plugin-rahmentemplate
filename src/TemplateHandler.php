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
        $templateID =   get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true);
        $defaultTemplateID = get_option('rahmentemplate_settings_input_default_field');

        $templates = get_option('rahmentemplate_settings_input_templates_field', []);
        
        $templateDetails = [];
        $defaultTemplateDetails = [];

        foreach ($templates as $template) {
            switch ($template['ID']) {
                case $templateID:
                    $templateDetails = $template;
                    break;
                case $defaultTemplateID:
                    $defaultTemplateDetails = $template;
                    break;
            }
        }

        if (empty($templateDetails['url']) && empty($defaultTemplateDetails['url'])) {
            echo 'Keine Template-URL gefunden. Standard Template im Plugin oder Template im Beitrag hinterlegen.';
            exit;
        } elseif (empty($templateDetails['url']) && !empty($defaultTemplateDetails['url'])) {
            $templateDetails = $defaultTemplateDetails;
        }

        $client = new Client([
            'auth' => ['test', 'test'],
        ]);

        try {
            $transient_key = $templateDetails['ID'] . '_transient';

            $cachedTemplate = get_transient($transient_key);

            if ($cachedTemplate) {
                $template = $cachedTemplate;
            } else {
                $templateRequest = $client->request('GET', $templateDetails['url']);
                $template['body'] = $templateRequest->getBody()->getContents();
                $template['createdAt'] = current_time('timestamp');

                set_transient($transient_key, $template, 60 * 60 * 48);
            }

            $dom = new DOMDocument();
            @$dom->loadHTML($template['body']);

            $parsedUrl = parse_url($templateDetails['url']);
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