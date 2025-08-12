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

namespace Satori\Forms\Forms;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Form submission handler (basic)
 * -------------------------------------------------*/
class FormHandler
{

    const META_KEY = '_satori_form_config';

    public function __construct()
    {
        // Hook to front-end form POST target (shortcode will post to admin-ajax or same page)
        add_action('init', [$this, 'maybe_handle_submission'], 20);
    }

    public function maybe_handle_submission()
    {
        if (empty($_POST['satori_form_id'])) {
            return;
        }

        $form_id = intval($_POST['satori_form_id']);
        $config_json = get_post_meta($form_id, self::META_KEY, true);
        $config = $config_json ? json_decode($config_json, true) : [];

        // Basic nonce & validation could be added here (shortcode should include nonce)
        // Example: if ( ! wp_verify_nonce( $_POST['_satori_forms_nonce'], 'satori_forms_submit_' . $form_id ) ) { ... }

        $fields = $config['fields'] ?? [];
        $errors = [];

        // Validate required fields
        foreach ($fields as $field) {
            if (! empty($field['required'])) {
                $name = $field['name'] ?? '';
                $val = isset($_POST[$name]) ? sanitize_text_field(wp_unslash($_POST[$name])) : '';
                if ('' === $val) {
                    $errors[] = sprintf(__('%s is required', 'satori-forms'), $field['label'] ?? $name);
                }
            }
        }

        if (! empty($errors)) {
            // Store errors in transient or redirect back with query args - left as an exercise
            // For now, set a transient and redirect to referrer
            set_transient('satori_forms_errors_' . get_current_user_id(), $errors, 30);
            wp_safe_redirect(wp_get_referer() ?: home_url());
            exit;
        }

        // Persist submission: here we simply insert into a custom DB table or post type.
        // For Phase 2 we will save to post meta on a new entry post type or use an entries table.
        // Placeholder: fire an action that other modules can hook into
        do_action('satori_forms_after_submission', $form_id, $_POST);

        // Redirect with success
        $success_message = $config['settings']['success_message'] ?? __('Thanks for your submission.', 'satori-forms');
        // Store success message in transient to show on redirect
        set_transient('satori_forms_success_' . get_current_user_id(), $success_message, 30);
        wp_safe_redirect(wp_get_referer() ?: home_url());
        exit;
    }
}
