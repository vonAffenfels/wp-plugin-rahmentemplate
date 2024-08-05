<?php

namespace Rahmentemplate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Rahmentemplate\Template\TemplateParts;

class TemplateHandler
{
    /**
     * @throws GuzzleException
     */
    function initTemplateHandler($content, $templatePart = 'content') : string
    {
        $template['ID'] =  $this->getTemplateIDCurrentPage();

        $defaultTemplateID = $this->getDefaultTemplateID();
        $defaultTemplate = [];

        $templates = $this->getAllTemplates();
        foreach ($templates as $temp) {
            $template = $this->filterTemplates($temp, $template, $defaultTemplateID);
        }

        $client = new Client([
            'auth' => ['test', 'test']
        ]);

        $template = $this->handleTemplateExceptions($template, $defaultTemplate);

        try {
            $template = $this->checkForCache($client, $template);
            $templateParts = new TemplateParts($template);

            if ($templatePart === 'header' && $templateParts->hasBeforeContent()) {
                return $templateParts->beforeContent();
            } elseif ($templatePart === 'footer' && $templateParts->hasAfterContent()) {
                return $templateParts->afterContent();
            } else {
                return $templateParts->content($content);
            }
        } catch (\Exception $e) {
            echo 'An error occurred: ' . $e->getMessage();
        }

        return $content;
    }

    private function checkForCache($client, $template)
    {
        $transient_key = ($template['ID'] ?? '') . '_transient';
        $cachedTemplate = get_transient($transient_key);

        if (!$cachedTemplate) {
            return $this->setCache($client, $template, $transient_key);
        } else {
            return $cachedTemplate;
        }
    }

    private function setCache($client, $template, $transient_key)
    {
        $templateRequest = $client->request('GET', $template['url']);
        $template['body'] = $templateRequest->getBody()->getContents();
        $template['createdAt'] = current_time('timestamp');

        set_transient($transient_key, $template, 60 * 60 * 48);
        return $template;
    }

    private function handleTemplateExceptions(mixed $template, mixed $defaultTemplate)
    {
        if (empty($template['url']) && empty($defaultTemplate['url'])) {
            echo 'Keine Template-URL gefunden. Standard Template im Plugin oder Template im Beitrag hinterlegen.';
            exit;
        } elseif (empty($template['url']) && !empty($defaultTemplate['url'])) {
            return $defaultTemplate;
        }
        return $template;
    }

    function getTemplateIDCurrentPage()
    {
        return get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true) ?? '';
    }

    function getDefaultTemplateID()
    {
        return get_option('rahmentemplate_settings_input_default_field');
    }

    function getAllTemplates()
    {
        return get_option('rahmentemplate_settings_input_templates_field', []);
    }

    function filterTemplates($temp, $template, $defaultTemplateID)
    {
        if ($temp['ID'] === $template['ID']) {
            return $temp;
        } elseif ($temp['ID'] === $defaultTemplateID && empty($template['ID'])) {
            return $temp;
        }
        return $template;
    }

    function getTemplateByID(mixed $ID)
    {
        $options = get_option('rahmentemplate_settings_input_templates_field', []);

        foreach ($options as $option) {
            if (isset($option['ID']) && $option['ID'] == $ID) {
                return $option;
            }
        }

        return [];
    }

    /**
     * @throws GuzzleException
     */
    function getTemplateBody ($template)
    {
        $client = new Client([
            'auth' => ['test', 'test']
        ]);

        $template = $this->checkForCache($client, $template);
        return $template['body'];
    }
}
