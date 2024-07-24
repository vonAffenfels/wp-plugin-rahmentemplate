<?php

namespace WP\Plugin\Rahmentemplate;

use VAF\WP\Framework\Menu\Attribute\AsMenuContainer;
use VAF\WP\Framework\Menu\Attribute\MenuItem;
use VAF\WP\Framework\Utils\Capabilities;
use VAF\WP\Framework\Utils\Dashicons;

#[AsMenuContainer]
class Menu
{
    public const SLUG_SETTINGS = 'wp-plugin-rahmentemplate-settings';

    #[MenuItem(
        menuTitle: 'Rahmentemplate',
        capability: Capabilities::MANAGE_OPTIONS,
        slug: self::SLUG_SETTINGS,
        icon: Dashicons::MISC_SEARCH,
        subMenuTitle: 'Settings'
    )]
    public function settingsMenu(AdminPage $page): void
    {
        $page->handle();
    }
}