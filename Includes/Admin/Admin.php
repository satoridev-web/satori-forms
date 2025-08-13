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

declare(strict_types=1);

namespace Satori\Forms\Admin;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Admin Menu and Asset Enqueue for SATORI Forms
 * -------------------------------------------------*/
class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_admin_menu'], 20);
        add_action('admin_menu', [$this, 'cleanup_legacy_top_levels'], 99);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_admin_menu(): void
    {
        add_menu_page(
            __('Satori Forms', 'satori-forms'),
            __('Satori Forms', 'satori-forms'),
            'edit_posts',
            'satori-forms',
            [$this, 'admin_dashboard_page'],
            'dashicons-feedback',
            26
        );

        // CPT submenus (Forms, Add New) are auto-added by WP when CPT uses show_in_menu => 'satori-forms'.

        add_submenu_page(
            'satori-forms',
            __('Tools / Debug', 'satori-forms'),
            __('Tools / Debug', 'satori-forms'),
            'manage_options',
            'satori-forms-tools',
            function () {
                if (function_exists('satori_forms_render_tools_page')) {
                    \satori_forms_render_tools_page();
                } else {
                    echo '<div class="wrap"><h1>' . esc_html__('Tools / Debug', 'satori-forms') . '</h1>';
                    echo '<p>' . esc_html__('Tools page callback not found. Ensure includes/Admin/ToolsPage.php is loaded.', 'satori-forms') . '</p></div>';
                }
            }
        );

        // Do NOT add Export CSV here (EntriesExport owns that submenu).
    }

    public function cleanup_legacy_top_levels(): void
    {
        // Defensive cleanup for older slugs
        remove_menu_page('satori-forms-legacy');
        remove_menu_page('satori-forms-top');

        // If anything else accidentally added duplicate Export with same slug, remove it here
        remove_submenu_page('satori-forms', 'satori-forms-export');
    }

    public function admin_dashboard_page(): void
    {
?>
        <div class="wrap">
            <h1><?php esc_html_e('Satori Forms', 'satori-forms'); ?></h1>
            <p><?php esc_html_e('Use the left menu to manage Forms and run Tools.', 'satori-forms'); ?></p>
        </div>
<?php
    }

    public function enqueue_admin_assets(string $hook): void
    {
        $load = (strpos($hook, 'satori-forms') !== false);

        if (!$load && function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && isset($screen->post_type)) {
                $load = in_array($screen->post_type, ['satori_form', 'form_entry'], true);
            }
        }

        if (!$load) {
            return;
        }

        $css_path = SATORI_FORMS_PLUGIN_DIR . 'Assets/Css/satori-forms-admin.css';
        $ver = defined('SATORI_FORMS_VERSION') ? SATORI_FORMS_VERSION : (file_exists($css_path) ? (string) filemtime($css_path) : null);

        wp_enqueue_style(
            'satori-forms-admin-css',
            SATORI_FORMS_PLUGIN_URL . 'Assets/Css/satori-forms-admin.css',
            [],
            $ver
        );

        wp_enqueue_script(
            'satori-forms-admin-js',
            SATORI_FORMS_PLUGIN_URL . 'Assets/Js/satori-forms-admin.js',
            ['jquery'],
            $ver,
            true
        );
    }
}
