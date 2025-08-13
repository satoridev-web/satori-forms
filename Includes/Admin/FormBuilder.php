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
 * Form Builder Meta Box (stores fields in post meta)
 * -------------------------------------------------*/
class FormBuilder
{
    public const META_FIELDS = '_satori_form_fields';

    public function __construct()
    {
        add_action('add_meta_boxes_satori_form', [$this, 'add_metabox']);
        add_action('save_post_satori_form', [$this, 'save'], 10, 2);
    }

    public function add_metabox(): void
    {
        add_meta_box(
            'satori_form_builder',
            __('Form Fields', 'satori-forms'),
            [$this, 'render'],
            'satori_form',
            'normal',
            'default'
        );
    }

    public function render(\WP_Post $post): void
    {
        wp_nonce_field('satori_form_builder', 'satori_form_builder_nonce');

        $fields = get_post_meta($post->ID, self::META_FIELDS, true);
        $fields = is_array($fields) ? $fields : [];

        $types = [
            'text'     => __('Text', 'satori-forms'),
            'email'    => __('Email', 'satori-forms'),
            'number'   => __('Number', 'satori-forms'),
            'textarea' => __('Textarea', 'satori-forms'),
            'select'   => __('Dropdown', 'satori-forms'),
            'radio'    => __('Radio', 'satori-forms'),
            'checkbox' => __('Checkbox', 'satori-forms'),
            'date'     => __('Date', 'satori-forms'),
            'submit'   => __('Submit Button', 'satori-forms'),
        ];

?>
        <style>
            .satori-form-builder table.widefat th,
            .satori-form-builder table.widefat td {
                vertical-align: top;
            }

            .sfb-small {
                width: 140px;
            }

            .sfb-medium {
                width: 220px;
            }

            .sfb-options {
                width: 100%;
                min-height: 60px;
            }

            .sfb-row-actions {
                display: flex;
                gap: 8px;
                align-items: center;
            }

            .sfb-row {
                background: #fff;
            }
        </style>

        <div class="satori-form-builder">
            <p class="description"><?php esc_html_e('Add your form fields below. For options-based fields (select, radio, checkbox), enter one option per line.', 'satori-forms'); ?></p>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th class="column-primary"><?php esc_html_e('Label', 'satori-forms'); ?></th>
                        <th><?php esc_html_e('Name (slug)', 'satori-forms'); ?></th>
                        <th><?php esc_html_e('Type', 'satori-forms'); ?></th>
                        <th><?php esc_html_e('Required', 'satori-forms'); ?></th>
                        <th><?php esc_html_e('Placeholder / Options', 'satori-forms'); ?></th>
                        <th><?php esc_html_e('Actions', 'satori-forms'); ?></th>
                    </tr>
                </thead>
                <tbody id="sfb-rows">
                    <?php if (empty($fields)) : ?>
                        <tr class="sfb-row">
                            <td><input class="regular-text" type="text" name="sfb[label][]" value="" placeholder="<?php esc_attr_e('Full Name', 'satori-forms'); ?>"></td>
                            <td><input class="sfb-small" type="text" name="sfb[name][]" value="" placeholder="full_name"></td>
                            <td>
                                <select name="sfb[type][]" class="sfb-small">
                                    <?php foreach ($types as $k => $label) : ?>
                                        <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><label><input type="checkbox" name="sfb[required][]" value="1"> <?php esc_html_e('Yes', 'satori-forms'); ?></label></td>
                            <td>
                                <input class="sfb-medium" type="text" name="sfb[placeholder][]" value="" placeholder="<?php esc_attr_e('Placeholder... or leave blank', 'satori-forms'); ?>">
                                <textarea class="sfb-options" name="sfb[options][]" placeholder="<?php esc_attr_e('One option per line (for select/radio/checkbox)', 'satori-forms'); ?>"></textarea>
                            </td>
                            <td class="sfb-row-actions">
                                <button class="button sfb-add"><?php esc_html_e('Add', 'satori-forms'); ?></button>
                                <button class="button sfb-remove"><?php esc_html_e('Remove', 'satori-forms'); ?></button>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($fields as $f) :
                            $label = isset($f['label']) ? (string) $f['label'] : '';
                            $name  = isset($f['name']) ? (string) $f['name'] : '';
                            $type  = isset($f['type']) ? (string) $f['type'] : 'text';
                            $req   = !empty($f['required']);
                            $ph    = isset($f['placeholder']) ? (string) $f['placeholder'] : '';
                            $opts  = isset($f['options']) && is_array($f['options']) ? implode("\n", array_map('strval', $f['options'])) : '';
                        ?>
                            <tr class="sfb-row">
                                <td><input class="regular-text" type="text" name="sfb[label][]" value="<?php echo esc_attr($label); ?>"></td>
                                <td><input class="sfb-small" type="text" name="sfb[name][]" value="<?php echo esc_attr($name); ?>"></td>
                                <td>
                                    <select name="sfb[type][]" class="sfb-small">
                                        <?php foreach ($types as $k => $tlabel) : ?>
                                            <option value="<?php echo esc_attr($k); ?>" <?php selected($type, $k); ?>><?php echo esc_html($tlabel); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><label><input type="checkbox" name="sfb[required][]" value="1" <?php checked($req, true); ?>> <?php esc_html_e('Yes', 'satori-forms'); ?></label></td>
                                <td>
                                    <input class="sfb-medium" type="text" name="sfb[placeholder][]" value="<?php echo esc_attr($ph); ?>">
                                    <textarea class="sfb-options" name="sfb[options][]" placeholder="<?php esc_attr_e('One option per line (for select/radio/checkbox)', 'satori-forms'); ?>"><?php echo esc_textarea($opts); ?></textarea>
                                </td>
                                <td class="sfb-row-actions">
                                    <button class="button sfb-add"><?php esc_html_e('Add', 'satori-forms'); ?></button>
                                    <button class="button sfb-remove"><?php esc_html_e('Remove', 'satori-forms'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p class="description"><?php esc_html_e('“Submit Button” type renders a button using the field’s label as its text.', 'satori-forms'); ?></p>
        </div>

        <script>
            (function($) {
                const $rows = $('#sfb-rows');
                $rows.on('click', '.sfb-add', function(e) {
                    e.preventDefault();
                    const $tr = $(this).closest('tr').clone(true);
                    $tr.find('input[type="text"]').val('');
                    $tr.find('textarea').val('');
                    $tr.find('input[type="checkbox"]').prop('checked', false);
                    $rows.append($tr);
                });
                $rows.on('click', '.sfb-remove', function(e) {
                    e.preventDefault();
                    const $all = $rows.find('tr');
                    if ($all.length > 1) {
                        $(this).closest('tr').remove();
                    }
                });
            })(jQuery);
        </script>
<?php
    }

    public function save(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST['satori_form_builder_nonce']) || !wp_verify_nonce($_POST['satori_form_builder_nonce'], 'satori_form_builder')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $in = isset($_POST['sfb']) && is_array($_POST['sfb']) ? $_POST['sfb'] : [];
        $count = isset($in['label']) && is_array($in['label']) ? count($in['label']) : 0;

        $fields = [];
        for ($i = 0; $i < $count; $i++) {
            $label = isset($in['label'][$i]) ? sanitize_text_field($in['label'][$i]) : '';
            $name  = isset($in['name'][$i]) ? sanitize_key($in['name'][$i]) : '';
            $type  = isset($in['type'][$i]) ? sanitize_key($in['type'][$i]) : 'text';
            $req   = isset($in['required'][$i]) ? 1 : 0;
            $ph    = isset($in['placeholder'][$i]) ? sanitize_text_field($in['placeholder'][$i]) : '';
            $opts  = isset($in['options'][$i]) ? (string) $in['options'][$i] : '';

            if ($label === '' && $name === '' && $type === '') {
                continue;
            }

            $options = [];
            if (in_array($type, ['select', 'radio', 'checkbox'], true) && $opts !== '') {
                $lines = array_map('trim', preg_split('/\r\n|\r|\n/', $opts));
                $lines = array_filter($lines, static function ($v) {
                    return $v !== '';
                });
                $options = array_values(array_map('wp_kses_post', $lines));
            }

            $fields[] = [
                'label'       => $label,
                'name'        => $name ?: 'field_' . ($i + 1),
                'type'        => $type,
                'required'    => $req ? 1 : 0,
                'placeholder' => $ph,
                'options'     => $options,
            ];
        }

        update_post_meta($post_id, self::META_FIELDS, $fields);

        if (function_exists('satori_forms_log')) {
            satori_forms_log('Saved form fields', ['form_id' => $post_id, 'count' => count($fields)]);
        }
    }
}
