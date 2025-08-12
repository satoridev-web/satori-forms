/**
 * Admin JS for SATORI Forms builder
 * Basic add, edit, remove, sort and serialise to hidden input.
 *
 * Note: This is a Phase 2 minimal implementation. Expand UX as required.
 */

( function ( $ ) {
    'use strict';

    var fieldIndex = 0;

    function makeFieldItem( type, fieldData ) {
        var label = fieldData && fieldData.label ? fieldData.label : '';
        var name  = fieldData && fieldData.name ? fieldData.name : '';
        var required = fieldData && fieldData.required ? true : false;
        var options = fieldData && fieldData.options ? fieldData.options : [];

        var $li = $(
            '<li class="satori-form-field-item" data-index="' + fieldIndex + '">' +
                '<div class="satori-form-field-handle">☰</div>' +
                '<div class="satori-form-field-main"><strong class="satori-form-field-label">' + (label || type) + '</strong> <span class="satori-form-field-type">' + type + '</span></div>' +
                '<div class="satori-form-field-controls">' +
                    '<button type="button" class="button satori-form-field-edit">Edit</button>' +
                    '<button type="button" class="button satori-form-field-remove">Remove</button>' +
                '</div>' +
                '<div class="satori-form-field-settings" style="display:none;">' +
                    '<p><label>Label</label><input type="text" class="widefat satori-field-label" value="' + label + '"></p>' +
                    '<p><label>Name (machine)</label><input type="text" class="widefat satori-field-name" value="' + name + '"></p>' +
                    '<p><label><input type="checkbox" class="satori-field-required" ' + (required ? 'checked' : '') + '> Required</label></p>' +
                '</div>' +
            '</li>'
        );

        // If options needed, add textarea
        if ( ['select','radio','checkbox'].indexOf(type) !== -1 ) {
            var opts = options.join("\n");
            $li.find('.satori-form-field-settings').append(
                '<p><label>Options (one per line)</label><textarea class="widefat satori-field-options" rows="4">'+opts+'</textarea></p>'
            );
        }

        fieldIndex++;
        return $li;
    }

    function serialiseFields() {
        var data = {
            fields: []
        };

        $('#satori-forms-fields-sortable .satori-form-field-item').each(function () {
            var $item = $(this);
            var type = $item.find('.satori-form-field-type').text();
            var label = $item.find('.satori-field-label').val() || $item.find('.satori-form-field-label').text();
            var name  = $item.find('.satori-field-name').val() || label.toLowerCase().replace(/\s+/g, '-');
            var required = $item.find('.satori-field-required').is(':checked') ? 1 : 0;
            var fieldObj = {
                type: type,
                label: label,
                name: name,
                required: required
            };

            var opts = $item.find('.satori-field-options');
            if ( opts.length ) {
                var raw = opts.val() || '';
                var arr = raw.split(/\r?\n/).map(function (s) { return s.trim(); }).filter(Boolean);
                fieldObj.options = arr;
            }

            data.fields.push(fieldObj);
        });

        $('#satori-forms-config').val(JSON.stringify(data));
    }

    // DOM ready
    $( function () {
        // initialise index from existing items
        fieldIndex = $('#satori-forms-fields-sortable .satori-form-field-item').length;

        // sortable
        $('#satori-forms-fields-sortable').sortable({
            handle: '.satori-form-field-handle',
            placeholder: 'satori-field-placeholder',
            update: function () {
                serialiseFields();
            }
        });

        // add field
        $('#satori-forms-add-field').on('click', function () {
            var type = $('#satori-forms-field-type').val();
            var $item = makeFieldItem(type, {});
            $('#satori-forms-fields-sortable').append($item);
            serialiseFields();
        });

        // remove
        $(document).on('click', '.satori-form-field-remove', function () {
            $(this).closest('.satori-form-field-item').remove();
            serialiseFields();
        });

        // edit toggle
        $(document).on('click', '.satori-form-field-edit', function () {
            var $settings = $(this).closest('.satori-form-field-item').find('.satori-form-field-settings');
            $settings.toggle();
        });

        // on change of any input inside settings, serialise
        $(document).on('input change', '.satori-form-field-settings input, .satori-form-field-settings textarea', function () {
            // update the visible label
            var $item = $(this).closest('.satori-form-field-item');
            var lbl = $item.find('.satori-field-label').val();
            if (lbl) {
                $item.find('.satori-form-field-label').text(lbl);
            }

            serialiseFields();
        });

        // when the post is saved via WP autosave or publish, ensure serialise
        $('#post').on('submit', function () {
            serialiseFields();
        });

        // Initial serialise (in case existing config present)
        serialiseFields();
    } );

})( jQuery );
