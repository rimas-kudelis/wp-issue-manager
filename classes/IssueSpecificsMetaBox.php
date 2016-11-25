<?php
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2016 Rimas Kudelis <rq@akl.lt>.
*/

namespace RQ;

if (!class_exists('RQ\IssueSpecificsMetaBox')) {

    class IssueSpecificsMetaBox {

        const TEXTDOMAIN = 'rq-issue-manager';

        /**
         * Custom fields for the Issue Post Type. Labels require localization,
         * so we actually this during object instantiation;
         */
        protected $meta_box_fields = array();

        /**
         * Object constructor.
         */
        public function __construct() {
            $this->meta_box_fields = array(
                array(
                    'id' => 'issue_number',
                    'label' => __('Issue Number', self::TEXTDOMAIN),
                    'type' => 'number',
                ),
                array(
                    'id' => 'issue_date',
                    'label' => __('Issue Date', self::TEXTDOMAIN),
                    'type' => 'datetime',
                ),
                array(
                    'id' => 'issue_pdf',
                    'label' => __('Issue PDF', self::TEXTDOMAIN),
                    'type' => 'media',
                ),
            );
        }

        /**
         * Get the HTML code for this meta box.
         *
         * @param $post_id int Post ID
         */
        public function add_meta_box_callback($post) {
            wp_nonce_field('issue_specifics_data', 'issue_specifics_nonce');
            echo $this->generate_fields($post->ID);
        }

        /**
         * Save post meta for Issue Specifics meta box.
         */
        public function save_post_meta($post_id) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!isset($_POST['issue_specifics_nonce'])) {
                return;
            }

            if (!wp_verify_nonce($_POST['issue_specifics_nonce'], 'issue_specifics_data')) {
                return;
            }

            foreach ($this->meta_box_fields as $field) {
                if (isset($_POST[$field['id']])) {
                    switch ($field['type']) {
                        case 'email':
                            $_POST[$field['id']] = sanitize_email($_POST[$field['id']]);
                            break;
                        case 'text':
                            $_POST[$field['id']] = sanitize_text_field($_POST[$field['id']]);
                            break;
                    }
                    update_post_meta($post_id, $field['id'], $_POST[$field['id']]);
                } else if ($field['type'] === 'checkbox') {
                    update_post_meta($post_id, $field['id'], '0');
                }
            }
        }

        /**
         * Generates the fields HTML for a meta box.
         */
        protected function generate_fields($post_id) {
            $output = '';
            $fields = $this->meta_box_fields;
            foreach ($fields as $field) {
                $label = '<label for="' . $field['id'] . '">' . esc_html($field['label']) . '</label>';
                $db_value = get_post_meta($post_id, $field['id'], true);
                switch ($field['type']) {
                    case 'media':
                        $input = sprintf(
                            '<input id="%s" name="%s" type="text" value="%s"> <input class="button issue-media-upload-button" id="%s_button" name="%s_button" type="button" value="' . esc_html(__('Upload', self::TEXTDOMAIN)) . '" />',
                            $field['id'],
                            $field['id'],
                            $db_value,
                            $field['id'],
                            $field['id']
                        );
                        break;
                    default:
                        $input = sprintf(
                            '<input id="%s" name="%s" type="%s" value="%s">',
                            $field['id'],
                            $field['id'],
                            $field['type'],
                            $db_value
                        );
                }
                $output .= '<p>' . $label . '<br>' . $input . '</p>';
            }
            return $output;
        }

    }
}