<?php
defined('ABSPATH') || exit;

if ( ! class_exists( 'OTINO_Lock_Content' ) ) {
    class OTINO_Lock_Content {

        public function __construct() {
            add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
            add_action( 'save_post', [ $this, 'save_meta' ] );
            add_action( 'template_redirect', [ $this, 'check_access' ] );
            add_action( 'init', [ $this, 'handle_redirect_after_login' ] ); // به جای woocommerce_login_redirect
        }

        public function add_meta_box() {
            add_meta_box(
                'otino_lock_meta',
                'قفل ورود کاربر (اوتینو)',
                [ $this, 'render_meta_box' ],
                [ 'post', 'page' ],
                'side',
                'high'
            );
        }

        public function render_meta_box( $post ) {
            $value = get_post_meta( $post->ID, '_otino_lock_post', true );
            ?>
            <label>
                <input type="checkbox" name="otino_lock_post" value="1" <?php checked( $value, '1' ); ?>>
                برای مشاهده این برگه کاربر باید وارد حساب کاربری شود
            </label>
            <?php
        }

        public function save_meta( $post_id ) {
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

            if ( isset( $_POST['otino_lock_post'] ) ) {
                update_post_meta( $post_id, '_otino_lock_post', '1' );
            } else {
                delete_post_meta( $post_id, '_otino_lock_post' );
            }
        }

        public function check_access() {
            if ( is_singular( [ 'post', 'page' ] ) ) {
                global $post;
                $locked = get_post_meta( $post->ID, '_otino_lock_post', true );

                if ( $locked == '1' && ! is_user_logged_in() ) {
                    $redirect_url = get_permalink( $post->ID );
                    $login_url    = wc_get_page_permalink( 'myaccount' );

                    // آدرس با پارامتر
                    $redirect_with_args = add_query_arg( [
                        'redirect-after-login' => 'otino-page',
                        'redirect-url'         => urlencode( $redirect_url ),
                        'pagelock'             => '1'
                    ], $login_url );

                    wp_redirect( $redirect_with_args );
                    exit;
                }
            }
        }

        public function handle_redirect_after_login() {
            if ( isset($_GET['redirect-after-login']) && is_user_logged_in() ) {
                if ( $_GET['redirect-after-login'] === 'otino-page' && !empty($_GET['redirect-url']) ) {
                    wp_redirect( esc_url_raw( $_GET['redirect-url'] ) );
                    exit;
                }
            }
        }
        
    }

    new OTINO_Lock_Content();
}
