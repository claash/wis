<?php

/**
 * Plugin name: Insta Scrapper
 * Description:       Load media from instagram to wordpress media library
 * Version:           1.0.1
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * Author:            claash
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       insta-scrapper
 * Domain Path:       /lang
 */

if (!class_exists('WP_Insta_Scrapper')) {

    /**
     * Composer
     */
    require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

    /**
     * Constants
     */
    define('WIS', 'insta-scrapper');
    define('WIS_PATH', plugin_dir_path(__FILE__));
    define('WIS_URL', plugin_dir_url(__FILE__));

    class WP_Insta_Scrapper
    {

        function __construct()
        {
            add_action('admin_menu', [$this, 'register_subpage']);
            add_action('admin_enqueue_scripts', [$this, 'styles']);
            add_action('wp_ajax_wis_load', [$this, 'get_insta_media']);
        }

        /**
         * Register subpage in media (upload.php)
         */
        function register_subpage()
        {
            add_submenu_page(
                'upload.php',
                __('Insta Scrapper', WIS),
                __('Insta Scrapper', WIS),
                'manage_options',
                WIS,
                [$this, 'render_subpage']
            );
        }

        /**
         * Register style css, js
         */
        function styles()
        {
            wp_enqueue_style('wis-styles', WIS_URL . '/assets/style.css');
            wp_enqueue_script('wis-script', WIS_URL . '/assets/script.js', ['jquery'], true);
        }

        /**
         * Template loader helper
         */
        function template_load($template = '', $atts = [])
        {
            if ($template == '') return;

            require_once WIS_PATH . 'view/template-' . $template . '.php';

            die();
        }

        /**
         * Render sub page
         */
        function render_subpage()
        {
            echo $this->template_load('base');

            die();
        }

        /**
         * Get attachment by name
         * 
         * @param string $name
         * @return object
         */
        function get_media_by_name($name)
        {
            $args           = array(
                'posts_per_page' => 1,
                'post_type'      => 'attachment',
                'name'           => trim( $name ),
            );

            $get_attachment = new WP_Query( $args );

            if ( ! $get_attachment || ! isset( $get_attachment->posts, $get_attachment->posts[0] ) ) {
                return false;
            }

            return $get_attachment->posts[0];
        }

        /**
         * Upload media by url
         * 
         * @param object $media
         * @return array
         */
        function upload_media($media)
        {
            $data = [
                'exist' => false,
            ];

            $account = $media->getOwner();
            $user_name = $account->getUsername();
            $name = sanitize_file_name($user_name) . '_' . $media->getId();

            $is_exist = $this->get_media_by_name($name);

            if ($is_exist)  {
                $data['exist'] = true;
                $data['id'] = $is_exist->ID;
                return $data;
            }

            $image_url = $media->getImageHighResolutionUrl();
            $original_url = $media->getLink();

            $upload_dir = wp_upload_dir();

            $image_data = file_get_contents($image_url);

            if (wp_mkdir_p($upload_dir['path'])) {
                $file = $upload_dir['path'] . '/' . $name . '.jpg';
            } else {
                $file = $upload_dir['basedir'] . '/' . $name . '.jpg';
            }

            file_put_contents($file, $image_data);

            $attachment = array(
                'post_mime_type' => 'image/jpeg',
                'post_title' => $name,
                'post_content' => $media->getCaption(),
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $file);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);

            update_post_meta($attach_id, 'user_name', $user_name);
            update_post_meta($attach_id, 'original_url', $original_url);

            if (is_plugin_active('wp-media-library-categories/index.php')) {
                wp_set_object_terms($attach_id, 'instagram', get_option( 'wpmlc_settings' )['wpmediacategory_taxonomy']);
            }

            $data['id'] = $attach_id;

            return $data;
        }

        /**
         * WP Ajax and get media by url from instagram
         */
        function get_insta_media()
        {
            $urls = $_POST['urls'];

            if (empty($urls)) {
                wp_send_json([
                    'status' => 'error',
                    'message' => 'No urls'
                ]);
            }

            $urls = explode(',', $urls);

            $instagram = new \InstagramScraper\Instagram(new \GuzzleHttp\Client());

            foreach ($urls as $url) {

                $sanitized_url = trim(str_replace('?utm_source=ig_web_copy_link', '', $url));

                echo $this->template_load('uploaded-item', ['data' => $this->upload_media($instagram->getMediaByUrl($sanitized_url))]);
            }

            die();
        }
    }
}

new WP_Insta_Scrapper();
