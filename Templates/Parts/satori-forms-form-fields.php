<?php

/**
 * Admin partial: form fields list
 * -------------------------------------------------
 * Expects $fields array in scope.
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<?php if (empty($fields)) : ?>
    <div class="satori-forms-no-fields"><?php esc_html_e('No fields yet. Use the selector above to add one.', 'satori-forms'); ?></div>
<?php else : ?>
    <ul id="satori-forms-fields-sortable" class="satori-forms-fields-sortable">
        <?php foreach ($fields as $index => $field) :
            $field_id = 'field-' . $index;
            $type = esc_attr($field['type'] ?? 'text');
            $label = esc_attr($field['label'] ?? '');
            $name = esc_attr($field['name'] ?? '');
        ?>
            <li class="satori-form-field-item" data-index="<?php echo esc_attr($index); ?>">
                <div class="satori-form-field-handle">☰</div>
                <div class="satori-form-field-main">
                    <strong class="satori-form-field-label"><?php echo $label ? esc_html($label) : esc_html($type); ?></strong>
                    <span class="satori-form-field-type"><?php echo esc_html($type); ?></span>
                </div>

                <div class="satori-form-field-controls">
                    <button type="button" class="button satori-form-field-edit"><?php esc_html_e('Edit', 'satori-forms'); ?></button>
                    <button type="button" class="button satori-form-field-remove"><?php esc_html_e('Remove', 'satori-forms'); ?></button>
                </div>

                <div class="satori-form-field-settings" style="display:none;">
                    <p>
                        <label><?php esc_html_e('Label', 'satori-forms'); ?></label>
                        <input type="text" class="widefat satori-field-label" value="<?php echo $label; ?>">
                    </p>
                    <p>
                        <label><?php esc_html_e('Name (machine)', 'satori-forms'); ?></label>
                        <input type="text" class="widefat satori-field-name" value="<?php echo $name; ?>">
                    </p>
                    <p>
                        <label><input type="checkbox" class="satori-field-required" <?php checked(! empty($field['required']), 1); ?>> <?php esc_html_e('Required', 'satori-forms'); ?></label>
                    </p>

                    <?php if (in_array($field['type'] ?? '', ['select', 'radio', 'checkbox'], true)) : ?>
                        <p>
                            <label><?php esc_html_e('Options (one per line)', 'satori-forms'); ?></label>
                            <textarea class="widefat satori-field-options" rows="4"><?php echo esc_textarea(implode("\n", $field['options'] ?? [])); ?></textarea>
                        </p>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>