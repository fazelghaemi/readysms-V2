<?php
defined('ABSPATH') || exit;

if (!class_exists('READYSMS_Lock_Content')) {
    /**
     * Class READYSMS_Lock_Content
     * Handles content locking for non-logged-in users.
     */
    class READYSMS_Lock_Content {

        public function __construct() {
            add_action('add_meta_boxes', [$this, 'add_meta_box']);
            add_action('save_post', [$this, 'save_meta_data']);
            add_action('template_redirect', [$this, 'check_content_access']);
            add_action('init', [$this, 'handle_redirect_after_login']);
        }

        /**
         * Adds the meta box to post and page edit screens.
         */
        public function add_meta_box() {
            add_meta_box(
                'readysms_lock_meta_box',      // Meta box ID
                'قفل محتوا (ReadySMS)',        // Meta box Title
                [$this, 'render_meta_box_content'], // Callback function
                ['post', 'page'],              // Post types
                'side',                        // Position
                'high'                         // Priority
            );
        }

        /**
         * Renders the content of the meta box.
         * @param WP_Post $post The current post object.
         */
        public function render_meta_box_content($post) {
            // Use a unique meta key for our plugin
            $is_locked = get_post_meta($post->ID, '_readysms_content_locked', true);
            ?>
            <label>
                <input type="checkbox" name="readysms_lock_post_field" value="1" <?php checked($is_locked, '1'); ?>>
                برای مشاهده این محتوا، کاربر باید وارد شود.
            </label>
            <?php
        }

        /**
         * Saves the custom meta data when a post is saved.
         * @param int $post_id The ID of the post being saved.
         */
        public function save_meta_data($post_id) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            // Check if our field is set.
            if (isset($_POST['readysms_lock_post_field'])) {
                update_post_meta($post_id, '_readysms_content_locked', '1');
            } else {
                delete_post_meta($post_id, '_readysms_content_locked');
            }
        }

        /**
         * Checks if the current content is locked and if the user has access.
         * If not, redirects to the login page.
         */
        public function check_content_access() {
            if (is_singular(['post', 'page'])) {
                global $post;
                $is_locked = get_post_meta($post->ID, '_readysms_content_locked', true);

                if ($is_locked && !is_user_logged_in()) {
                    $current_page_url = get_permalink($post->ID);
                    $login_page_url = wc_get_page_permalink('myaccount'); // Assuming WooCommerce is active

                    // Add query arguments for redirecting back after login
                    $redirect_url_with_args = add_query_arg([
                        'redirect_after_login' => 'readysms-content',
                        'redirect_url'         => urlencode($current_page_url),
                    ], $login_page_url);

                    wp_redirect($redirect_url_with_args);
                    exit;
                }
            }
        }

        /**
         * Handles the redirection back to the locked page after a successful login.
         */
        public function handle_redirect_after_login() {
            if (is_user_logged_in() && isset($_GET['redirect_after_login'])) {
                if ($_GET['redirect_after_login'] === 'readysms-content' && !empty($_GET['redirect_url'])) {
                    wp_redirect(esc_url_raw(urldecode($_GET['redirect_url'])));
                    exit;
                }
            }
        }
    }

    new READYSMS_Lock_Content();
}