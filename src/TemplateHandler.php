<?php

namespace Rahmentemplate;

use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Rahmentemplate\Template\TemplateParts;

class TemplateHandler
{
    /**
     * @throws GuzzleException
     */
    function initTemplateHandler($content) : string
    {
        $templateID =  $this->getTemplateIDCurrentPage();
        $defaultTemplateID = $this->getDefaultTemplateID();
        $templates = $this->getAllTemplates();
        $templateDetails = [];
        $defaultTemplateDetails = [];

        $client = new Client([
            'auth' => ['test', 'test'],
        ]);

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

        $templateDetails = $this->handleTemplateExceptions($templateDetails, $defaultTemplateDetails);

        try {
            $template = $this->checkForCache($client, $templateDetails);
            $template = new TemplateParts($template, $templateDetails, $content);

            return $template->beforeContent() . $template->content() . $template->afterContent();
        } catch (\Exception $e) {
            echo 'An error occurred: ' . $e->getMessage();
        }

        return $content;
    }

    private function setCache($client, $templateDetails, $transient_key)
    {
        $templateRequest = $client->request('GET', $templateDetails['url']);
        $template['body'] = $templateRequest->getBody()->getContents();
        $template['createdAt'] = current_time('timestamp');

        set_transient($transient_key, $template, 60 * 60 * 48);
        return $template;
    }

    private function handleTemplateExceptions(mixed $templateDetails, mixed $defaultTemplateDetails)
    {
        if (empty($templateDetails['url']) && empty($defaultTemplateDetails['url'])) {
            echo 'Keine Template-URL gefunden. Standard Template im Plugin oder Template im Beitrag hinterlegen.';
            exit;
        } elseif (empty($templateDetails['url']) && !empty($defaultTemplateDetails['url'])) {
            return $defaultTemplateDetails;
        }
        return $templateDetails;
    }

    private function checkForCache($client, $templateDetails)
    {
        $transient_key = ($templateDetails['ID'] ?? '') . '_transient';
        $cachedTemplate = get_transient($transient_key);

        if (!$cachedTemplate) {
            return $this->setCache($client, $templateDetails, $transient_key);
        } else {
            return $cachedTemplate;
        }
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


}
