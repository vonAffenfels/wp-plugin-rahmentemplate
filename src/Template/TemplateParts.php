<?php

namespace Rahmentemplate\Template;

use DOMDocument;

class TemplateParts
{
    private array $template = [];
    private string $content = '';
    private array $templateDetails = [];
    private ?DOMDocument $dom = null;

    private array $htmlTags = ['<p>', '<div>', '<span>'];
    
    public function __construct($template, $templateDetails, $content)
    {
        $this->dom = new DOMDocument();
        $this->template = $template;
        $this->templateDetails = $templateDetails;
        $this->content = $content;

        @$this->dom->loadHTML($this->template['body']);
        $this->replaceURLs();
        $this->template['body'] = mb_convert_encoding($this->dom->saveHTML() , 'UTF-8', 'HTML-ENTITIES');
    }
    
    public function beforeContent(): string
    {
        return substr($this->template['body'], 0, strpos($this->template['body'], $this->templateDetails['replace'] ?: '<p>CONTENT</p>'));
    }
    
    public function content(): string
    {
        return $this->replaceContent();
    }
    
    public function afterContent(): string
    {
        $countCharsUntilContent = strpos($this->template['body'], $this->templateDetails['replace'] ?: '<p>CONTENT</p>') + strlen($this->templateDetails['replace'] ?: '<p>CONTENT</p>');
        return substr($this->template['body'], $countCharsUntilContent);
    }

    private function replaceContent(): string
    {
        $replaced = '';
        foreach ($this->htmlTags as $tag) {
            $closeTag = str_replace('<', '</', $tag);
            $replace = $tag . (!empty($this->templateDetails['replace']) ? $this->templateDetails['replace'] : 'CONTENT') . $closeTag;

            $replaced  = substr_replace($replace, $this->content, 0);
        }

        return $replaced;
    }

    public function replaceURLs(): void
    {
        $parsedUrl = parse_url($this->templateDetails['url']);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        $baseUrl .=  '/';

        $UrlTags = [
            'img' => 'src',
            'link' => 'href',
            'script' => 'src'
        ];

        foreach ($UrlTags as $tag => $attribute) {
            $elements = $this->dom->getElementsByTagName($tag);
            foreach ($elements as $element) {
                $url = $element->getAttribute($attribute);
                if ($url && !parse_url($url, PHP_URL_SCHEME)) {
                    $element->setAttribute($attribute, $baseUrl . ltrim($url, '/'));
                }
            }
        }
    }
}