<?php

namespace Rahmentemplate;

class MetaBox
{

    public static function registerField(): void
    {
        add_action('add_meta_boxes', [self::class, 'add']);
        add_action('save_post', [self::class, 'save']);
    }
    public static function add(): void
    {
        add_meta_box('rahmentemplate_settings_page', 'Rahmentemplate', [self::class, 'html'], '', 'normal', 'high');
    }

    public static function save(int $postID): void
    {
        if (!isset($_POST['rahmentemplate_settings_page_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['rahmentemplate_settings_page_nonce'], 'rahmentemplate_settings_page_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $postID)) {
            return;
        }

        $templates = $_POST['rahmentemplate_settings_input_templates_field'] ?? [];
        update_post_meta($postID, 'rahmentemplate_settings_input_templates_field', $templates);
    }

    public static function html(): void
    {
        wp_nonce_field('rahmentemplate_settings_page_nonce', 'rahmentemplate_settings_page_nonce');

        $templates = get_option('rahmentemplate_settings_input_templates_field', []);
        $selected_template = get_post_meta(get_the_ID(), 'rahmentemplate_settings_input_templates_field', true);
        $default_template = get_option('rahmentemplate_settings_input_default_field', 'Template auswählen');

        self::markup($templates, $selected_template, $default_template);
    }

    public static function markup($templates, $selected_template, $default_template): void
    {
        ?>
        <div class="section templates">
            <div class="infobox">
                <p>Die Select Box bietet eine Liste von verfügbaren Templates, die für diesen Post verwendet werden können.</p>
            </div>
            <select name="rahmentemplate_settings_input_templates_field" class="selectDefault">
                <option value="none" <?php echo ($selected_template === 'none' || $default_template === 'none') ? 'selected' : '' ?>>Kein Template</option>
                <?php
                foreach ($templates as $template) {
                    if ($default_template === $template['ID'] && !$selected_template) {
                        $selected = true;
                    } elseif ($selected_template === $template['ID']) {
                        $selected = true;
                    } else {
                        $selected = false;
                    }
                    ?>
                    <option value="<?php echo esc_attr($template['ID'])?>"<?php if($selected) echo 'selected="selected"'; ?>><?php echo esc_html($template['title']); ?></option>
                <?php } ?>
            </select>
        </div>
        <style>
            .templates {
                padding-top: 0.5em;
                text-align: center;
            }
            .inside {
                border: 4px solid #1d2327;
                margin: 0 !important;
            }
            #rahmentemplate_settings_page .postbox-header {
                background: #1d2327;
                color: #fff !important;
            }
            .selectDefault {
                border: none;
                box-shadow: 0 6px 10px 0 rgba(0, 0, 0, 0.1) !important;
                text-align: center;
                transition: .3s;
                width: 30%;
            }
            .selectDefault:focus {
                box-shadow: 0 6px 10px 0 rgba(0, 0, 0, 0.4) !important;
                transition: .3s;
                border: none !important;
            }
            #rahmentemplate_settings_page .handle-actions button, #rahmentemplate_settings_page .handlediv .toggle-indicator::before {
                color: #fff !important;
            }
        </style>
        <?php
    }
}