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

        $templates = $_POST['rahmentemplate_settings_input_field'] ?? [];
        update_post_meta($postID, 'rahmentemplate_settings_input_field', $templates);
    }

    public static function html(): void
    {
        wp_nonce_field('rahmentemplate_settings_page_nonce', 'rahmentemplate_settings_page_nonce');

        $templates = get_option('rahmentemplate_settings_input_field', []);
        self::markup($templates);
    }

    public static function markup($templates): void
    {
        ?>
        <div class="test">
            <select name="rahmentemplate_settings_input_field">
                <?php foreach ($templates as $template) : ?>
                    <option value="<?php echo esc_attr($template['title']); ?>"><?php echo esc_html($template['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }
}