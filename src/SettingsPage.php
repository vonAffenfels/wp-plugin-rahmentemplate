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
            __( 'Version List Settings', 'rahmentemplate' ),
            __( 'Version List Settings', 'rahmentemplate' ),
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
            <form method="post">
                <input type="submit" name="btn-send-to-api" value="Send Information to API">
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
            'rahmentemplate Settings Page',
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
            __( 'ID', 'rahmentemplate' ),
            [$this,'rahmentemplate_settings_input_field_id_callback'],
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_section'
        );

        // Registe token input field
        register_setting(
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_input_field_token',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Register token input field
        register_setting(
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_input_field_token',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add token fields
        add_settings_field(
            'rahmentemplate_settings_input_field_token',
            __( 'Token', 'rahmentemplate' ),
            [$this,'rahmentemplate_settings_input_field_token_callback'],
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_section'
        );

        // Register url input field
        register_setting(
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_input_field_url',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add url fields
        add_settings_field(
            'rahmentemplate_settings_input_field_url',
            __( 'Api Url', 'rahmentemplate' ),
            [$this,'rahmentemplate_settings_input_field_url_callback'],
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_section'
        );
    }


    public function rahmentemplate_settings_input_field_token_callback() {
        $version_listinput_field = get_option('rahmentemplate_settings_input_field_token');
        ?>
        <input type="text" name="rahmentemplate_settings_input_field_token" class="regular-text" value="<?php echo isset($version_listinput_field) ? esc_attr( $version_listinput_field ) : ''; ?>" />
        <?php
    }


    public function rahmentemplate_settings_input_field_url_callback() {
        $version_listinput_field = get_option('rahmentemplate_settings_input_field_url');
        ?>
        <input type="text" name="rahmentemplate_settings_input_field_url" class="regular-text" value="<?php echo isset($version_listinput_field) ? esc_attr( $version_listinput_field ) : ''; ?>" />
        <?php
    }


    public function rahmentemplate_settings_input_field_id_callback() {
        $version_listinput_field = get_option('rahmentemplate_settings_input_field_id');
        ?>
        <input type="text" name="rahmentemplate_settings_input_field_id" class="regular-text" value="<?php echo isset($version_listinput_field) ? esc_attr( $version_listinput_field ) : ''; ?>" />
        <?php
    }
}
