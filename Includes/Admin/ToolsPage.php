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

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Render Tools / Debug Page
 * -------------------------------------------------*/
if (!function_exists('satori_forms_render_tools_page')) :
    function satori_forms_render_tools_page(): void
    {
        if (isset($_POST['satori_forms_tools_nonce']) && wp_verify_nonce($_POST['satori_forms_tools_nonce'], 'satori_forms_tools_action')) {
            $enabled = !empty($_POST['satori_forms_debug_enabled']) ? 1 : 0;
            update_option('satori_forms_debug_enabled', $enabled);

            if (function_exists('satori_forms_admin_notice')) {
                satori_forms_admin_notice(__('Debug setting saved.', 'satori-forms'), 'success');
            }
        }

        $checked = get_option('satori_forms_debug_enabled', 0) ? 'checked' : '';
?>
        <div class="wrap">
            <h1><?php esc_html_e('Satori Forms — Tools / Debug', 'satori-forms'); ?></h1>

            <form method="post">
                <?php wp_nonce_field('satori_forms_tools_action', 'satori_forms_tools_nonce'); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('Enable Debug Logging', 'satori-forms'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="satori_forms_debug_enabled" value="1" <?php echo $checked; ?> />
                                    <?php esc_html_e('Write debug messages to PHP error_log when enabled.', 'satori-forms'); ?>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button(__('Save', 'satori-forms')); ?>
            </form>
        </div>
<?php
    }
endif;
