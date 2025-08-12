<?php

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

/**
 * Plugin Name: SATORI Forms
 * Plugin URI:  https://satori.com.au/
 * Description: Core form builder plugin by SATORI. Start building forms and managing submissions.
 * Version:     0.1.0
 * Author:      SATORI
 * Author URI:  https://satori.com.au/
 * Text Domain: satori-forms
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Define constants
 * -------------------------------------------------*/
define('SATORI_FORMS_VERSION', '0.1.0');
define('SATORI_FORMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SATORI_FORMS_PLUGIN_URL', plugin_dir_url(__FILE__));

/* -------------------------------------------------
 * PSR-4 Autoloader for Satori\Forms namespace
 * -------------------------------------------------*/
spl_autoload_register(function ($class) {

    $prefix = 'Satori\\Forms\\';
    $base_dir = SATORI_FORMS_PLUGIN_DIR . 'Includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Not our namespace, bail out.
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

/* -------------------------------------------------
 * Plugin Initialization
 * -------------------------------------------------*/
function satori_forms_init()
{
    // Instantiate CPT registration
    $cpt = new Satori\Forms\PostTypes\RegisterFormsCPT();

    // Instantiate admin class if in admin area
    if (is_admin()) {
        $admin = new Satori\Forms\Admin\Admin();
    }
}
add_action('plugins_loaded', 'satori_forms_init');
