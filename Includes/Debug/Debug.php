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
 * Debug flag helper
 * -------------------------------------------------*/
if (!function_exists('satori_forms_is_debug')) {
    function satori_forms_is_debug(): bool
    {
        if (defined('SATORI_FORMS_DEBUG') && SATORI_FORMS_DEBUG) {
            return true;
        }
        if (get_option('satori_forms_debug_enabled', false)) {
            return true;
        }
        return (defined('WP_DEBUG') && WP_DEBUG);
    }
}

/* -------------------------------------------------
 * Central log helper
 * -------------------------------------------------*/
if (!function_exists('satori_forms_log')) {
    /**
     * @param mixed $message
     * @param array $context
     */
    function satori_forms_log($message, array $context = []): void
    {
        if (!satori_forms_is_debug()) {
            return;
        }
        if (is_array($message) || is_object($message)) {
            $message = wp_json_encode($message);
        }
        if (!empty($context)) {
            $message .= ' ' . wp_json_encode($context);
        }
        error_log('[SATORI Forms] ' . $message);
    }
}
