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

namespace Satori\Forms\Handlers;

use Satori\Forms\Admin\FormBuilder;
use Satori\Forms\PostTypes\RegisterEntriesCPT;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Handles front-end submissions -> creates form_entry
 * -------------------------------------------------*/
class SubmissionHandler
{
    public function __construct()
    {
        add_action('admin_post_nopriv_satori_forms_submit', [$this, 'handle']);
        add_action('admin_post_satori_forms_submit', [$this, 'handle']);
    }

    public function handle(): void
    {
        $redirect = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url('/');

        $form_id = isset($_POST['satori_form_id']) ? absint($_POST['satori_form_id']) : 0;
        $nonce   = $_POST['satori_forms_nonce'] ?? '';

        if (!$form_id || !wp_verify_nonce($nonce, 'satori_forms_submit_' . $form_id)) {
            $this->fail($redirect, 'nonce');
        }

        // Honeypot
        if (!empty($_POST['website'])) {
            $this->fail($redirect, 'honeypot');
        }

        $form_post = get_post($form_id);
        if (!$form_post || $form_post->post_type !== 'satori_form') {
            $this->fail($redirect, 'form');
        }

        $fields_def = get_post_meta($form_id, FormBuilder::META_FIELDS, true);
        $fields_def = is_array($fields_def) ? $fields_def : [];

        $input = isset($_POST['fields']) && is_array($_POST['fields']) ? $_POST['fields'] : [];

        $errors = [];
        $clean  = [];

        foreach ($fields_def as $f) {
            $type = isset($f['type']) ? (string) $f['type'] : 'text';
            $name = isset($f['name']) ? (string) $f['name'] : '';
            $req  = !empty($f['required']);

            if ($type === 'submit' || $name === '') {
                continue;
            }

            $key = sanitize_key($name);
            $val = $input[$key] ?? '';

            switch ($type) {
                case 'email':
                    $val = is_string($val) ? trim($val) : '';
                    if ($req && $val === '') {
                        $errors[$key] = __('Required', 'satori-forms');
                    } elseif ($val !== '' && !is_email($val)) {
                        $errors[$key] = __('Invalid email', 'satori-forms');
                    } else {
                        $clean[$key] = sanitize_email($val);
                    }
                    break;

                case 'number':
                    $val = is_string($val) ? trim($val) : '';
                    if ($req && $val === '') {
                        $errors[$key] = __('Required', 'satori-forms');
                    } elseif ($val !== '' && !is_numeric($val)) {
                        $errors[$key] = __('Invalid number', 'satori-forms');
                    } else {
                        $clean[$key] = $val;
                    }
                    break;

                case 'checkbox':
                    $val = isset($_POST['fields'][$key]) ? (array) $_POST['fields'][$key] : [];
                    if ($req && empty($val)) {
                        $errors[$key] = __('Required', 'satori-forms');
                    } else {
                        $clean[$key] = array_map('sanitize_text_field', $val);
                    }
                    break;

                case 'textarea':
                case 'text':
                case 'date':
                case 'radio':
                case 'select':
                default:
                    $val = is_string($val) ? trim($val) : '';
                    if ($req && $val === '') {
                        $errors[$key] = __('Required', 'satori-forms');
                    } else {
                        $clean[$key] = sanitize_text_field($val);
                    }
                    break;
            }
        }

        if (!empty($errors)) {
            if (function_exists('satori_forms_log')) {
                satori_forms_log('Submission errors', ['form_id' => $form_id, 'errors' => $errors]);
            }
            $this->fail($redirect, 'validation');
        }

        // Create entry
        $entry_title = sprintf(
            __('Entry – %1$s – %2$s', 'satori-forms'),
            get_the_title($form_id),
            current_time(get_option('date_format') . ' ' . get_option('time_format'))
        );

        $entry_id = wp_insert_post([
            'post_type'   => 'form_entry',
            'post_status' => 'publish',
            'post_title'  => $entry_title,
        ], true);

        if (is_wp_error($entry_id)) {
            $this->fail($redirect, 'insert');
        }

        // Link to parent form + store payload
        update_post_meta($entry_id, RegisterEntriesCPT::META_FORM_ID, $form_id);
        update_post_meta($entry_id, '_satori_entry_fields', $clean);

        // Capture metadata: IP, UA, User ID
        $ip      = $this->get_client_ip();
        $ua      = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 1000) : '';
        $user_id = get_current_user_id();

        update_post_meta($entry_id, RegisterEntriesCPT::META_IP, $ip);
        update_post_meta($entry_id, RegisterEntriesCPT::META_USER_AGENT, $ua);
        update_post_meta($entry_id, RegisterEntriesCPT::META_USER_ID, $user_id);

        if (function_exists('satori_forms_log')) {
            satori_forms_log('Submission stored', [
                'entry_id' => $entry_id,
                'form_id'  => $form_id,
                'ip'       => $ip,
                'user_id'  => $user_id,
            ]);
        }

        // Notify (per-form override -> fallback to site admin)
        $to_override = trim((string) get_post_meta($form_id, '_satori_notify_email', true));
        $to_emails   = $to_override !== '' ? $to_override : get_option('admin_email');

        // Subject includes IP and User ID
        $subject = sprintf(
            /* translators: 1: form title, 2: IP, 3: user id, 4: entry id */
            __('New submission: %1$s — IP %2$s — User %3$s — Entry #%4$d', 'satori-forms'),
            get_the_title($form_id),
            $ip ?: __('unknown', 'satori-forms'),
            $user_id ?: 0,
            (int) $entry_id
        );

        $body = '';
        foreach ($clean as $k => $v) {
            if (is_array($v)) {
                $v = implode(', ', array_map('strval', $v));
            }
            $body .= $k . ': ' . $v . "\n";
        }
        $body .= "\n---\n";
        $body .= 'IP: ' . $ip . "\n";
        $body .= 'User ID: ' . ($user_id ? (string) $user_id : '0') . "\n";
        $body .= 'User Agent: ' . $ua . "\n";
        $body .= 'Entry ID: ' . (int) $entry_id . "\n";

        // Allow comma-separated list
        $recipients = array_map('trim', explode(',', $to_emails));
        foreach ($recipients as $rcpt) {
            if (is_email($rcpt)) {
                wp_mail($rcpt, $subject, $body);
            }
        }

        // Redirect success
        $this->success($redirect);
    }

    private function get_client_ip(): string
    {
        $keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        ];
        foreach ($keys as $k) {
            if (!empty($_SERVER[$k])) {
                $raw = (string) $_SERVER[$k];
                $ip  = trim(explode(',', $raw)[0]);
                $ip  = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
                if ($ip) {
                    return $ip;
                }
            }
        }
        return '';
    }

    private function success(string $url): void
    {
        $url = add_query_arg('sf_success', '1', $url);
        wp_safe_redirect($url);
        exit;
    }

    private function fail(string $url, string $reason): void
    {
        $url = add_query_arg('sf_error', $reason, $url);
        wp_safe_redirect($url);
        exit;
    }
}
