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

namespace Satori\Forms\MetaBoxes;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Meta Boxes: Form Builder
 * -------------------------------------------------*/
class MetaBoxes
{

    /**
     * Meta key where the JSON config is stored.
     */
    const META_KEY = '_satori_form_config';

    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post_satori_form', [$this, 'save_form_meta'], 10, 2);

        // Enqueue assets for form post edit screen
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_meta_boxes()
    {
        add_meta_box(
            'satori-forms-builder',
            __('SATORI Form Builder', 'satori-forms'),
            [$this, 'render_form_builder_meta_box'],
            'satori_form',
            'normal',
            'high'
        );

        add_meta_box(
            'satori-forms-settings',
            __('Form Settings', 'satori-forms'),
            [$this, 'render_form_settings_meta_box'],
            'satori_form',
            'side',
            'default'
        );
    }

    public function enqueue_admin_assets($hook)
    {
        // Only load on satori_form edit/add screens
        if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();
        if (! $screen || 'satori_form' !== $screen->post_type) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'satori-forms-admin-css',
            plugin_dir_url(__DIR__) . '../../Assets/Css/satori-forms-admin.css',
            [],
            SATORI_FORMS_VERSION
        );

        // JS
        wp_enqueue_script(
            'satori-forms-admin-js',
            plugin_dir_url(__DIR__) . '../../Assets/Js/satori-forms-admin.js',
            ['jquery', 'jquery-ui-sortable'],
            SATORI_FORMS_VERSION,
            true
        );

        // Pass available field types and nonce
        $field_types = [
            'text'      => __('Text', 'satori-forms'),
            'textarea'  => __('Textarea', 'satori-forms'),
            'email'     => __('Email', 'satori-forms'),
            'number'    => __('Number', 'satori-forms'),
            'select'    => __('Dropdown', 'satori-forms'),
            'radio'     => __('Radio', 'satori-forms'),
            'checkbox'  => __('Checkbox', 'satori-forms'),
            'date'      => __('Date', 'satori-forms'),
            // Add more as needed
        ];

        wp_localize_script(
            'satori-forms-admin-js',
            'SatoriFormsAdmin',
            [
                'fieldTypes' => $field_types,
                'nonce'      => wp_create_nonce('satori_forms_meta_nonce'),
                'strings'    => [
                    'addField' => __('Add Field', 'satori-forms'),
                    'noFields' => __('No fields yet. Add one to get started.', 'satori-forms'),
                ],
            ]
        );
    }

    public function render_form_builder_meta_box($post)
    {
        // Get existing config
        $config_json = get_post_meta($post->ID, self::META_KEY, true);
        $config = [];
        if ($config_json) {
            $decoded = json_decode($config_json, true);
            if (is_array($decoded)) {
                $config = $decoded;
            }
        }

        // Nonce
        wp_nonce_field('satori_forms_save_meta', 'satori_forms_meta_nonce');

        // Hidden input to store JSON
?>
        <div id="satori-forms-builder">
            <input type="hidden" id="satori-forms-config" name="<?php echo esc_attr(self::META_KEY); ?>" value="<?php echo esc_attr(wp_json_encode($config)); ?>" />

            <div class="satori-forms-builder-actions">
                <select id="satori-forms-field-type" class="widefat">
                    <?php foreach (Satori\Forms\MetaBoxes\MetaBoxes::available_field_options() as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="satori-forms-add-field" class="button button-primary" style="margin-top:8px;">
                    <?php esc_html_e('Add Field', 'satori-forms'); ?>
                </button>
            </div>

            <div id="satori-forms-fields-list" class="satori-forms-fields-list">
                <?php
                // Include the admin partial which renders fields from $config
                $fields = $config['fields'] ?? [];
                include plugin_dir_path(__DIR__) . '../Templates/Parts/satori-forms-form-fields.php';
                ?>
            </div>

            <p class="description"><?php esc_html_e('Drag to reorder fields. Click the gear icon to edit a field.', 'satori-forms'); ?></p>
        </div>
    <?php
    }

    public function render_form_settings_meta_box($post)
    {
        $config_json = get_post_meta($post->ID, self::META_KEY, true);
        $config = $config_json ? json_decode($config_json, true) : [];

        $settings = $config['settings'] ?? [];
        $submit_text = $settings['submit_text'] ?? __('Submit', 'satori-forms');
        $success_message = $settings['success_message'] ?? __('Thanks! Your submission has been received.', 'satori-forms');
    ?>
        <p>
            <label for="satori-form-submit-text"><?php esc_html_e('Submit button text', 'satori-forms'); ?></label>
            <input type="text" id="satori-form-submit-text" name="satori_form_submit_text" class="widefat" value="<?php echo esc_attr($submit_text); ?>">
        </p>

        <p>
            <label for="satori-form-success-message"><?php esc_html_e('Success message', 'satori-forms'); ?></label>
            <textarea id="satori-form-success-message" name="satori_form_success_message" class="widefat" rows="3"><?php echo esc_textarea($success_message); ?></textarea>
        </p>

        <p>
            <label>
                <input type="checkbox" name="satori_form_honeypot" value="1" <?php checked($settings['honeypot'] ?? 0, 1); ?>>
                <?php esc_html_e('Enable honeypot spam protection', 'satori-forms'); ?>
            </label>
        </p>

        <p>
            <strong><?php esc_html_e('Shortcode', 'satori-forms'); ?></strong><br />
            <code>[satori_form id="<?php echo esc_attr(get_the_ID()); ?>"]</code>
        </p>
<?php
    }

    public static function available_field_options()
    {
        return [
            'text'     => __('Text', 'satori-forms'),
            'textarea' => __('Textarea', 'satori-forms'),
            'email'    => __('Email', 'satori-forms'),
            'number'   => __('Number', 'satori-forms'),
            'select'   => __('Dropdown', 'satori-forms'),
            'radio'    => __('Radio', 'satori-forms'),
            'checkbox' => __('Checkbox', 'satori-forms'),
            'date'     => __('Date', 'satori-forms'),
        ];
    }

    public function save_form_meta($post_id, $post)
    {
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Capability
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check nonce
        if (! isset($_POST['satori_forms_meta_nonce']) || ! wp_verify_nonce(wp_unslash($_POST['satori_forms_meta_nonce']), 'satori_forms_save_meta')) {
            return;
        }

        // Get config from hidden field (already JSON)
        $raw = isset($_POST[self::META_KEY]) ? wp_unslash($_POST[self::META_KEY]) : '';
        $config = [];

        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $config = $decoded;
            }
        }

        // Merge settings fields from side meta box
        $settings = $config['settings'] ?? [];
        $settings['submit_text']   = isset($_POST['satori_form_submit_text']) ? sanitize_text_field(wp_unslash($_POST['satori_form_submit_text'])) : $settings['submit_text'] ?? '';
        $settings['success_message'] = isset($_POST['satori_form_success_message']) ? sanitize_textarea_field(wp_unslash($_POST['satori_form_success_message'])) : $settings['success_message'] ?? '';
        $settings['honeypot'] = isset($_POST['satori_form_honeypot']) ? 1 : 0;

        $config['settings'] = $settings;

        // Validate / sanitize fields
        if (isset($config['fields']) && is_array($config['fields'])) {
            foreach ($config['fields'] as $idx => $field) {
                $config['fields'][$idx]['label'] = isset($field['label']) ? sanitize_text_field($field['label']) : '';
                $config['fields'][$idx]['name']  = isset($field['name']) ? sanitize_text_field($field['name']) : sanitize_title($field['label'] ?? 'field-' . $idx);
                $config['fields'][$idx]['required'] = ! empty($field['required']) ? 1 : 0;

                // options for select/radio/checkbox
                if (in_array($field['type'], ['select', 'radio', 'checkbox'], true)) {
                    $raw_options = $field['options'] ?? '';
                    if (is_string($raw_options)) {
                        $opts = array_map('trim', explode("\n", $raw_options));
                    } elseif (is_array($raw_options)) {
                        $opts = array_map('sanitize_text_field', $raw_options);
                    } else {
                        $opts = [];
                    }
                    $config['fields'][$idx]['options'] = $opts;
                }
            }
        }

        // Save JSON
        update_post_meta($post_id, self::META_KEY, wp_json_encode($config));
    }
}
