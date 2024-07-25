<?php

namespace Rahmentemplate;

class TemplateHandler
{
    function initTemplateHandler($content)
    {
        $template = get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true);


        return $content;
    }

}