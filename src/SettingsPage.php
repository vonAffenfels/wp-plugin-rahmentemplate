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
    }

    public function rahmentemplate_sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key]['default'] = sanitize_text_field($value['default']);
                $input[$key]['title'] = sanitize_text_field($value['title']);
                $input[$key]['url'] = sanitize_text_field($value['url']);
                $input[$key]['replace'] = sanitize_text_field($value['replace']);
                $input[$key]['ID'] = sanitize_text_field($value['ID']);
            }
        }
        return $input;
    }

    public function rahmentemplate_settings_input_default_field_callback(): void
    {
        $selected_template = get_option('rahmentemplate_settings_input_default_field');
        $templates = get_option('rahmentemplate_settings_input_templates_field', array());
       ?>
        <div class="infobox">
            <p>Die Select Box bietet eine Liste von verfügbaren Templates, die für Posts verwendet werden können. Das ausgewählte Template
            dient als Standard, wenn Posts kein anderes der aufgelisteten Templates hinterlegt haben.</p>
        </div>
         <select name="rahmentemplate_settings_input_default_field" class="selectDefault">
                <option value="">Template auswählen</option>
                <?php foreach ($templates as $template) {
                    $selected = $selected_template == $template['ID'] ?? '';
                    ?>
                    <option value="<?php echo $template['ID']?>"<?php if($selected) echo 'selected="selected"'; ?>><?php echo esc_html($template['title']); ?></option>
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
        <div class="infobox">
            <p>Templates die in der oberen Select Box als Standard, oder im jeweiligen Post verwendet werden können.</p><br>
            <li>Templates in Benutzung können nicht gelöscht werden.</li>
            <li>Unter "Details" werden Posts aufgelistet, die das entsprechende Template in Benutzung haben.</li>
            <li>Unter "Details" kann der entsprechende Cache markiert und anschließend beim Speichervorgang geleert werden.</li>
        </div>
        <div class="cacheInfo">
            <p class="cacheInfoText"></p>
        </div>
        <div class="headings">
            <span><b>Name</b></span>
            <span><b>URL</b></span>
            <span><b>Zu ersetzender Text</b></span>
            <span><b>Benutzung</b></span>
            <span><b>Bearbeiten</b></span>
        </div>
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
            <button id="addRow" class="button addRow">Hinzufügen</button><br><br>
            <button id="clearAllCaches" class="button red">Markierte Caches leeren</button>
        </div>
        <?php
    }

    private function count_templates($pages)
    {
        $selected_templates = [];
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
                    <input class="inputID" hidden type="text" placeholder="ID" name="" value="<?php echo uniqid() ?>"/>
                    <input class="inputTitle" type="text" placeholder="Titel" name="" />
                    <input class="inputURL" type="text" placeholder="URL" name="" />
                    <input class="inputReplace" type="text" placeholder="Zu ersetzender Text" name="" />
                    <input class="countedTemplates" type="text" disabled placeholder="nicht in Benutzung" value="">
                    <button class="button removeRow">Löschen</button>
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
                <input class="inputID" hidden type="text" name="rahmentemplate_settings_input_templates_field[0][ID]" value="" />
                <input class="inputTitle" type="text" placeholder="Titel" name="rahmentemplate_settings_input_templates_field[0][title]" />
                <input class="inputURL" type="text" placeholder="URL" name="rahmentemplate_settings_input_templates_field[0][url]" />
                <input class="inputReplace" type="text" placeholder="Zu ersetzender Text" name="rahmentemplate_settings_input_templates_field[0][replace]" />
                <input class="countedTemplates" type="text" disabled placeholder="nicht in Benutzung" value="">
                <button class="removeRow button">Löschen</button>
            </div>
            <div class="details">
            </div>
        </div>
        <?php
    }

    private function addFieldset(int|string $key, mixed $field, $counted_templates)
    {
        $idExist = array_key_exists($field['ID'], $counted_templates) && $field['ID'];
        if (empty($field['ID'])) {
            $field['ID'] = uniqid();
        }
        ?>
            <div class="repeatable-fieldset">
                <div class="input-group">
                    <input class="inputID" hidden type="text" name="rahmentemplate_settings_input_templates_field[<?php echo $key ?>][ID]" value="<?php echo $field['ID'] ?>" />
                    <input class="inputTitle" type="text" placeholder="Titel" name="rahmentemplate_settings_input_templates_field[<?php echo $key ?>][title]" value="<?php echo $field['title'] ?? ''; ?>" />
                    <input class="inputURL" type="text" placeholder="URL" name="rahmentemplate_settings_input_templates_field[<?php echo $key ?>][url]" value="<?php echo $field['url'] ?? ''; ?>" />
                    <input class="inputReplace" type="text" placeholder="Zu ersetzender Text" name="rahmentemplate_settings_input_templates_field[<?php echo $key ?>][replace]" value="<?php echo $field['replace'] ?? ''; ?>" />
                    <input class="countedTemplates" type="text" disabled placeholder="nicht in Benutzung" value="<?php echo (array_key_exists($field['ID'], $counted_templates) && $field['ID']) ? $counted_templates[$field['ID']] . ' mal in Benutzung' : 'nicht in Benutzung'  ?>" />
                    <?php if (!$idExist) { ?>
                        <button id="removeRow" class="removeRow button">Löschen</button>
                    <?php } else {
                        ?>
                        <button id="openDetails" class="button openDetails">Details</button>
                    <?php } ?>
                </div>
                <div class="details">
                    <div class="detailsLeft">
                        <h4><?php $this->addCacheData($field, $key); ?></h4>
                        <label>
                            <input type="checkbox" value="<?php echo $field['title'] . '_transient' ?>" id="cacheButton" class="button cacheButton"><br><br>
                            <?php
                            ?>
                        </label>
                    </div>
                    <div class="detailsRight">
                        <h4>Seiten</h4>
                        <div class="detailGroup">
                            <?php
                            if ($idExist) {
                                $this->getAdminPagesUsingTemplate($counted_templates, $field);
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }

    private function getAdminPagesUsingTemplate($counted_templates, $field): void
    {
        if (array_key_exists($field['ID'], $counted_templates) && $field['ID']) {
            $args = array(
                'post_type' => 'page',
                'meta_query' => array(
                    array(
                        'key' => 'rahmentemplate_settings_input_templates_field',
                        'value' => $field['ID'],
                        'compare' => 'LIKE'
                    )
                )
            );
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<a class="detailPage" target="_blank" href="' . admin_url( 'post.php?post=' . get_the_ID() ) . '&action=edit' . '">' . get_the_title() . '</a><br>';
                }
            }
        }
    }

    private function addCacheData(mixed $field, $key)
    {
        $cache = get_transient($field['title'] . '_transient');

        if (isset($cache['createdAt']) &&  $cache['createdAt'] > 0) {
            echo '<p>Cache wurde erstellt am: <br>'. date('d.m.Y H:i', $cache['createdAt']) . '</p>';
        } else {
            echo '<p>Cache nicht erstellt.</p>';
        }
    }

    public function rahmentemplate_settings_page() {
        ?>
        <div class="wrap">
            <h2>Rahmentemplate Settings</h2>
            <form action="options.php" method="post" id="options" class="options">
                <?php
                settings_fields('rahmentemplate-settings-page');
                do_settings_sections('rahmentemplate-settings-page');
                submit_button('Änderungen Speichern');
                exit;
                ?>
            </form>
        </div>
        <?php
    }


    public function css() {
        ?>
        <style>
            table {
                border: 4px solid #1d2327;
                overflow: hidden;
            }
            .repeatable-fieldset-container {
                display: flex;
                flex-direction: column;
            }
            .repeatable-fieldset {
                padding-bottom: 1.5em;
                padding-top: 1.5em;
            }
            .input-group {
                display: flex;
                align-items: center;
            }
            .repeatable-fieldset select {
                flex: 1;
            }
            .repeatable-fieldset-actions {
                margin-top: 10px;
            }
            .repeatable-fieldset-container input {
                flex: 1;
                width: 20%;
                margin-right: 10px;
                box-shadow: 0 6px 10px 0 rgba(0, 0, 0, 0.1);
                transition: .3s;
                border: none;
            }
            .repeatable-fieldset-container input:focus {
                box-shadow: 0 6px 10px 0 rgba(0, 0, 0, 0.4);
                transition: .3s;
            }
            .repeatable-fieldset-container .openDetails {
                flex: 1;
                width: 20%;
                margin-right: 0;
                font-weight: bold;
            }
            button {
                width: 100px;
            }
            .removeRow, .red {
                border-color: #dc3232 !important;
                color: #dc3232 !important;
                transition: .3s;
                box-shadow: 0 6px 10px 0 rgba(220, 50, 50, 0.3);
                flex: 1;
                width: 20%;
                font-weight: bold;
                margin-right: 0 !important;
            }
            .red {
                width: 19.5%;
            }
            .removeRow:hover, .red:hover, .detailsLeft input[type="checkbox"]:hover {
                border-color: #dc3232 !important;
                color: #fff !important;
                background: #dc3232 !important;
                transition: .3s;
            }
            .detailsLeft input[type="checkbox"] {
                border-color: #dc3232 !important;
                color: #dc3232;
                transition: .3s;
                box-shadow: 0 6px 10px 0 rgba(220, 50, 50, 0.3);
            }
            .detailsLeft input[type="checkbox"] {
                width: 60%;
            }
            .detailsLeft input[type="checkbox"]:checked {
                background: #dc3232 !important;
                transition: .3s;
                box-shadow: 0 6px 10px 0 rgba(220, 50, 50, 0.3) !important;
            }
            .detailsLeft input[type="checkbox"]:checked::before {
                display: none;
            }
            .detailsLeft input[type="checkbox"]::after {
                content: 'Cache leeren';
                color: #dc3232;
                font-weight: bold;
            }
            .detailsLeft input[type="checkbox"]:hover::after, .detailsLeft input[type="checkbox"]:checked::after {
                color: #fff !important;
                transition: .3s;
            }
            .detailsLeft input[type="checkbox"]:focus {
                box-shadow: 0 6px 10px 0 rgba(220, 50, 50, 0.4) !important;
                transition: .3s;
                border: none !important;
            }
            .addRow {
                border-color: #46b450 !important;
                color: #46b450 !important;
                width: 19.5%;
                box-shadow: 0 6px 10px 0 rgba(70, 180, 80, 0.3) !important;
                transition: .3s;
                font-weight: bold;
            }
            .addRow:hover {
                border-color: #46b450 !important;
                color: #fff !important;
                background: #46b450 !important;
                transition: .3s;
            }
            .openDetails {
                box-shadow: 0 6px 10px 0 rgba(34, 113, 177, 0.3) !important;
            }
            .openDetails:hover, .detailPage:hover, .openDetails.open {
                border-color: #2271b1 !important;
                color: #fff !important;
                background: #2271b1 !important;
                transition: .3s;
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
                display: flex;
                align-items: flex-start;
                justify-content: start;
            }
            .detailGroup {
                display: flex;
            }
            .details h4 {
                margin-right: 10px;
            }
            .detailPage {
                margin-right: 1em;
                padding: 0.3em 1em;
                border: 1px solid #3582c4;
                border-radius: 5px;
                text-decoration: none;
                background: #f6f7f7;
                color: #0a4b78;
            }
            .detailPage:hover {
                background: #f0f0f1;
                border-color: #0a4b78;
                color: #0a4b78;
            }
            th {
                text-transform: uppercase;
                font-weight: bold;
                text-align: center !important;
                width: 100% !important;
                font-size: 1.3em !important;
                background: #1d2327;
                color: #fff !important;
            }
            tr {
                display: flex;
                flex-flow: column;
            }
            tbody:first-child td {
                text-align:center
            }
            .headings {
                display: flex;
                align-items: center;
                border-bottom: 1px solid black;
                text-align: left;

                span {
                    flex: 1;
                    margin-right: 10px;
                    width: 20%;
                }
            }
            .selectDefault {
                border: none;
                box-shadow: 0 6px 10px 0 rgba(0, 0, 0, 0.1) !important;
                width: 100%;
                text-align: center;
                transition: .3s;
            }
            .selectDefault:focus {
                box-shadow: 0 6px 10px 0 rgba(0, 0, 0, 0.4) !important;
                transition: .3s;
                border: none !important;
            }
            .selectDefault:hover {
                color: #2c3338 !important;
            }
            .infobox {
                display: flex;
                justify-content: center;
                align-items: center;
                flex-flow: column;
                padding-bottom: 2em;

                p {
                    width: 50%;
                }
            }
            .detailsLeft {
                width: 20%;
                text-align: left;
            }
            .detailsRight {
                text-align: left;
            }
            .cacheInfo {
                margin-bottom: 2em;
                color: #46b450;
                text-shadow: 0 6px 10px rgba(70, 180, 80, 0.2);
                font-weight: bold;
            }


        </style>
        <?php
    }


    public function js() {
        ?>
        <script>
            jQuery(document).ready(function($) {
                $('#addRow').on('click', function() {
                    var row = $('.empty-row').clone(true);
                    row.removeClass('empty-row');
                    row.css('display', 'block')
                    row.addClass('repeatable-fieldset');
                    $('.repeatable-fieldset-container').append(row);

                    var containers = $('.repeatable-fieldset-container').find('.repeatable-fieldset');
                    containers.each(function(containerIndex) {
                        $(this).find('.inputID').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][ID]');
                        $(this).find('.inputTitle').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][title]');
                        $(this).find('.inputURL').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][url]');
                        $(this).find('.inputReplace').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][replace]');
                    });

                    return false;
                });


                $(document).on('click', '.openDetails', function() {
                    var containers = $(this).parents('.repeatable-fieldset').find('.details');
                    containers.each(function(containerIndex, container) {
                        $(container).toggleClass('open');
                    });
                    $(this).toggleClass('open'); // Toggle the 'open' class on the clicked element
                    return false;
                });

                $(document).on('click', '.removeRow', function() {
                    $(this).parents('.repeatable-fieldset').remove();

                    var containers = $('.repeatable-fieldset-container').find('.repeatable-fieldset');
                    containers.each(function(containerIndex) {
                        $(this).find('.inputID').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][ID]');
                        $(this).find('.inputTitle').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][title]');
                        $(this).find('.inputURL').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][url]');
                        $(this).find('.inputReplace').attr('name', 'rahmentemplate_settings_input_templates_field[' + containerIndex + '][replace]');
                    });

                    return false;
                });

                $(document).on('change', '#cacheButton', function() {
                    var containers = $(this).parents('.repeatable-fieldset').find('.detailsLeft input[type="checkbox"]');
                    containers.each(function(containerIndex, container) {
                        $(container).toggleClass('marked');
                    });
                });

                $(document).on('click', '#clearAllCaches', function() {
                    const markedCaches = document.querySelectorAll('.marked');
                    let cacheArray = [];
                    markedCaches.forEach(function(item) {
                        cacheArray.push(item.value);
                    });

                    const data = {
                        'action': 'clearCache',
                        'transient_keys': cacheArray
                    };

                    $.post('/wp-json/rahmentemplate/v1/clearCache', data, async function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            console.log('Failed to clear cache.');
                        }
                    });
                });

            });
        </script>
        <?php
    }
}
