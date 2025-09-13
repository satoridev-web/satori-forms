<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Satori_Forms_CPT {
    public static function register() {
        register_post_type( 'satori_forms_form', [
            'labels' => [
                'name' => 'Forms',
                'singular_name' => 'Form'
            ],
            'public' => false,
            'show_ui' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-feedback',
            'supports' => ['title'],
        ]);

        register_post_type( 'satori_forms_entry', [
            'labels' => [
                'name' => 'Entries',
                'singular_name' => 'Entry'
            ],
            'public' => false,
            'show_ui' => true,
            'menu_position' => 26,
            'menu_icon' => 'dashicons-list-view',
            'supports' => ['title'],
        ]);
    }
}
