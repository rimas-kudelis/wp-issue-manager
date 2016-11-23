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

if (!class_exists('RQ\IssueArticlesMetaBox')) {

    class IssueArticlesMetaBox {

        const TEXTDOMAIN = 'rq-issue-manager';
        const METAKEY = 'issue_articles';

        /**
         * Output the HTML code for this meta box.
         *
         * @param $post int Post ID.
         */
        public function add_meta_box_callback($post) {
            wp_nonce_field ('issue_articles_data', 'issue_articles_nonce');
            echo self::get_articles($post->ID);
        }

        /**
         * Save post meta for Issue Articles meta box.
         *
         * @param $post_id int Post ID.
         */
        public function save_post_meta($post_id) {
            if (!isset($_POST['issue_articles_nonce'])) {
                return;
            }

            if (!wp_verify_nonce($_POST['issue_articles_nonce'], 'issue_articles_data')) {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (empty($_POST[self::METAKEY]) || !is_array($_POST[self::METAKEY])) {
                delete_post_meta($post_id, self::METAKEY, null);
            }

            $articles = array();

            foreach($_POST[self::METAKEY] as $article_data) {
                if (!isset($article_data['title']) || $article_data['title'] === '') {
                    continue;
                }

                $article = array();

                $article['title'] = (string)$article_data['title'];

                if (isset($article_data['author'])) {
                    $article['author'] = (string)$article_data['author'];
                }

                if (isset($article_data['url'])) {
                    $article['url'] = (string)$article_data['url'];
                }

                $articles[] = $article;
            }

            update_post_meta($post_id, self::METAKEY, $articles);
        }

        /**
         * Returns HTML code for the Issue Articles block.
         *
         * @param $post_id int Post ID.
         * @return string
         */
        protected static function get_articles($post_id) {

            $html .= '<ul class="issue-article-list">'."\n";

            $articles = get_post_meta($post_id, self::METAKEY, $single=true);

            if (!empty($articles)) {
                foreach ($articles as $num => $article) {
                    if(!empty(trim($article['title']))) {
                        $html .= "\t" . self::get_article_row(self::METAKEY, $num, $article) . "\n";
                    }
                }
            }

            $html .= "</ul>\n";
            $html .= '<a href="javascript:void(0);" class="issue-add-article">' . esc_html(__('Add Article', self::TEXTDOMAIN)) . "</a>\n";
            $html .= '<ul class="hidden issue-new-article">' . "\n";
            $html .= "\t" . self::get_article_row(self::METAKEY . "_new") . "\n";
            $html .= "</ul>\n";

            return $html;
        }

        /**
         * Returns HTML code for a single article row.
         *
         * @param $key string Base of all field names.
         * @param $num int|null Order number of the current article row.
         * @param $article array Current article data as an associative array.
         * @return string
         */
        protected static function get_article_row($key, $num = null, array $article = array()) {

            $keyArticle = ($num === null ? $key : "{$key}[{$num}]");

            $html = '<li class="issue-article">';
            $html .= sprintf(
                '<span class="issue-article-order" id="%s[order]">%s</span>',
                $keyArticle,
                esc_html($num + 1)
            );
            $html .= sprintf(
                '<a href="javascript:void(0);" class="issue-del-article">%s</a>',
                esc_html(__('Delete Article', self::TEXTDOMAIN))
            );
            $html .= sprintf(
                '<input class="issue-article-author" type="text" id="%1$s[author]" name="%1$s[author]" value="%2$s" placeholder="%3$s">',
                $keyArticle,
                (isset($article['author']) ? esc_attr($article['author']) : ''),
                esc_attr(__('Article author(s)', self::TEXTDOMAIN))
            );
            $html .= sprintf(
                '<input class="issue-article-title" type="text" id="%1$s[title]" name="%1$s[title]" value="%2$s" placeholder="%3$s">',
                $keyArticle,
                (isset($article['title']) ? esc_attr($article['title']) : ''),
                esc_attr(__('Article title', self::TEXTDOMAIN))
            );
            $html .= sprintf(
                '<input class="issue-article-url" id="%1$s[url]" name="%1$s[url]" type="text" value="%2$s" placeholder="%3$s"><input class="button issue-media-upload-button issue-article-url-button" id="%1$s[url]_button" name="%1$s[url]_button" type="button" value="%4$s" />',
                $keyArticle,
                (isset($article['url']) ? esc_attr($article['url']) : ''),
                esc_attr(__('Article URL', self::TEXTDOMAIN)),
                esc_html(__('Upload', self::TEXTDOMAIN))
            );
            $html .= '</li>';

            return $html;
        }
    }
}