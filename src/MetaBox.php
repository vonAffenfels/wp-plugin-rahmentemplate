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
        add_meta_box('rahmentemplate_settings_page', 'Rahmentemplate', [self::class, 'html'], 'page', 'normal', 'high');
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
            <select name="rahmentemplate_settings_input_templates_field">
                <? if (!$selected_template && !$default_template) : ?>
                    <option value="" selected>Template auswählen</option>
                <? endif; ?>


                <?php foreach ($templates as $template) {
                    $selected = ($selected_template == $template['title'] || $default_template == $template['title']) ?? '';
                    ?>
                    <option value="<?php echo esc_attr($template['title'])?>"<?php if($selected) echo 'selected="selected"'; ?>><?php echo esc_html($template['title']); ?></option>
                <?php } ?>
            </select>
        </div>
        <?php
    }
}