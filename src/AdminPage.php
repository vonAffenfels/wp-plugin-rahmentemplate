<?php

namespace WP\Plugin\Rahmentemplate;

use VAF\WP\Framework\AdminPages\Attributes\IsTabbedPage;
use VAF\WP\Framework\AdminPages\Attributes\PageTab;
use VAF\WP\Framework\AdminPages\TabbedPage;
use WP\Plugin\Rahmentemplate\AdminPages\SettingsPage;

#[IsTabbedPage(pageTitle: 'Rahmentemplate')]
class AdminPage extends TabbedPage
{
    #[PageTab(slug: Menu::SLUG_SETTINGS, title: 'Settings')]
    public function handleSettings(SettingsPage $page): void
    {
        $page->handle();
    }
}