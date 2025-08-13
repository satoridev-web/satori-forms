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

namespace Satori\Forms\Render;

use Satori\Forms\Admin\FormBuilder;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Renderer for front-end form markup
 * -------------------------------------------------*/
class Renderer
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_assets']);
    }

    public function enqueue_front_assets(): void
    {
        wp_register_script(
            'satori-forms-frontend',
            SATORI_FORMS_PLUGIN_URL . 'assets/js/satori-forms-frontend.js',
            [],
            SATORI_FORMS_VERSION,
            true
        );
        wp_register_style(
            'satori-forms-frontend',
            SATORI_FORMS_PLUGIN_URL . 'assets/css/satori-forms-admin.css',
            [],
            SATORI_FORMS_VERSION
        );
    }

    public static function render_form(int $form_id): string
    {
        $form = get_post($form_id);
        if (!$form || $form->post_type !== 'satori_form') {
            return '<div class="satori-form-error">' . esc_html__('Form not found.', 'satori-forms') . '</div>';
        }

        $fields = get_post_meta($form_id, FormBuilder::META_FIELDS, true);
        $fields = is_array($fields) ? $fields : [];

        $action = esc_url(admin_url('admin-post.php'));
        $nonce  = wp_create_nonce('satori_forms_submit_' . $form_id);

        // Notices
        $success_message = trim((string) get_post_meta($form_id, '_satori_success_message', true));
        if ($success_message === '') {
            $success_message = __('Thanks! Your submission was received.', 'satori-forms');
        }

        $notice = '';
        if (isset($_GET['sf_success']) && $_GET['sf_success'] === '1') {
            $notice = '<div class="satori-form-success">' . esc_html($success_message) . '</div>';
        } elseif (isset($_GET['sf_error'])) {
            $code = sanitize_key((string) $_GET['sf_error']);
            $msg  = __('There was a problem with your submission. Please try again.', 'satori-forms');
            if ($code === 'nonce') {
                $msg = __('Security check failed. Please reload the page and try again.', 'satori-forms');
            }
            if ($code === 'honeypot') {
                $msg = __('Spam check failed. Please try again.', 'satori-forms');
            }
            if ($code === 'validation') {
                $msg = __('Please complete the required fields and try again.', 'satori-forms');
            }
            $notice = '<div class="satori-form-error">' . esc_html($msg) . '</div>';
        }

        ob_start();
        echo $notice;

        do_action('satori_forms_before_form', $form_id);
?>
        <form class="satori-form" method="post" action="<?php echo $action; ?>" novalidate>
            <input type="hidden" name="action" value="satori_forms_submit">
            <input type="hidden" name="satori_form_id" value="<?php echo (int) $form_id; ?>">
            <input type="hidden" name="satori_forms_nonce" value="<?php echo esc_attr($nonce); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo esc_url(self::current_url()); ?>">
            <div style="display:none">
                <label><?php esc_html_e('Leave this field empty', 'satori-forms'); ?></label>
                <input type="text" name="website" value="">
            </div>

            <?php foreach ($fields as $f) :
                $type  = isset($f['type']) ? (string) $f['type'] : 'text';
                $name  = isset($f['name']) ? (string) $f['name'] : 'field';
                $label = isset($f['label']) ? (string) $f['label'] : '';
                $req   = !empty($f['required']);
                $ph    = isset($f['placeholder']) ? (string) $f['placeholder'] : '';
                $opts  = isset($f['options']) && is_array($f['options']) ? $f['options'] : [];
                $field_name = 'fields[' . sanitize_key($name) . ']';
            ?>
                <div class="satori-form-field satori-form-field-<?php echo esc_attr($type); ?>">
                    <?php if ($type !== 'submit') : ?>
                        <label>
                            <?php echo esc_html($label); ?>
                            <?php if ($req) : ?><span class="required">*</span><?php endif; ?>
                        </label>
                    <?php endif; ?>

                    <?php
                    switch ($type) {
                        case 'email':
                        case 'text':
                        case 'number':
                        case 'date':
                            printf(
                                '<input type="%1$s" name="%2$s" value="" %3$s placeholder="%4$s" />',
                                esc_attr($type),
                                esc_attr($field_name),
                                $req ? 'required' : '',
                                esc_attr($ph)
                            );
                            break;

                        case 'textarea':
                            printf(
                                '<textarea name="%1$s" %2$s placeholder="%3$s"></textarea>',
                                esc_attr($field_name),
                                $req ? 'required' : '',
                                esc_attr($ph)
                            );
                            break;

                        case 'select':
                            echo '<select name="' . esc_attr($field_name) . '" ' . ($req ? 'required' : '') . '>';
                            echo '<option value="">' . esc_html__('— Select —', 'satori-forms') . '</option>';
                            foreach ($opts as $opt) {
                                echo '<option value="' . esc_attr($opt) . '">' . esc_html($opt) . '</option>';
                            }
                            echo '</select>';
                            break;

                        case 'radio':
                            foreach ($opts as $opt) {
                                $id = sanitize_html_class($name . '_' . md5($opt));
                                echo '<label for="' . esc_attr($id) . '">';
                                echo '<input type="radio" id="' . esc_attr($id) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($opt) . '" ' . ($req ? 'required' : '') . '>';
                                echo ' ' . esc_html($opt) . '</label> ';
                            }
                            break;

                        case 'checkbox':
                            $array_name = 'fields[' . sanitize_key($name) . '][]';
                            foreach ($opts as $opt) {
                                $id = sanitize_html_class($name . '_' . md5($opt));
                                echo '<label for="' . esc_attr($id) . '">';
                                echo '<input type="checkbox" id="' . esc_attr($id) . '" name="' . esc_attr($array_name) . '" value="' . esc_attr($opt) . '">';
                                echo ' ' . esc_html($opt) . '</label> ';
                            }
                            break;

                        case 'submit':
                            $btn = $label !== '' ? $label : __('Submit', 'satori-forms');
                            echo '<button type="submit" class="button">' . esc_html($btn) . '</button>';
                            break;
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </form>
<?php
        do_action('satori_forms_after_form', $form_id);

        return ob_get_clean();
    }

    public static function current_url(): string
    {
        $scheme = is_ssl() ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'] ?? '';
        $uri    = $_SERVER['REQUEST_URI'] ?? '';
        return esc_url_raw($scheme . $host . $uri);
    }
}
