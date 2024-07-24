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
            __( 'Rahmentemplate Settings', 'rahmentemplate' ),
            __( 'Rahmentemplate Settings', 'rahmentemplate' ),
            'manage_options',
            'rahmentemplate-settings-page',
            [$this,'rahmentemplate_settings_template_callback'],
            '',
            null
        );

    }

    public function rahmentemplate_settings_template_callback() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form action="options.php" method="post">
                <?php
                // security field
                settings_fields( 'rahmentemplate-settings-page' );

                // output settings section here
                do_settings_sections('rahmentemplate-settings-page');

                // save settings button
                submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Settings Template
     */
    public function rahmentemplate_settings_init() {

        // Setup settings section
        add_settings_section(
            'rahmentemplate_settings_section',
            'Rahmentemplate Settings Page',
            '',
            'rahmentemplate-settings-page'
        );

        // Register ID input field
        register_setting(
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_input_field_id',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add ID fields
        add_settings_field(
            'rahmentemplate_settings_input_field_id',
            __( 'Templates', 'rahmentemplate' ),
            [$this,'rahmentemplate_settings_input_field_id_callback'],
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_section'
        );


    }


    public function rahmentemplate_settings_input_field_id_callback(): void
    {
        $postEditionTopTopics = get_option('rahmentemplate_settings_input_field_id');
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
            <?php if ($postEditionTopTopics) {
                foreach ($postEditionTopTopics as $key => $field) { ?>
                    <div class="repeatable-fieldset">
                        <input type="text" placeholder="Title" name="template[<?php echo $key ?>][title]" value="<?php echo $field['title'] ?? ''; ?>" />
                        <textarea placeholder="Template-URL" cols="55" rows="5" name="template[<?php echo $key ?>][templateUrl]"><?php echo $field['templateUrl'] ?? ''; ?></textarea>
                        <button class="remove-row button">Remove</button>
                    </div>
                <?php }
            } else { ?>
                <div class="repeatable-fieldset">
                    <input type="text" placeholder="Title" name="template[0][title]" value="" />
                    <textarea placeholder="Template-URL" cols="55" rows="5" name="template[0][templateUrl]"></textarea>
                    <button class="remove-row button">Remove</button>
                </div>
            <?php } ?>
            <!-- empty hidden one for jQuery -->
            <div class="empty-row" style="display: none">
                <input type="text" placeholder="Title" name="" />
                <textarea placeholder="Template-URL" cols="55" rows="5" name=""></textarea>
                <button class="remove-row button">Remove</button>
            </div>
        </div>
        <div class="repeatable-fieldset-actions">
            <button id="add-row" class="button">Add another</button>
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
                        $(this).find('input[type="text"]').attr('name', 'template[' + containerIndex + '][title]');
                        $(this).find('textarea').attr('name', 'template[' + containerIndex + '][templateUrl]');
                    });

                    return false;
                });

                $(document).on('click', '.remove-row', function() {
                    $(this).parents('.repeatable-fieldset').remove();

                    var containers = $('.repeatable-fieldset-container').find('.repeatable-fieldset');
                    containers.each(function(containerIndex) {
                        $(this).find('input[type="text"]').attr('name', 'template[' + containerIndex + '][title]');
                        $(this).find('textarea').attr('name', 'template[' + containerIndex + '][templateUrl]');
                    });

                    return false;
                });
            });
        </script>
        <?php
    }
}
