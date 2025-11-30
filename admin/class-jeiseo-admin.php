<?php
/**
 * Admin functionality
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin class
 */
class JeiSEO_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_filter( 'plugin_action_links_' . JEISEO_PLUGIN_BASENAME, array( $this, 'plugin_links' ) );
    }

    /**
     * Add admin menu
     */
    public function add_menu(): void {
        add_menu_page(
            __( 'JeiSEO', 'jeiseo' ),
            __( 'JeiSEO', 'jeiseo' ),
            'manage_options',
            'jeiseo',
            array( $this, 'render_dashboard' ),
            'dashicons-chart-area',
            30
        );

        add_submenu_page(
            'jeiseo',
            __( 'Dashboard', 'jeiseo' ),
            __( 'Dashboard', 'jeiseo' ),
            'manage_options',
            'jeiseo',
            array( $this, 'render_dashboard' )
        );

        add_submenu_page(
            'jeiseo',
            __( 'SEO Audit', 'jeiseo' ),
            __( 'SEO Audit', 'jeiseo' ),
            'manage_options',
            'jeiseo-audit',
            array( $this, 'render_audit' )
        );

        add_submenu_page(
            'jeiseo',
            __( 'AI Content', 'jeiseo' ),
            __( 'AI Content', 'jeiseo' ),
            'manage_options',
            'jeiseo-content',
            array( $this, 'render_content' )
        );

        add_submenu_page(
            'jeiseo',
            __( 'Settings', 'jeiseo' ),
            __( 'Settings', 'jeiseo' ),
            'manage_options',
            'jeiseo-settings',
            array( $this, 'render_settings' )
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets( string $hook ): void {
        if ( strpos( $hook, 'jeiseo' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'jeiseo-admin',
            JEISEO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JEISEO_VERSION
        );

        wp_enqueue_script(
            'jeiseo-admin',
            JEISEO_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            JEISEO_VERSION,
            true
        );

        wp_localize_script(
            'jeiseo-admin',
            'jeiseoAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'jeiseo_nonce' ),
                'strings' => array(
                    'running'  => __( 'Running audit...', 'jeiseo' ),
                    'fixing'   => __( 'Fixing issues...', 'jeiseo' ),
                    'complete' => __( 'Complete!', 'jeiseo' ),
                    'error'    => __( 'An error occurred.', 'jeiseo' ),
                ),
            )
        );
    }

    /**
     * Register settings
     */
    public function register_settings(): void {
        register_setting( 'jeiseo_settings', 'jeiseo_api_provider' );
        register_setting( 'jeiseo_settings', 'jeiseo_api_key' );
        register_setting( 'jeiseo_settings', 'jeiseo_auto_fix' );
        register_setting( 'jeiseo_settings', 'jeiseo_weekly_report' );
        register_setting( 'jeiseo_settings', 'jeiseo_license_key' );
    }

    /**
     * Add plugin action links
     */
    public function plugin_links( array $links ): array {
        $custom = array(
            '<a href="' . admin_url( 'admin.php?page=jeiseo' ) . '">' . __( 'Dashboard', 'jeiseo' ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=jeiseo-settings' ) . '">' . __( 'Settings', 'jeiseo' ) . '</a>',
        );

        if ( ! jeiseo()->is_pro() ) {
            $custom[] = '<a href="https://jeiseo.com/pro" target="_blank" style="color:#22c55e;font-weight:bold;">' . __( 'Upgrade to PRO', 'jeiseo' ) . '</a>';
        }

        return array_merge( $custom, $links );
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard(): void {
        include JEISEO_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render audit page
     */
    public function render_audit(): void {
        include JEISEO_PLUGIN_DIR . 'admin/views/audit.php';
    }

    /**
     * Render content page
     */
    public function render_content(): void {
        include JEISEO_PLUGIN_DIR . 'admin/views/content.php';
    }

    /**
     * Render settings page
     */
    public function render_settings(): void {
        include JEISEO_PLUGIN_DIR . 'admin/views/settings.php';
    }
}
