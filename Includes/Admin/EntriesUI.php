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
 * Admin UI around Forms <-> Entries relationship
 * -------------------------------------------------*/
class EntriesUI
{
    public function __construct()
    {
        // Forms list: add "Entries" column
        add_filter('manage_satori_form_posts_columns', [$this, 'forms_columns']);
        add_action('manage_satori_form_posts_custom_column', [$this, 'forms_column_content'], 10, 2);

        // Form editor: "Entries" meta box (latest 10)
        add_action('add_meta_boxes_satori_form', [$this, 'add_entries_metabox']);
    }

    /* -------------------------------------------------
     * Forms list table: "Entries" column
     * -------------------------------------------------*/
    public function forms_columns(array $cols): array
    {
        $new = [];
        foreach ($cols as $key => $label) {
            $new[$key] = $label;
            if ('title' === $key) {
                $new['satori_entries_count'] = __('Entries', 'satori-forms');
            }
        }
        return $new;
    }

    public function forms_column_content(string $col, int $post_id): void
    {
        if ('satori_entries_count' !== $col) {
            return;
        }
        $count = $this->count_entries_for_form($post_id);
        $url   = add_query_arg(
            ['post_type' => 'form_entry', RegisterEntriesCPT::META_FORM_ID => $post_id],
            admin_url('edit.php')
        );

        printf(
            '<a href="%s">%s</a>',
            esc_url($url),
            esc_html(number_format_i18n($count))
        );
    }

    private function count_entries_for_form(int $form_id): int
    {
        global $wpdb;
        $meta_key = RegisterEntriesCPT::META_FORM_ID;
        $sql = $wpdb->prepare(
            "
            SELECT COUNT(1)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} m
                ON p.ID = m.post_id AND m.meta_key = %s AND m.meta_value = %d
            WHERE p.post_type = 'form_entry' AND p.post_status NOT IN ('trash', 'auto-draft')
            ",
            $meta_key,
            $form_id
        );
        $count = (int) $wpdb->get_var($sql);
        return $count;
    }

    /* -------------------------------------------------
     * Form editor: "Entries" meta box (latest 10)
     * -------------------------------------------------*/
    public function add_entries_metabox(): void
    {
        add_meta_box(
            'satori_form_entries_box',
            __('Entries', 'satori-forms'),
            [$this, 'render_entries_metabox'],
            'satori_form',
            'normal',
            'default'
        );
    }

    public function render_entries_metabox(\WP_Post $post): void
    {
        $entries = get_posts([
            'post_type'      => 'form_entry',
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'   => RegisterEntriesCPT::META_FORM_ID,
                    'value' => $post->ID,
                    'type'  => 'NUMERIC',
                    'compare' => '=',
                ]
            ],
        ]);

        $view_all_url = add_query_arg(
            ['post_type' => 'form_entry', RegisterEntriesCPT::META_FORM_ID => $post->ID],
            admin_url('edit.php')
        );

        echo '<p><a class="button button-secondary" href="' . esc_url($view_all_url) . '">' . esc_html__('View all entries for this form', 'satori-forms') . '</a></p>';

        if (empty($entries)) {
            echo '<p class="description">' . esc_html__('No entries yet for this form.', 'satori-forms') . '</p>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Entry', 'satori-forms') . '</th>';
        echo '<th>' . esc_html__('Date', 'satori-forms') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($entries as $entry) {
            $edit_link = get_edit_post_link($entry->ID);
            printf(
                '<tr><td><a href="%s">%s</a></td><td>%s</td></tr>',
                esc_url($edit_link),
                esc_html(get_the_title($entry) ?: ('#' . $entry->ID)),
                esc_html(get_the_time(get_option('date_format') . ' ' . get_option('time_format'), $entry))
            );
        }
        echo '</tbody></table>';
    }
}
