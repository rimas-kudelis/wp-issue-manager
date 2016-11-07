<?php
/*
Plugin Name: Periodic Downloadable Issue Manager
Description: Publish your journal, newspaper or any other periodic publication in PDF format.
Version: 0.9
Text Domain: rq-issue-manager
Author: Rimas Kudelis
Author URI: https://rimas.kudelis.lt/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

This plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

This plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this plugin. If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
*/

namespace RQ;

// Abort immediately if this file is called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (!class_exists('RQ\IssueManager')) {

    class IssueManager {

        const TEXTDOMAIN = 'rq-issue-manager';

        /**
         * Custom fields for the Issue Post Type. Labels require localization,
         * so we actually this during init();
         */
        protected $meta_box_fields_issue = array();

        /**
         * Plugin object constructor.
         */
        public function __construct() {
        	load_plugin_textdomain( self::TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ))  . '/languages' );
            $this->init();

            add_action( 'init', array( $this, 'register_taxonomy_journal' ));
            add_action( 'init', array( $this, 'register_cpt_issue' ));
            add_action( 'init', array( $this, 'register_journal_taxonomy_for_issue_cpt' ));
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ));
            add_action( 'admin_footer', array( $this, 'admin_footer' ));
            add_action( 'save_post', array( $this, 'save_post' ));
            add_action( 'pre_get_posts', array( $this, 'order_issues'), 1 );
        }

        /**
         * Initialize this plugin.
         */
        protected function init() {
            $this->meta_box_fields_issue = array(
                array(
                    'id' => 'issue-number',
                    'label' => __( 'Issue Number', self::TEXTDOMAIN ),
                    'type' => 'number',
                ),
                array(
                    'id' => 'issue-date',
                    'label' => __( 'Issue Date', self::TEXTDOMAIN ),
                    'type' => 'datetime',
                ),
                array(
                    'id' => 'issue-pdf',
                    'label' => __( 'Issue PDF', self::TEXTDOMAIN ),
                    'type' => 'media',
                ),
            );
        }

        /**
         * Register the Journal Taxonomy.
         */
        public function register_taxonomy_journal() {

            $labels = array(
                'name'                       => _x( 'Journals', 'Taxonomy General Name', self::TEXTDOMAIN ),
                'singular_name'              => _x( 'Journal', 'Taxonomy Singular Name', self::TEXTDOMAIN ),
                'menu_name'                  => __( 'Journals', self::TEXTDOMAIN ),
                'all_items'                  => __( 'All Journals', self::TEXTDOMAIN ),
                'parent_item'                => __( 'Parent Journal', self::TEXTDOMAIN ),
                'parent_item_colon'          => __( 'Parent Journal:', self::TEXTDOMAIN ),
                'new_item_name'              => __( 'New Journal Name', self::TEXTDOMAIN ),
                'add_new_item'               => __( 'Add New Journal', self::TEXTDOMAIN ),
                'edit_item'                  => __( 'Edit Journal', self::TEXTDOMAIN ),
                'update_item'                => __( 'Update Journal', self::TEXTDOMAIN ),
                'view_item'                  => __( 'View Journal', self::TEXTDOMAIN ),
                'separate_items_with_commas' => __( 'Separate journals with commas', self::TEXTDOMAIN ),
                'add_or_remove_items'        => __( 'Add or remove journals', self::TEXTDOMAIN ),
                'choose_from_most_used'      => __( 'Choose from the most used', self::TEXTDOMAIN ),
                'popular_items'              => __( 'Popular Journals', self::TEXTDOMAIN ),
                'search_items'               => __( 'Search Journals', self::TEXTDOMAIN ),
                'not_found'                  => __( 'Not Found', self::TEXTDOMAIN ),
                'no_terms'                   => __( 'No journals', self::TEXTDOMAIN ),
                'items_list'                 => __( 'Journal list', self::TEXTDOMAIN ),
                'items_list_navigation'      => __( 'Journal list navigation', self::TEXTDOMAIN ),
            );
            $args = array(
                'labels'                     => $labels,
                'hierarchical'               => false,
                'public'                     => true,
                'show_ui'                    => true,
                'show_admin_column'          => true,
                'show_in_nav_menus'          => true,
                'show_tagcloud'              => true,
            );
            register_taxonomy( 'journal', null, $args );
        }

        /**
         * Register the Issue Post Type.
         */
        public function register_cpt_issue() {

            $labels = array(
                'name'                  => _x( 'Issues', 'Post Type General Name', self::TEXTDOMAIN ),
                'singular_name'         => _x( 'Issue', 'Post Type Singular Name', self::TEXTDOMAIN ),
                'menu_name'             => __( 'Journals', self::TEXTDOMAIN ),
                'name_admin_bar'        => __( 'Issue', self::TEXTDOMAIN ),
                'archives'              => __( 'Issue Archives', self::TEXTDOMAIN ),
                'parent_item_colon'     => __( 'Parent Issue:', self::TEXTDOMAIN ),
                'all_items'             => __( 'All Issues', self::TEXTDOMAIN ),
                'add_new_item'          => __( 'Add New Issue', self::TEXTDOMAIN ),
                'add_new'               => _x( 'Add New Issue', 'Menu Item Name', self::TEXTDOMAIN ),
                'new_item'              => __( 'New Issue', self::TEXTDOMAIN ),
                'edit_item'             => __( 'Edit Issue', self::TEXTDOMAIN ),
                'update_item'           => __( 'Update Issue', self::TEXTDOMAIN ),
                'view_item'             => __( 'View Issue', self::TEXTDOMAIN ),
                'search_items'          => __( 'Search Issues', self::TEXTDOMAIN ),
                'not_found'             => __( 'Not found', self::TEXTDOMAIN ),
                'not_found_in_trash'    => __( 'Not found in Trash', self::TEXTDOMAIN ),
                'featured_image'        => __( 'Cover Image', self::TEXTDOMAIN ),
                'set_featured_image'    => __( 'Set cover image', self::TEXTDOMAIN ),
                'remove_featured_image' => __( 'Remove cover image', self::TEXTDOMAIN ),
                'use_featured_image'    => __( 'Use as cover image', self::TEXTDOMAIN ),
                'insert_into_item'      => __( 'Insert into issue', self::TEXTDOMAIN ),
                'uploaded_to_this_item' => __( 'Uploaded to this issue', self::TEXTDOMAIN ),
                'items_list'            => __( 'Issue list', self::TEXTDOMAIN ),
                'items_list_navigation' => __( 'Issue list navigation', self::TEXTDOMAIN ),
                'filter_items_list'     => __( 'Filter issue list', self::TEXTDOMAIN ),
            );
            $args = array(
                'label'                 => __( 'Issue', self::TEXTDOMAIN ),
                'description'           => __( 'Periodic downloadable publication', self::TEXTDOMAIN ),
                'labels'                => $labels,
                'supports'              => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', 'post-formats', ),
                'hierarchical'          => false,
                'public'                => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_position'         => 5,
                'menu_icon'             => 'dashicons-admin-page',
                'show_in_admin_bar'     => true,
                'show_in_nav_menus'     => true,
                'can_export'            => true,
                'has_archive'           => true,
                'exclude_from_search'   => false,
                'publicly_queryable'    => true,
                'capability_type'       => 'post',
                'rewrite'               => array('slug' => 'issue'),
            );
            register_post_type( 'rq_issue', $args );
        }

        /**
         * Register Journal Taxonomy for Issue Post Type.
         */
        public function register_journal_taxonomy_for_issue_cpt() {
            register_taxonomy_for_object_type( 'journal', 'rq_issue' );
        }

        /**
         * Add the meta box for the Issue Post Type.
         */
        public function add_meta_boxes() {
            add_meta_box(
                'issue-specifics',
                __( 'Issue Specifics', self::TEXTDOMAIN ),
                array( $this, 'add_meta_box_callback_issue' ),
                'rq_issue',
                'side',
                'default'
            );
        }

        /**
         * Generates the HTML for the meta box for the Issue Post Type.
         *
         * @param object $post WordPress post object
         */
        public function add_meta_box_callback_issue( $post ) {
            wp_nonce_field( 'issue_specifics_data', 'issue_specifics_nonce' );
            $this->generate_fields( $post, $this->meta_box_fields_issue );
        }

        /**
         * Generates the fields HTML for a meta box.
         */
        public function generate_fields( $post, $fields ) {
            $output = '';
            foreach ( $fields as $field ) {
                $label = '<label for="' . $field['id'] . '">' . __( $field['label'], self::TEXTDOMAIN ) . '</label>';
                $db_value = get_post_meta( $post->ID, $field['id'], true );
                switch ( $field['type'] ) {
                    case 'media':
                        $input = sprintf(
                            '<input id="%s" name="%s" type="text" value="%s"> <input class="button rational-metabox-media" id="%s_button" name="%s_button" type="button" value="' . __( 'Upload', self::TEXTDOMAIN ) . '" />',
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
            echo $output;
        }

        /**
         * Hooks into WordPress' admin_footer function.
         * Adds scripts for media uploader.
         */
        public function admin_footer() {
            ?><script>
                // https://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
                jQuery(document).ready(function($){
                    if ( typeof wp.media !== 'undefined' ) {
                        var _custom_media = true,
                        _orig_send_attachment = wp.media.editor.send.attachment;
                        $('.rational-metabox-media').click(function(e) {
                            var send_attachment_bkp = wp.media.editor.send.attachment;
                            var button = $(this);
                            var id = button.attr('id').replace('_button', '');
                            _custom_media = true;
                                wp.media.editor.send.attachment = function(props, attachment){
                                if ( _custom_media ) {
                                    $("#"+id).val(attachment.url);
                                } else {
                                    return _orig_send_attachment.apply( this, [props, attachment] );
                                };
                            }
                            wp.media.editor.open(button);
                            return false;
                        });
                        $('.add_media').on('click', function(){
                            _custom_media = false;
                        });
                    }
                });
            </script><?php
        }

        /**
         * Hooks into WordPress' save_post function
         */
        public function save_post( $post_id ) {
            if ( ! isset( $_POST['issue_specifics_nonce'] ) )
                return $post_id;

            $nonce = $_POST['issue_specifics_nonce'];
            if ( !wp_verify_nonce( $nonce, 'issue_specifics_data' ) )
                return $post_id;

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
                return $post_id;

            foreach ( $this->meta_box_fields_issue as $field ) {
                if ( isset( $_POST[ $field['id'] ] ) ) {
                    switch ( $field['type'] ) {
                        case 'email':
                            $_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
                            break;
                        case 'text':
                            $_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
                            break;
                    }
                    update_post_meta( $post_id, $field['id'], $_POST[ $field['id'] ] );
                } else if ( $field['type'] === 'checkbox' ) {
                    update_post_meta( $post_id, $field['id'], '0' );
                }
            }
        }

        /**
         * When viewing a journal, order issues by issue number, regardless of publication date.
         */
        public function order_issues( $query ) {
            // exit out if it's the admin or it isn't the main query
            if ( is_admin() || ! $query->is_main_query() ) {
                return;
            }
            // order category archives by title in ascending order
            if ( is_tax('journal') ) {
                $query->set( 'order' , 'desc' );
                $query->set( 'orderby', 'issue-number');
            }
        }
    }

    new \RQ\IssueManager;
}
