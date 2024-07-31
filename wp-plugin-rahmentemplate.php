<?php

/**
 * Plugin Name:       Wordpress Rahmentemplate
 * Description:       Plugin to replace Content from a Template
 * Version:           0.1
 * Requires at least: 6.2
 * Author:            Charalambos Schmechel <charalambos.schmechel@vonaffenfels.de>
 * Author URI:        https://www.vonaffenfels.de
 */

use Rahmentemplate\Plugin;

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

$plugin = new Plugin();
$plugin->init();