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

namespace Satori\Forms\Forms;

defined('ABSPATH') || exit;

/* -------------------------------------------------
 * Field rendering helpers
 * -------------------------------------------------*/
class FormFields
{

    /**
     * Render a field on the frontend based on $field config array
     *
     * @param array $field
     * @return string
     */
    public static function render_field($field)
    {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? '';
        $required = ! empty($field['required']) ? 'required' : '';
        $html = '';

        $attr_name = esc_attr($name);
        $attr_label = esc_html($label);

        switch ($type) {
            case 'textarea':
                $html .= "<label for=\"{$attr_name}\">{$attr_label}</label>";
                $html .= "<textarea name=\"{$attr_name}\" id=\"{$attr_name}\" {$required}></textarea>";
                break;

            case 'select':
                $html .= "<label for=\"{$attr_name}\">{$attr_label}</label>";
                $html .= "<select name=\"{$attr_name}\" id=\"{$attr_name}\" {$required}>";
                foreach ($field['options'] ?? [] as $opt) {
                    $opt_esc = esc_attr($opt);
                    $html .= "<option value=\"{$opt_esc}\">" . esc_html($opt) . "</option>";
                }
                $html .= "</select>";
                break;

            case 'checkbox':
                $html .= "<label><input type=\"checkbox\" name=\"{$attr_name}\" id=\"{$attr_name}\" value=\"1\"> {$attr_label}</label>";
                break;

            case 'radio':
                $html .= "<fieldset><legend>{$attr_label}</legend>";
                foreach ($field['options'] ?? [] as $opt) {
                    $opt_val = esc_attr($opt);
                    $html .= "<label><input type=\"radio\" name=\"{$attr_name}\" value=\"{$opt_val}\"> " . esc_html($opt) . "</label><br/>";
                }
                $html .= "</fieldset>";
                break;

            default:
                // text, email, number, date
                $input_type = esc_attr($type);
                $html .= "<label for=\"{$attr_name}\">{$attr_label}</label>";
                $html .= "<input type=\"{$input_type}\" name=\"{$attr_name}\" id=\"{$attr_name}\" {$required} />";
                break;
        }

        return $html;
    }
}
