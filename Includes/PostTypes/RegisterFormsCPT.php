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

namespace Satori\Forms\PostTypes;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Register CPT: satori_form
 * -------------------------------------------------*/
class RegisterFormsCPT
{

    /* -------------------------------------------------
     * Class Constructor
     * -------------------------------------------------*/
    public function __construct()
    {
        add_action('init', [$this, 'register_cpt']);
    }

    /* -------------------------------------------------
     * Register Custom Post Type
     * -------------------------------------------------*/
    public function register_cpt()
    {
        $labels = [
            'name'                  => __('Forms', 'satori-forms'),
            'singular_name'         => __('Form', 'satori-forms'),
            'menu_name'             => __('SATORI Forms', 'satori-forms'),
            'name_admin_bar'        => __('Form', 'satori-forms'),
            'add_new'               => __('Add New', 'satori-forms'),
            'add_new_item'          => __('Add New Form', 'satori-forms'),
            'edit_item'             => __('Edit Form', 'satori-forms'),
            'new_item'              => __('New Form', 'satori-forms'),
            'view_item'             => __('View Form', 'satori-forms'),
            'search_items'          => __('Search Forms', 'satori-forms'),
            'not_found'             => __('No forms found.', 'satori-forms'),
            'not_found_in_trash'    => __('No forms found in Trash.', 'satori-forms'),
            'all_items'             => __('All Forms', 'satori-forms'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false,  // Admin menu added separately
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => ['title'],
            'has_archive'         => false,
            'menu_position'       => 60,
            'menu_icon'           => 'dashicons-feedback',
            'exclude_from_search' => true,
            'show_in_rest'        => true,
        ];

        register_post_type('satori_form', $args);
    }
}
