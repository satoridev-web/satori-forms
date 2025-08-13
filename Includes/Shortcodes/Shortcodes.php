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

namespace Satori\Forms\Shortcodes;

use Satori\Forms\Render\Renderer;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Shortcodes
 * -------------------------------------------------*/
class Shortcodes
{
    public function __construct()
    {
        add_shortcode('satori_form', [$this, 'satori_form']);
    }

    public function satori_form($atts = []): string
    {
        $atts = shortcode_atts([
            'id'   => 0,
            'slug' => '',
        ], $atts, 'satori_form');

        $form_id = absint($atts['id']);
        if (!$form_id && $atts['slug'] !== '') {
            $post = get_page_by_path(sanitize_title($atts['slug']), OBJECT, 'satori_form');
            if ($post) {
                $form_id = (int) $post->ID;
            }
        }
        if (!$form_id) {
            return '<div class="satori-form-error">' . esc_html__('Form not specified.', 'satori-forms') . '</div>';
        }

        return Renderer::render_form($form_id);
    }
}
