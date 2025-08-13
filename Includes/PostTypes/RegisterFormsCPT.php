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

namespace Satori\Forms\PostTypes;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * CPT: satori_form (the actual Form definitions)
 * -------------------------------------------------*/
class RegisterFormsCPT
{
    /** @var bool per-request guard so register() only runs once */
    private static bool $did_register = false;

    public function __construct()
    {
        // Register on init (single-run hook). Priority 10 is fine.
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        if (self::$did_register) {
            // Prevent double-run within the same request.
            return;
        }
        self::$did_register = true;

        $labels = [
            'name'               => __('Forms', 'satori-forms'),
            'singular_name'      => __('Form', 'satori-forms'),
            'menu_name'          => __('Forms', 'satori-forms'),
            'name_admin_bar'     => __('Form', 'satori-forms'),
            'add_new'            => __('Add New', 'satori-forms'),
            'add_new_item'       => __('Add New Form', 'satori-forms'),
            'edit_item'          => __('Edit Form', 'satori-forms'),
            'new_item'           => __('New Form', 'satori-forms'),
            'view_item'          => __('View Form', 'satori-forms'),
            'search_items'       => __('Search Forms', 'satori-forms'),
            'not_found'          => __('No forms found.', 'satori-forms'),
            'not_found_in_trash' => __('No forms found in Trash.', 'satori-forms'),
            'all_items'          => __('Forms', 'satori-forms'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'satori-forms', // under our top-level menu
            'show_in_admin_bar'  => false,
            'hierarchical'       => false,
            'supports'           => ['title'],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ];

        register_post_type('satori_form', $args);

        // Cleaner debug: show which hook fired this registration
        if (function_exists('satori_forms_log')) {
            $hook = function_exists('current_action') ? (string) current_action() : 'unknown';
            satori_forms_log(
                sprintf('Registered CPT: %s (show_in_menu => %s) via %s', 'satori_form', 'satori-forms', $hook)
            );
        }
    }
}
