<?php

/**
 * Plugin Name:  SATORI Forms
 * Plugin URI:   https://satori.com.au/
 * Description:  Core form builder plugin by SATORI. Start building forms and managing submissions.
 * Version:      0.1.0
 * Author:       SATORI
 * Author URI:   https://satori.com.au/
 * Text Domain:  satori-forms
 * Domain Path:  /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * This file is part of the SATORI Forms plugin.
 *
 * SATORI Forms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * SATORI Forms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SATORI Forms. If not, see <https://www.gnu.org/licenses/>.
 */

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Constants
 * -------------------------------------------------*/
if (!defined('SATORI_FORMS_VERSION')) {
    define('SATORI_FORMS_VERSION', '0.1.0');
}
if (!defined('SATORI_FORMS_PLUGIN_DIR')) {
    define('SATORI_FORMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('SATORI_FORMS_PLUGIN_URL')) {
    define('SATORI_FORMS_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('SATORI_FORMS_DEBUG')) {
    define('SATORI_FORMS_DEBUG', false);
}

/* -------------------------------------------------
 * Load textdomain
 * -------------------------------------------------*/
add_action('init', function () {
    load_plugin_textdomain(
        'satori-forms',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
});

/* -------------------------------------------------
 * PSR-4 Autoloader for Satori\Forms\
 * -------------------------------------------------*/
spl_autoload_register(function ($class) {
    $prefix   = 'Satori\\Forms\\';
    $base_dir = SATORI_FORMS_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // not our namespace
    }

    $relative_class = substr($class, $len);
    $file           = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

/* -------------------------------------------------
 * Procedural helpers
 * -------------------------------------------------*/
require_once SATORI_FORMS_PLUGIN_DIR . 'includes/Debug/Debug.php';
require_once SATORI_FORMS_PLUGIN_DIR . 'includes/Admin/AdminNotices.php';
require_once SATORI_FORMS_PLUGIN_DIR . 'includes/Admin/ToolsPage.php';

/* -------------------------------------------------
 * Activation / Deactivation
 * -------------------------------------------------*/
register_activation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

/* -------------------------------------------------
 * Plugin Initialization (boot once)
 * -------------------------------------------------*/
function satori_forms_init(): void
{
    static $booted = false;
    if ($booted) {
        return;
    }
    $booted = true;

    // CPTs
    if (class_exists('\\Satori\\Forms\\PostTypes\\RegisterFormsCPT')) {
        new \Satori\Forms\PostTypes\RegisterFormsCPT();
    }
    if (class_exists('\\Satori\\Forms\\PostTypes\\RegisterEntriesCPT')) {
        new \Satori\Forms\PostTypes\RegisterEntriesCPT();
    }

    // Admin UI
    if (is_admin()) {
        if (class_exists('\\Satori\\Forms\\Admin\\Admin')) {
            new \Satori\Forms\Admin\Admin();
        }
        if (class_exists('\\Satori\\Forms\\Admin\\EntriesUI')) {
            new \Satori\Forms\Admin\EntriesUI();
        }
        if (class_exists('\\Satori\\Forms\\Admin\\FormBuilder')) {
            new \Satori\Forms\Admin\FormBuilder();
        }
        if (class_exists('\\Satori\\Forms\\Admin\\FormSettings')) {
            new \Satori\Forms\Admin\FormSettings();
        }
        if (class_exists('\\Satori\\Forms\\Admin\\EntriesExport')) {
            new \Satori\Forms\Admin\EntriesExport();
        }
    }

    // Front-end
    if (class_exists('\\Satori\\Forms\\Render\\Renderer')) {
        new \Satori\Forms\Render\Renderer();
    }
    if (class_exists('\\Satori\\Forms\\Shortcodes\\Shortcodes')) {
        new \Satori\Forms\Shortcodes\Shortcodes();
    }
    if (class_exists('\\Satori\\Forms\\Handlers\\SubmissionHandler')) {
        new \Satori\Forms\Handlers\SubmissionHandler();
    }
}
add_action('plugins_loaded', 'satori_forms_init');
