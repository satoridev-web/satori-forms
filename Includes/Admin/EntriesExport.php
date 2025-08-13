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

use Satori\Forms\PostTypes\RegisterEntriesCPT;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Entries CSV Export (by Form, optional date range)
 * -------------------------------------------------*/
class EntriesExport
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'menu']);
    }

    public function menu(): void
    {
        // Guard: avoid duplicate “Export CSV” if someone else registered same slug.
        global $submenu;
        $already = false;
        $parent  = 'satori-forms';
        $slug    = 'satori-forms-export';

        if (isset($submenu[$parent]) && is_array($submenu[$parent])) {
            foreach ($submenu[$parent] as $item) {
                // $item = [page_title, capability, menu_slug, menu_title]
                if (!empty($item[2]) && $item[2] === $slug) {
                    $already = true;
                    break;
                }
            }
        }

        if (!$already) {
            add_submenu_page(
                $parent,
                __('Export CSV', 'satori-forms'),
                __('Export CSV', 'satori-forms'),
                'manage_options',
                $slug,
                [$this, 'render']
            );
        }
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission.', 'satori-forms'));
        }

        // Handle download
        if (isset($_GET['download']) && $_GET['download'] === '1' && check_admin_referer('satori_forms_export', 'sfe_nonce')) {
            $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
            $from    = isset($_GET['from']) ? sanitize_text_field((string) $_GET['from']) : '';
            $to      = isset($_GET['to']) ? sanitize_text_field((string) $_GET['to']) : '';
            $this->download_csv($form_id, $from, $to);
            return;
        }

        $selected = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        $from     = isset($_GET['from']) ? sanitize_text_field((string) $_GET['from']) : '';
        $to       = isset($_GET['to']) ? sanitize_text_field((string) $_GET['to']) : '';

        $forms = get_posts([
            'post_type'      => 'satori_form',
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ]);

?>
        <div class="wrap">
            <h1><?php esc_html_e('Export Entries (CSV)', 'satori-forms'); ?></h1>

            <form method="get">
                <input type="hidden" name="page" value="satori-forms-export" />
                <?php wp_nonce_field('satori_forms_export', 'sfe_nonce'); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('Form', 'satori-forms'); ?></th>
                            <td>
                                <select name="form_id" required>
                                    <option value=""><?php esc_html_e('— Select a form —', 'satori-forms'); ?></option>
                                    <?php foreach ($forms as $fid) : ?>
                                        <option value="<?php echo (int) $fid; ?>" <?php selected($selected, (int) $fid); ?>>
                                            <?php echo esc_html(get_the_title($fid) ?: ('#' . $fid)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('From date', 'satori-forms'); ?></th>
                            <td><input type="date" name="from" value="<?php echo esc_attr($from); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('To date', 'satori-forms'); ?></th>
                            <td><input type="date" name="to" value="<?php echo esc_attr($to); ?>"></td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <button class="button button-primary" name="download" value="1"><?php esc_html_e('Download CSV', 'satori-forms'); ?></button>
                </p>
            </form>
        </div>
<?php
    }

    private function download_csv(int $form_id, string $from, string $to): void
    {
        if ($form_id <= 0) {
            wp_die(__('Form is required.', 'satori-forms'));
        }

        $args = [
            'post_type'      => 'form_entry',
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'     => RegisterEntriesCPT::META_FORM_ID,
                    'value'   => $form_id,
                    'type'    => 'NUMERIC',
                    'compare' => '=',
                ]
            ],
            'date_query' => [],
            'fields'     => 'ids',
        ];

        $dq = [];
        if ($from !== '') {
            $dq['after']  = $from;
        }
        if ($to   !== '') {
            $dq['before'] = $to . ' 23:59:59';
        }
        if (!empty($dq)) {
            $args['date_query'][] = $dq;
        }

        $entries = get_posts($args);

        // Build dynamic header
        $dynamic_columns = [];
        foreach ($entries as $eid) {
            $payload = get_post_meta($eid, '_satori_entry_fields', true);
            $payload = is_array($payload) ? $payload : [];
            foreach ($payload as $k => $_) {
                $dynamic_columns[$k] = true;
            }
        }
        $dynamic_columns = array_keys($dynamic_columns);
        sort($dynamic_columns, SORT_NATURAL);

        // Fixed columns first
        $fixed = ['entry_id', 'date', 'form_id', 'user_id', 'ip', 'user_agent'];

        // Output headers
        $filename = 'satori-forms-' . sanitize_title(get_the_title($form_id) ?: ('form-' . $form_id)) . '-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $out = fopen('php://output', 'w');
        fputcsv($out, array_merge($fixed, $dynamic_columns));

        foreach ($entries as $eid) {
            $payload   = get_post_meta($eid, '_satori_entry_fields', true);
            $payload   = is_array($payload) ? $payload : [];

            $user_id   = (int) get_post_meta($eid, '_satori_entry_user_id', true);
            $ip        = (string) get_post_meta($eid, '_satori_entry_ip', true);
            $ua        = (string) get_post_meta($eid, '_satori_entry_user_agent', true);

            $row = [
                $eid,
                get_post_time('Y-m-d H:i:s', false, $eid, true),
                $form_id,
                $user_id,
                $ip,
                $ua,
            ];

            foreach ($dynamic_columns as $col) {
                $val = $payload[$col] ?? '';
                if (is_array($val)) {
                    $val = implode(', ', array_map('strval', $val));
                }
                $row[] = $val;
            }

            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }
}
