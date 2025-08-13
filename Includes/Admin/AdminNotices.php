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
 * Admin Notice
 * -------------------------------------------------*/
if (!function_exists('satori_forms_admin_notice')) {
    /**
     * Output a core WP admin notice
     *
     * @param string $message
     * @param string $type success|error|warning|info
     * @return void
     */
    function satori_forms_admin_notice(string $message, string $type = 'success'): void
    {
        $class = 'notice';
        if ($type === 'error') {
            $class .= ' notice-error';
        }
        if ($type === 'warning') {
            $class .= ' notice-warning';
        }
        if ($type === 'info') {
            $class .= ' notice-info';
        }
        if ($type === 'success') {
            $class .= ' notice-success';
        }

        printf(
            '<div class="%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr($class),
            wp_kses_post($message)
        );
    }
}
