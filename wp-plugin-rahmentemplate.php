<?php

/**
 * Plugin Name:       Wordpress Rahmen Template
 * Description:       Plugin to replace Content from a Template
 * Version:           0.1
 * Requires at least: 6.2
 * Author:            Charalambos Schmechel <charalambos.schmechel@vonaffenfels.de>
 * Author URI:        https://www.vonaffenfels.de
 */

use VAF\WP\Framework\Plugin;

if (!defined('ABSPATH')) {
    die('');
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

try {
    Plugin::registerPlugin(__FILE__, defined('WP_DEBUG') && WP_DEBUG);
} catch (Exception $e) {
}