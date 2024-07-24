<?php

namespace Rahmentemplate;

class Plugin
{
    public function init()
    {
        $settingsPage = new SettingsPage();
        $settingsPage->initSettingsPage();
    }

}
