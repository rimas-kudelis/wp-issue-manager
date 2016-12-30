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

namespace RQ\IssueManager;

if (!class_exists('RQ\IssueManager\IssueSpecificsMetaBox')) {

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
                    'type' => 'date_inexact',
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
            echo Helper::generate_fields($post->ID, $this->meta_box_fields);
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

            Helper::save_post_meta($post_id, $this->meta_box_fields);
        }
    }
}