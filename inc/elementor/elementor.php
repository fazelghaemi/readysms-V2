<?php

/**
 * Main Elementor ReadySMS Extension Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class Elementor_ReadySMS_Extension {

    /**
     * Plugin Version
     *
     * @since 1.0.0
     *
     * @var string The plugin version.
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     *
     * @since 1.0.0
     *
     * @var string Minimum Elementor version required to run the plugin.
     */
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     *
     * @var string Minimum PHP version required to run the plugin.
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Instance
     *
     * @since 1.0.0
     *
     * @access private
     * @static
     *
     * @var Elementor_ReadySMS_Extension The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     *
     * @access public
     * @static
     *
     * @return Elementor_ReadySMS_Extension An instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function __construct() {
        add_action( 'after_setup_theme', [ $this, 'on_plugins_loaded' ] );
        add_action('elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ]);
    }

    public function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'readysms_category',
            [
                'title' => __('المان‌های ReadySMS', 'readysms'),
                'icon' => 'fas fa-plug readysms-el-icon-type',
            ]
        );
    }

    /**
     * Load Textdomain
     *
     * @since 1.0.0
     * @access public
     */
    public function i18n() {
        load_plugin_textdomain( 'readysms' );
    }

    /**
     * On Plugins Loaded
     *
     * @since 1.0.0
     * @access public
     */
    public function on_plugins_loaded() {
        if ( $this->is_compatible() ) {
            add_action( 'elementor/init', [ $this, 'init' ] );
        }
    }

    /**
     * Compatibility Checks
     *
     * @since 1.0.0
     * @access public
     */
    public function is_compatible() {
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
            return false;
        }

        if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return false;
        }

        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return false;
        }

        return true;
    }

    /**
     * Initialize the plugin
     *
     * @since 1.0.0
     * @access public
     */
    public function init() {
        $this->i18n();

        add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
        add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );
    }

    /**
     * Init Widgets
     *
     * @since 1.0.0
     * @access public
     */
    public function init_widgets() {
        // Include Widget files
        require_once( __DIR__ . '/widgets/login-form-widget.php' );

        // Register widget
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_ReadySMS_Login_Form_Widget() );
    }

    /**
     * Init Controls
     *
     * @since 1.0.0
     * @access public
     */
    public function init_controls() {
        // Intentionally left empty
    }

    /**
     * Admin notice
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_missing_main_plugin() {
        // Placeholder for notice
    }

    /**
     * Admin notice
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_minimum_elementor_version() {
        // Placeholder for notice
    }

    /**
     * Admin notice
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_minimum_php_version() {
        // Placeholder for notice
    }
}

Elementor_ReadySMS_Extension::instance();