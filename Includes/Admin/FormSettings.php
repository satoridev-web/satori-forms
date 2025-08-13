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
 * Per-form Settings (notify email + success message)
 * -------------------------------------------------*/
class FormSettings
{
    public function __construct()
    {
        add_action('add_meta_boxes_satori_form', [$this, 'add_metabox']);
        add_action('save_post_satori_form', [$this, 'save'], 10, 2);
    }

    public function add_metabox(): void
    {
        add_meta_box(
            'satori_form_settings',
            __('Form Settings', 'satori-forms'),
            [$this, 'render'],
            'satori_form',
            'side',
            'high'
        );
    }

    public function render(\WP_Post $post): void
    {
        wp_nonce_field('satori_form_settings', 'satori_form_settings_nonce');

        $notify = (string) get_post_meta($post->ID, '_satori_notify_email', true);
        $msg    = (string) get_post_meta($post->ID, '_satori_success_message', true);

?>
        <p><label for="satori_notify_email"><strong><?php esc_html_e('Notification email(s)', 'satori-forms'); ?></strong></label></p>
        <p>
            <input type="text" class="widefat" id="satori_notify_email" name="satori_notify_email" value="<?php echo esc_attr($notify); ?>" placeholder="<?php esc_attr_e('example@site.com, another@site.com', 'satori-forms'); ?>">
        </p>
        <p class="description"><?php esc_html_e('Leave blank to use the site admin email. Separate multiple addresses with commas.', 'satori-forms'); ?></p>

        <hr />

        <p><label for="satori_success_message"><strong><?php esc_html_e('Success message', 'satori-forms'); ?></strong></label></p>
        <p>
            <textarea class="widefat" id="satori_success_message" name="satori_success_message" rows="3" placeholder="<?php esc_attr_e('Thanks! Your submission was received.', 'satori-forms'); ?>"><?php echo esc_textarea($msg); ?></textarea>
        </p>
<?php
    }

    public function save(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST['satori_form_settings_nonce']) || !wp_verify_nonce($_POST['satori_form_settings_nonce'], 'satori_form_settings')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $notify = isset($_POST['satori_notify_email']) ? sanitize_text_field((string) $_POST['satori_notify_email']) : '';
        $msg    = isset($_POST['satori_success_message']) ? wp_kses_post((string) $_POST['satori_success_message']) : '';

        update_post_meta($post_id, '_satori_notify_email', $notify);
        update_post_meta($post_id, '_satori_success_message', $msg);

        if (function_exists('satori_forms_log')) {
            satori_forms_log('Saved form settings', [
                'form_id' => $post_id,
                'notify'  => $notify !== '' ? 'custom' : 'admin_email',
                'msg_len' => strlen($msg),
            ]);
        }
    }
}
