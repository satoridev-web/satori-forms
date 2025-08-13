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

namespace Satori\Forms\PostTypes;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * CPT: form_entry (entries linked to satori_form via meta)
 * -------------------------------------------------*/
class RegisterEntriesCPT
{
    public const META_FORM_ID    = 'satori_form_id';
    public const META_USER_ID    = '_satori_entry_user_id';
    public const META_IP         = '_satori_entry_ip';
    public const META_USER_AGENT = '_satori_entry_user_agent';

    // Admin filter keys
    private const QP_FORM_ID  = 'satori_form_id';
    private const QP_USER_ID  = '_satori_entry_user_id';
    private const QP_IP_LIKE  = '_satori_entry_ip_like';

    /** @var bool per-request guard so register() only runs once */
    private static bool $did_register = false;

    public function __construct()
    {
        add_action('init', [$this, 'register']);
        add_action('init', [$this, 'register_meta']);

        add_action('add_meta_boxes_form_entry', [$this, 'add_entry_metabox']);
        add_action('save_post_form_entry', [$this, 'save_entry_meta'], 10, 2);

        add_filter('manage_form_entry_posts_columns', [$this, 'columns']);
        add_action('manage_form_entry_posts_custom_column', [$this, 'column_content'], 10, 2);

        add_action('restrict_manage_posts', [$this, 'entries_filter_controls']);
        add_action('pre_get_posts', [$this, 'apply_admin_filters']);
    }

    public function register(): void
    {
        if (self::$did_register) {
            return; // guard against double-run in same request
        }
        self::$did_register = true;

        $labels = [
            'name'               => __('Entries', 'satori-forms'),
            'singular_name'      => __('Entry', 'satori-forms'),
            'menu_name'          => __('Entries', 'satori-forms'),
            'name_admin_bar'     => __('Entry', 'satori-forms'),
            'add_new'            => __('Add New', 'satori-forms'),
            'add_new_item'       => __('Add New Entry', 'satori-forms'),
            'edit_item'          => __('Edit Entry', 'satori-forms'),
            'new_item'           => __('New Entry', 'satori-forms'),
            'view_item'          => __('View Entry', 'satori-forms'),
            'search_items'       => __('Search Entries', 'satori-forms'),
            'not_found'          => __('No entries found.', 'satori-forms'),
            'not_found_in_trash' => __('No entries found in Trash.', 'satori-forms'),
            'all_items'          => __('Entries', 'satori-forms'),
        ];

        $args = [
            'labels'            => $labels,
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => 'satori-forms',
            'show_in_admin_bar' => false,
            'hierarchical'      => false,
            'supports'          => ['title'],
            'capability_type'   => 'post',
            'map_meta_cap'      => true,
        ];

        register_post_type('form_entry', $args);

        if (function_exists('satori_forms_log')) {
            $hook = function_exists('current_action') ? (string) current_action() : 'unknown';
            satori_forms_log(
                sprintf('Registered CPT: %s (show_in_menu => %s) via %s', 'form_entry', 'satori-forms', $hook)
            );
        }
    }

    public function register_meta(): void
    {
        register_post_meta('form_entry', self::META_FORM_ID, [
            'type'              => 'integer',
            'single'            => true,
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'show_in_rest'      => true,
            'auth_callback'     => fn() => current_user_can('edit_posts'),
        ]);

        register_post_meta('form_entry', self::META_USER_ID, [
            'type'              => 'integer',
            'single'            => true,
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'show_in_rest'      => true,
            'auth_callback'     => fn() => current_user_can('edit_posts'),
        ]);

        register_post_meta('form_entry', self::META_IP, [
            'type'              => 'string',
            'single'            => true,
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest'      => true,
            'auth_callback'     => fn() => current_user_can('edit_posts'),
        ]);

        register_post_meta('form_entry', self::META_USER_AGENT, [
            'type'              => 'string',
            'single'            => true,
            'default'           => '',
            'sanitize_callback' => 'sanitize_textarea_field',
            'show_in_rest'      => true,
            'auth_callback'     => fn() => current_user_can('edit_posts'),
        ]);
    }

    /* ---------------- Entry edit screen: Form selector ---------------- */

    public function add_entry_metabox(): void
    {
        add_meta_box(
            'satori_form_entry_parent',
            __('Form', 'satori-forms'),
            [$this, 'render_entry_metabox'],
            'form_entry',
            'side',
            'high'
        );
    }

    public function render_entry_metabox(\WP_Post $post): void
    {
        $current = (int) get_post_meta($post->ID, self::META_FORM_ID, true);
        wp_nonce_field('satori_entry_form_meta', 'satori_entry_form_meta_nonce');

        $forms = get_posts([
            'post_type'      => 'satori_form',
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ]);

        echo '<label class="screen-reader-text" for="satori_form_id">' . esc_html__('Parent Form', 'satori-forms') . '</label>';
        echo '<select name="satori_form_id" id="satori_form_id" class="widefat">';
        echo '<option value="0">' . esc_html__('— Select a form —', 'satori-forms') . '</option>';

        foreach ($forms as $form_id) {
            $title = get_the_title($form_id);
            printf(
                '<option value="%1$d"%2$s>%3$s</option>',
                (int) $form_id,
                selected($current, (int) $form_id, false),
                esc_html($title ?: sprintf(__('(no title) #%d', 'satori-forms'), $form_id))
            );
        }
        echo '</select>';

        // Read-only meta preview
        $user_id = (int) get_post_meta($post->ID, self::META_USER_ID, true);
        $ip      = (string) get_post_meta($post->ID, self::META_IP, true);
        $ua      = (string) get_post_meta($post->ID, self::META_USER_AGENT, true);

        echo '<hr/><p><strong>' . esc_html__('Captured Meta', 'satori-forms') . '</strong></p>';
        echo '<p>' . esc_html__('User ID:', 'satori-forms') . ' ' . esc_html($user_id ?: 0) . '</p>';
        echo '<p>' . esc_html__('IP:', 'satori-forms') . ' ' . esc_html($ip) . '</p>';
        echo '<p>' . esc_html__('User Agent:', 'satori-forms') . '</p>';
        echo '<textarea class="widefat" rows="3" readonly>' . esc_textarea($ua) . '</textarea>';
    }

    public function save_entry_meta(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST['satori_entry_form_meta_nonce']) || !wp_verify_nonce($_POST['satori_entry_form_meta_nonce'], 'satori_entry_form_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $form_id = isset($_POST['satori_form_id']) ? absint($_POST['satori_form_id']) : 0;
        update_post_meta($post_id, self::META_FORM_ID, $form_id);
    }

    /* ---------------- List table columns ---------------- */

    public function columns(array $cols): array
    {
        $new = [];
        foreach ($cols as $key => $label) {
            $new[$key] = $label;
            if ('title' === $key) {
                $new['satori_parent_form'] = __('Form', 'satori-forms');
                $new['satori_entry_ip']    = __('IP', 'satori-forms');
                $new['satori_entry_uid']   = __('User ID', 'satori-forms');
            }
        }
        return $new;
    }

    public function column_content(string $col, int $post_id): void
    {
        if ('satori_parent_form' === $col) {
            $form_id = (int) get_post_meta($post_id, self::META_FORM_ID, true);
            if ($form_id > 0) {
                $link = admin_url('post.php?post=' . $form_id . '&action=edit');
                echo '<a href="' . esc_url($link) . '">' . esc_html(get_the_title($form_id) ?: ('#' . $form_id)) . '</a>';
            } else {
                echo '<span class="description">' . esc_html__('(none)', 'satori-forms') . '</span>';
            }
        } elseif ('satori_entry_ip' === $col) {
            echo esc_html((string) get_post_meta($post_id, self::META_IP, true));
        } elseif ('satori_entry_uid' === $col) {
            echo esc_html((int) get_post_meta($post_id, self::META_USER_ID, true) ?: 0);
        }
    }

    /* ---------------- Admin filters (Form, User ID, IP) ---------------- */

    public function entries_filter_controls(string $post_type): void
    {
        global $typenow;
        if ($typenow !== 'form_entry') {
            return;
        }

        // Form dropdown
        $selected_form = isset($_GET[self::QP_FORM_ID]) ? absint($_GET[self::QP_FORM_ID]) : 0;
        $forms = get_posts([
            'post_type'      => 'satori_form',
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ]);

        echo '<label for="filter-by-satori-form" class="screen-reader-text">' . esc_html__('Filter by form', 'satori-forms') . '</label>';
        echo '<select name="' . esc_attr(self::QP_FORM_ID) . '" id="filter-by-satori-form" class="postform">';
        echo '<option value="0">' . esc_html__('All forms', 'satori-forms') . '</option>';
        foreach ($forms as $form_id) {
            printf(
                '<option value="%1$d"%2$s>%3$s</option>',
                (int) $form_id,
                selected($selected_form, (int) $form_id, false),
                esc_html(get_the_title($form_id) ?: sprintf(__('(no title) #%d', 'satori-forms'), $form_id))
            );
        }
        echo '</select>';

        // User ID number field
        $uid_val = isset($_GET[self::QP_USER_ID]) ? (string) $_GET[self::QP_USER_ID] : '';
        echo '&nbsp;<label class="screen-reader-text" for="satori-filter-userid">' . esc_html__('Filter by User ID', 'satori-forms') . '</label>';
        echo '<input type="number" min="0" step="1" name="' . esc_attr(self::QP_USER_ID) . '" id="satori-filter-userid" value="' . esc_attr($uid_val) . '" placeholder="' . esc_attr__('User ID', 'satori-forms') . '" />';

        // IP text field (supports partial match)
        $ip_like = isset($_GET[self::QP_IP_LIKE]) ? (string) $_GET[self::QP_IP_LIKE] : '';
        echo '&nbsp;<label class="screen-reader-text" for="satori-filter-ip">' . esc_html__('Filter by IP', 'satori-forms') . '</label>';
        echo '<input type="text" name="' . esc_attr(self::QP_IP_LIKE) . '" id="satori-filter-ip" value="' . esc_attr($ip_like) . '" placeholder="' . esc_attr__('IP contains…', 'satori-forms') . '" />';
    }

    public function apply_admin_filters(\WP_Query $q): void
    {
        if (!is_admin() || !$q->is_main_query() || $q->get('post_type') !== 'form_entry') {
            return;
        }

        $meta_query = (array) $q->get('meta_query');

        // Filter by Form
        $form_id = isset($_GET[self::QP_FORM_ID]) ? absint($_GET[self::QP_FORM_ID]) : 0;
        if ($form_id > 0) {
            $meta_query[] = [
                'key'     => self::META_FORM_ID,
                'value'   => $form_id,
                'type'    => 'NUMERIC',
                'compare' => '=',
            ];
        }

        // Filter by User ID
        if (isset($_GET[self::QP_USER_ID]) && $_GET[self::QP_USER_ID] !== '') {
            $uid = absint($_GET[self::QP_USER_ID]);
            $meta_query[] = [
                'key'     => self::META_USER_ID,
                'value'   => $uid,
                'type'    => 'NUMERIC',
                'compare' => '=',
            ];
        }

        // Filter by IP (partial match)
        if (isset($_GET[self::QP_IP_LIKE]) && $_GET[self::QP_IP_LIKE] !== '') {
            $needle = sanitize_text_field((string) $_GET[self::QP_IP_LIKE]);
            $meta_query[] = [
                'key'     => self::META_IP,
                'value'   => $needle,
                'compare' => 'LIKE',
            ];
        }

        if (!empty($meta_query)) {
            $q->set('meta_query', $meta_query);
        }
    }
}
