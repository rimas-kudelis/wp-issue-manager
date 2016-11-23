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

if (!class_exists('RQ\IssueManager')) {

    class IssueManager {

        const TEXTDOMAIN = 'rq-issue-manager';

        /**
         * Meta box class instances.
         */
        protected $issue_specifics_meta_box;
        protected $issue_articles_meta_box;

        /**
         * Plugin object constructor.
         */
        public function __construct() {
            load_plugin_textdomain (self::TEXTDOMAIN, false, dirname(plugin_basename(dirname(__FILE__))) . '/languages');

            $this->issue_specifics_meta_box = new IssueSpecificsMetaBox;
            $this->issue_articles_meta_box = new IssueArticlesMetaBox;

            add_action('init', array($this, 'register_taxonomy_journal'));
            add_action('init', array($this, 'register_cpt_issue'));
            add_action('init', array($this, 'register_journal_taxonomy_for_issue_cpt'));

            add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

            add_action('save_post', array($this->issue_specifics_meta_box, 'save_post_meta'));
            add_action('save_post', array($this->issue_articles_meta_box, 'save_post_meta'));

            add_action('pre_get_posts', array($this, 'order_issues'), 1);
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_assets'));
        }

        /**
         * Register the Journal Taxonomy.
         */
        public function register_taxonomy_journal() {

            $labels = array(
                'name'                       => _x( 'Journals', 'Taxonomy General Name', self::TEXTDOMAIN),
                'singular_name'              => _x('Journal', 'Taxonomy Singular Name', self::TEXTDOMAIN),
                'menu_name'                  => __('Journals', self::TEXTDOMAIN),
                'all_items'                  => __('All Journals', self::TEXTDOMAIN),
                'parent_item'                => __('Parent Journal', self::TEXTDOMAIN),
                'parent_item_colon'          => __('Parent Journal:', self::TEXTDOMAIN),
                'new_item_name'              => __('New Journal Name', self::TEXTDOMAIN),
                'add_new_item'               => __('Add New Journal', self::TEXTDOMAIN),
                'edit_item'                  => __('Edit Journal', self::TEXTDOMAIN),
                'update_item'                => __('Update Journal', self::TEXTDOMAIN),
                'view_item'                  => __('View Journal', self::TEXTDOMAIN),
                'separate_items_with_commas' => __('Separate journals with commas', self::TEXTDOMAIN),
                'add_or_remove_items'        => __('Add or remove journals', self::TEXTDOMAIN),
                'choose_from_most_used'      => __('Choose from the most used', self::TEXTDOMAIN),
                'popular_items'              => __('Popular Journals', self::TEXTDOMAIN),
                'search_items'               => __('Search Journals', self::TEXTDOMAIN),
                'not_found'                  => __('Not Found', self::TEXTDOMAIN),
                'no_terms'                   => __('No journals', self::TEXTDOMAIN),
                'items_list'                 => __('Journal list', self::TEXTDOMAIN),
                'items_list_navigation'      => __('Journal list navigation', self::TEXTDOMAIN),
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
                'name'                  => _x('Issues', 'Post Type General Name', self::TEXTDOMAIN),
                'singular_name'         => _x('Issue', 'Post Type Singular Name', self::TEXTDOMAIN),
                'menu_name'             => _x('Journals', 'Parent Menu Item Name', self::TEXTDOMAIN),
                'name_admin_bar'        => __('Issue', self::TEXTDOMAIN),
                'archives'              => __('Issue Archives', self::TEXTDOMAIN),
                'parent_item_colon'     => __('Parent Issue:', self::TEXTDOMAIN),
                'all_items'             => __('All Issues', self::TEXTDOMAIN),
                'add_new_item'          => __('Add New Issue', self::TEXTDOMAIN),
                'add_new'               => _x('Add New Issue', 'Menu Item Name', self::TEXTDOMAIN),
                'new_item'              => __('New Issue', self::TEXTDOMAIN),
                'edit_item'             => __('Edit Issue', self::TEXTDOMAIN),
                'update_item'           => __('Update Issue', self::TEXTDOMAIN),
                'view_item'             => __('View Issue', self::TEXTDOMAIN),
                'search_items'          => __('Search Issues', self::TEXTDOMAIN),
                'not_found'             => __('Not found', self::TEXTDOMAIN),
                'not_found_in_trash'    => __('Not found in Trash', self::TEXTDOMAIN),
                'featured_image'        => __('Cover Image', self::TEXTDOMAIN),
                'set_featured_image'    => __('Set cover image', self::TEXTDOMAIN),
                'remove_featured_image' => __('Remove cover image', self::TEXTDOMAIN),
                'use_featured_image'    => __('Use as cover image', self::TEXTDOMAIN),
                'insert_into_item'      => __('Insert into issue', self::TEXTDOMAIN),
                'uploaded_to_this_item' => __('Uploaded to this issue', self::TEXTDOMAIN),
                'items_list'            => __('Issue list', self::TEXTDOMAIN),
                'items_list_navigation' => __('Issue list navigation', self::TEXTDOMAIN),
                'filter_items_list'     => __('Filter issue list', self::TEXTDOMAIN),
            );
            $args = array(
                'label'                 => __('Issue', self::TEXTDOMAIN),
                'description'           => __('Periodic downloadable publication', self::TEXTDOMAIN),
                'labels'                => $labels,
                'supports'              => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', 'post-formats'),
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
            register_taxonomy_for_object_type('journal', 'rq_issue');
        }

        /**
         * Add the meta box for the Issue Post Type.
         */
        public function add_meta_boxes() {
            add_meta_box(
                'issue-specifics',
                __('Issue Specifics', self::TEXTDOMAIN),
                array($this->issue_specifics_meta_box, 'add_meta_box_callback'),
                'rq_issue',
                'side',
                'default'
            );
            add_meta_box(
                'issue-articles',
                __('Issue Articles', self::TEXTDOMAIN),
                array($this->issue_articles_meta_box, 'add_meta_box_callback'),
                'rq_issue',
                'normal',
                'default'
            );
        }

        /**
         * Enqueue admin scripts and styles.
         */
        public function admin_enqueue_assets() {
             wp_enqueue_script('issue_admin_script', plugins_url('assets/js/admin.js', dirname(__FILE__)), array('jquery'));
             wp_enqueue_style('issue_admin_style', plugins_url('assets/css/admin.css', dirname(__FILE__)));
        }

        /**
         * When viewing a journal, order issues by issue number, regardless of publication date.
         */
        public function order_issues($query) {
            // Exit if it's the admin or it isn't the main query.
            if (is_admin() || !$query->is_main_query()) {
                return;
            }
            // Order issues by issue number in descending order.
            if (is_tax('journal')) {
                $query->set('order', 'desc');
                $query->set('orderby', 'issue_number');
            }
        }
    }
}
