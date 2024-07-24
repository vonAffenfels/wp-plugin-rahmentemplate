<?php

namespace Rahmentemplate;

class SettingsPage
{
    public function initSettingsPage()
    {
        add_action('admin_menu', [$this,'rahmentemplate_settings_menu']);
        add_action('admin_init', [$this,'rahmentemplate_settings_init'] );
    }

    public function rahmentemplate_settings_menu() {

        add_menu_page(
            __( 'Rahmentemplates', 'rahmentemplate' ),
            __( 'Rahmentemplates', 'rahmentemplate' ),
            'manage_options',
            'rahmentemplate-settings-page',
            [$this,'rahmentemplate_settings_page'],
            'dashicons-layout',
            null
        );

    }

    /**
     * Settings Template
     */
    public function rahmentemplate_settings_init() {

        // Setup settings section
        add_settings_section(
            'rahmentemplate_settings_section',
            'Rahmentemplates',
            '',
            'rahmentemplate-settings-page'
        );

        // Register ID input field
        register_setting(
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_input_field',
            array(
                'type' => 'array',
                'sanitize_callback' => [$this, 'rahmentemplate_sanitize'],
                'default' => array()
            )
        );

        // Add ID fields
        add_settings_field(
            'rahmentemplate_settings_input_field',
            __( 'Templates', 'rahmentemplate' ),
            [$this,'rahmentemplate_settings_input_field_callback'],
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_section'
        );


    }

    public function rahmentemplate_sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key]['title'] = sanitize_text_field($value['title']);
                $input[$key]['url'] = sanitize_text_field($value['url']);
            }
        }
        return $input;
    }

    public function rahmentemplate_settings_input_field_callback(): void
    {
        $templates = get_option('rahmentemplate_settings_input_field', array());

        ?>
        <style>
            .repeatable-fieldset-container {
                display: flex;
                flex-direction: column;
            }
            .repeatable-fieldset {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .repeatable-fieldset select {
                flex: 1;
            }
            .repeatable-fieldset-actions {
                margin-top: 10px;
            }
            .repeatable-fieldset-actions button {
                margin-right: 10px;
            }
        </style>
        <div class="repeatable-fieldset-container">
            <?php if ($templates) {
                foreach ($templates as $key => $field) { ?>
                    <div class="repeatable-fieldset">
                        <input class="inputTitle" type="text" placeholder="Titel" name="rahmentemplate_settings_input_field[<?php echo $key ?>][title]" value="<?php echo $field['title'] ?? ''; ?>" />
                        <input class="inputURL" type="text" placeholder="URL" name="rahmentemplate_settings_input_field[<?php echo $key ?>][url]" value="<?php echo $field['url'] ?? ''; ?>" />
                        <button class="remove-row button">Löschen</button>
                    </div>
                <?php }
            } else { ?>
                <div class="repeatable-fieldset">
                    <input class="inputTitle" type="text" placeholder="Titel" name="rahmentemplate_settings_input_field[0][title]" value="" />
                    <input class="inputURL" type="text" placeholder="URL" name="rahmentemplate_settings_input_field[0][url]" value="" />
                    <button class="remove-row button">Löschen</button>
                </div>
            <?php } ?>
            <!-- empty hidden one for jQuery -->
            <div class="empty-row" style="display: none">
                <input class="inputTitle" type="text" placeholder="Titel" name="" />
                <input class="inputURL" type="text" placeholder="URL" name="" />
                <button class="remove-row button">Löschen</button>
            </div>
        </div>
        <div class="repeatable-fieldset-actions">
            <button id="add-row" class="button">Hinzufügen</button>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#add-row').on('click', function() {
                    var row = $('.empty-row').clone(true);
                    row.removeClass('empty-row');
                    row.css('display', 'flex')
                    row.addClass('repeatable-fieldset');
                    $('.repeatable-fieldset-container').append(row);

                    var containers = $('.repeatable-fieldset-container').find('.repeatable-fieldset');
                    containers.each(function(containerIndex) {
                        $(this).find('.inputTitle').attr('name', 'rahmentemplate_settings_input_field[' + containerIndex + '][title]');
                        $(this).find('.inputURL').attr('name', 'rahmentemplate_settings_input_field[' + containerIndex + '][url]');
                    });

                    return false;
                });

                $(document).on('click', '.remove-row', function() {
                    $(this).parents('.repeatable-fieldset').remove();

                    var containers = $('.repeatable-fieldset-container').find('.repeatable-fieldset');
                    containers.each(function(containerIndex) {
                        $(this).find('.inputTitle').attr('name', 'rahmentemplate_settings_input_field[' + containerIndex + '][title]');
                        $(this).find('.inputURL').attr('name', 'rahmentemplate_settings_input_field[' + containerIndex + '][url]');
                    });

                    return false;
                });
            });
        </script>
        <?php
    }

    public function rahmentemplate_settings_page() {
        ?>
        <div class="wrap">
            <h2>Rahmentemplate Settings</h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('rahmentemplate-settings-page');
                do_settings_sections('rahmentemplate-settings-page');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}


