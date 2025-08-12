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

namespace Satori\Forms\Admin;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Admin Menu and Asset Enqueue for SATORI Forms
 * -------------------------------------------------*/
class Admin
{

    /* -------------------------------------------------
     * Class Constructor
     * -------------------------------------------------*/
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /* -------------------------------------------------
     * Register Admin Menu and Submenu
     * -------------------------------------------------*/
    public function register_admin_menu()
    {
        add_menu_page(
            __('SATORI Forms', 'satori-forms'),
            __('SATORI Forms', 'satori-forms'),
            'edit_posts',
            'satori-forms',
            [$this, 'admin_dashboard_page'],
            'dashicons-feedback',
            60
        );

        add_submenu_page(
            'satori-forms',
            __('Forms', 'satori-forms'),
            __('Forms', 'satori-forms'),
            'edit_posts',
            'edit.php?post_type=satori_form'
        );
    }

    /* -------------------------------------------------
     * Admin Dashboard Page Output
     * -------------------------------------------------*/
    public function admin_dashboard_page()
    {
?>
        <div class="wrap">
            <h1><?php esc_html_e('SATORI Forms Dashboard', 'satori-forms'); ?></h1>
            <p><?php esc_html_e('Welcome to SATORI Forms! Use the Forms submenu to manage your forms.', 'satori-forms'); ?></p>
        </div>
<?php
    }

    /* -------------------------------------------------
     * Enqueue Admin Styles and Scripts
     * -------------------------------------------------*/
    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'satori-forms') !== false || strpos($hook, 'satori_form') !== false) {
            wp_enqueue_style(
                'satori-forms-admin-css',
                plugin_dir_url(__DIR__) . '../../assets/css/satori-forms-main.css',
                [],
                SATORI_FORMS_VERSION
            );
            wp_enqueue_script(
                'satori-forms-admin-js',
                plugin_dir_url(__DIR__) . '../../assets/js/satori-forms-admin.js',
                ['jquery'],
                SATORI_FORMS_VERSION,
                true
            );
        }
    }
}
