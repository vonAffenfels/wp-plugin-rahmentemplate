<?php

namespace Rahmentemplate;

use WP_Query;

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
            'rahmentemplate_settings_input_templates_field',
            array(
                'type' => 'array',
                'sanitize_callback' => [$this, 'rahmentemplate_sanitize'],
                'default' => array()
            )
        );

        // Add ID fields
        add_settings_field(
            'rahmentemplate_settings_input_templates_field',
            __( 'Templates', 'rahmentemplate' ),
            [$this,'rahmentemplate_settings_input_templates_field_callback'],
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_section'
        );

        // Register default field
        register_setting(
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_input_default_field',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );

        // Add select field
        add_settings_field(
            'rahmentemplate_settings_input_default_field',
            __( 'Standard Template', 'rahmentemplate' ),
            [$this,'rahmentemplate_settings_input_default_field_callback'],
            'rahmentemplate-settings-page',
            'rahmentemplate_settings_section'
        );
    }

    public function rahmentemplate_sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key]['default'] = sanitize_text_field($value['default']);
                $input[$key]['title'] = sanitize_text_field($value['title']);
                $input[$key]['url'] = sanitize_text_field($value['url']);
            }
        }
        return $input;
    }

    public function rahmentemplate_settings_input_default_field_callback(): void
    {
        $selected_template = get_option('rahmentemplate_settings_input_default_field');
        $templates = get_option('rahmentemplate_settings_input_templates_field', array());
       ?>
         <select name="rahmentemplate_settings_input_default_field">
                <option value="">Template auswählen</option>
                <?php foreach ($templates as $template) {
                    $selected = $selected_template == $template['title'] ?? '';
                    ?>
                    <option value="<?php echo esc_attr($template['title'])?>"<?php if($selected) echo 'selected="selected"'; ?>><?php echo esc_html($template['title']); ?></option>
                <?php } ?>
        </select>
        <?php
    }

    public function rahmentemplate_settings_input_templates_field_callback(): void
    {
        $pages = get_pages();
        $templates = get_option('rahmentemplate_settings_input_templates_field', array());
        $counted_templates = $this->count_templates($pages);

        self::markup($templates, $counted_templates);
        self::css();
        self::js();
    }

    public function markup($templates, $counted_templates) {
        ?>
        <div class="repeatable-fieldset-container">
            <?php if ($templates) {
                foreach ($templates as $key => $field) {
                    $this->addFieldset($key, $field, $counted_templates);
                 }
            } else {
                $this->addEmptyFieldset();
            }
            $this->addHiddenFieldset();
            ?>
        </div>
        <div class="repeatable-fieldset-actions">
            <button id="add-row" class="button add-row">Hinzufügen</button>
        </div>
        <?php
    }

    public function css() {
        ?>
        <style>
            .repeatable-fieldset-container {
                display: flex;
                flex-direction: column;
            }
            .repeatable-fieldset {
                padding-bottom: 1em;
                padding-top: 1em;
                border-top: 1px solid black;
            }
            .input-group {
                display: flex;
                align-items: center;
                justify-content: space-between;
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
            button {
                width: 100px;
            }
            .remove-row {
                border-color: #dc3232 !important;
                color: #dc3232 !important;
            }
            .add-row {
                border-color: #46b450 !important;
                color: #46b450 !important;
            }
            .details {
                height: 0;
                opacity: 0;
                transition: all 0.3s ease-in-out;
                display: none;
            }
            .details.open {
                height: 100%;
                opacity: 1;
                transition: all 0.3s ease-in-out;
                display: block;
            }
            .detail-group {
                display: flex;
            }
            .detail-page {
                margin-right: 1em;
                padding: 0.3em 1em;
                border: 1px solid #3582c4;
                border-radius: 5px;
                text-decoration: none;
                background: #f6f7f7;
                color: #0a4b78;
            }
            .detail-page:hover {
                background: #f0f0f1;
                border-color: #0a4b78;
                color: #0a4b78;
            }

        </style>
        <?php
    }

    public function js() {
        ?>
        <script>
            jQuery(document).ready(function($) {
                $('#add-row').on('click', function() {
                    var row = $('.empty-row').clone(true);
                    row.removeClass('empty-row');
                    row.css('display', 'block')
                    row.addClass('repeatable-fieldset');
                    $('.repeatable-fieldset-container').append(row);

                    var containers = $('.repeatable-fieldset-container').find('.repeatable-fieldset');
                    containers.each(function(containerIndex) {
                        $(this).find('.inputTitle').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][title]');
                        $(this).find('.inputURL').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][url]');
                    });

                    return false;
                });


                $(document).on('click', '.open-details', function() {
                    var containers = $(this).parents('.repeatable-fieldset').find('.details');
                    containers.each(function(containerIndex) {
                        $(this).toggleClass('open');
                    });
                    return false;
                });

                $(document).on('click', '.remove-row', function() {
                    $(this).parents('.repeatable-fieldset').remove();

                    var containers = $('.repeatable-fieldset-container').find('.repeatable-fieldset');
                    containers.each(function(containerIndex) {
                        $(this).find('.inputTitle').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][title]');
                        $(this).find('.inputURL').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][url]');
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

    private function count_templates($pages)
    {
        foreach ($pages as $key => $page) {
            $selected_templates[$key] = get_post_meta($page->ID, 'rahmentemplate_settings_input_templates_field', true);
        }
        return array_count_values($selected_templates);
    }

    private function addHiddenFieldset()
    {
        ?>
            <!-- empty hidden one for jQuery -->
            <div class="empty-row" style="display: none">
                <div class="input-group">
                    <input class="inputTitle" type="text" placeholder="Titel" name="" />
                    <input class="inputURL" type="text" placeholder="URL" name="" />
                    <input class="countedTemplates" type="text" disabled placeholder="nicht in Benutzung" value="">
                    <button class="remove-row button">Löschen</button>
                </div>
                <div class="details">
                </div>
            </div>
        <?php
    }

    private function addEmptyFieldset()
    {
        ?>
        <div class="empty-row repeatable-fieldset">
            <div class="input-group">
                <input class="inputTitle" type="text" placeholder="Titel" name="rahmentemplate_settings_input_templates_field[0][title]" />
                <input class="inputURL" type="text" placeholder="URL" name="rahmentemplate_settings_input_templates_field[0][url]" />
                <input class="countedTemplates" type="text" disabled placeholder="nicht in Benutzung" value="">
                <button class="remove-row button">Löschen</button>
            </div>
            <div class="details">
            </div>
        </div>
        <?php
    }

    private function addFieldset(int|string $key, mixed $field, $counted_templates)
    {
        ?>
            <div class="repeatable-fieldset">
                <div class="input-group">
                    <input class="inputTitle" type="text" placeholder="Titel" name="rahmentemplate_settings_input_templates_field[<?php echo $key ?>][title]" value="<?php echo $field['title'] ?? ''; ?>" />
                    <input class="inputURL" type="text" placeholder="URL" name="rahmentemplate_settings_input_templates_field[<?php echo $key ?>][url]" value="<?php echo $field['url'] ?? ''; ?>" />
                    <input class="countedTemplates" type="text" disabled placeholder="nicht in Benutzung" value="<?php echo (array_key_exists($field['title'], $counted_templates) && $field['title']) ? $counted_templates[$field['title']] . ' mal in Benutzung' : 'nicht in Benutzung'  ?>" />
                    <?php if (!array_key_exists($field['title'], $counted_templates) || !$field['title'] ) { ?>
                        <button id="remove-row" class="remove-row button">Löschen</button>
                    <?php } else {
                        ?>
                        <button id="open-details" class="button open-details">Details</button>
                    <?php } ?>
                </div>
                <div class="details">
                    <h3>Seiten: </h3>
                    <div class="detail-group">
                        <?php $this->getDetails($counted_templates, $field); ?>
                    </div>
                </div>
            </div>
        <?php
    }

    private function getDetails($counted_templates, $field): void
    {
        if (array_key_exists($field['title'], $counted_templates) && $field['title']) {
            $args = array(
                'post_type' => 'page',
                'meta_query' => array(
                    array(
                        'key' => 'rahmentemplate_settings_input_templates_field',
                        'value' => $field['title'],
                        'compare' => 'LIKE'
                    )
                )
            );
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<a class="detail-page" target="_blank" href="' . admin_url( 'post.php?post=' . get_the_ID() ) . '&action=edit' . '">' . get_the_title() . '</a><br>';
                }
            }
        }
    }
}
