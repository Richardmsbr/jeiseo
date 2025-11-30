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
            __( 'JeiSEO', 'jeiseo-ai-marketing-automation' ),
            __( 'JeiSEO', 'jeiseo-ai-marketing-automation' ),
            'manage_options',
            'jeiseo-ai-marketing-automation',
            array( $this, 'render_dashboard' ),
            'dashicons-chart-area',
            30
        );

        add_submenu_page(
            'jeiseo-ai-marketing-automation',
            __( 'Dashboard', 'jeiseo-ai-marketing-automation' ),
            __( 'Dashboard', 'jeiseo-ai-marketing-automation' ),
            'manage_options',
            'jeiseo-ai-marketing-automation',
            array( $this, 'render_dashboard' )
        );

        add_submenu_page(
            'jeiseo-ai-marketing-automation',
            __( 'SEO Audit', 'jeiseo-ai-marketing-automation' ),
            __( 'SEO Audit', 'jeiseo-ai-marketing-automation' ),
            'manage_options',
            'jeiseo-audit',
            array( $this, 'render_audit' )
        );

        add_submenu_page(
            'jeiseo-ai-marketing-automation',
            __( 'AI Content', 'jeiseo-ai-marketing-automation' ),
            __( 'AI Content', 'jeiseo-ai-marketing-automation' ),
            'manage_options',
            'jeiseo-content',
            array( $this, 'render_content' )
        );

        add_submenu_page(
            'jeiseo-ai-marketing-automation',
            __( 'Settings', 'jeiseo-ai-marketing-automation' ),
            __( 'Settings', 'jeiseo-ai-marketing-automation' ),
            'manage_options',
            'jeiseo-settings',
            array( $this, 'render_settings' )
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets( string $hook ): void {
        if ( strpos( $hook, 'jeiseo-ai-marketing-automation' ) === false ) {
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
                    'running'  => __( 'Running audit...', 'jeiseo-ai-marketing-automation' ),
                    'fixing'   => __( 'Fixing issues...', 'jeiseo-ai-marketing-automation' ),
                    'complete' => __( 'Complete!', 'jeiseo-ai-marketing-automation' ),
                    'error'    => __( 'An error occurred.', 'jeiseo-ai-marketing-automation' ),
                ),
            )
        );
    }

    /**
     * Register settings
     */
    public function register_settings(): void {
        register_setting(
            'jeiseo_settings',
            'jeiseo_api_provider',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'openai',
            )
        );

        register_setting(
            'jeiseo_settings',
            'jeiseo_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            )
        );

        register_setting(
            'jeiseo_settings',
            'jeiseo_auto_fix',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
            )
        );

        register_setting(
            'jeiseo_settings',
            'jeiseo_weekly_report',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => true,
            )
        );

        register_setting(
            'jeiseo_settings',
            'jeiseo_license_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            )
        );
    }

    /**
     * Add plugin action links
     */
    public function plugin_links( array $links ): array {
        $custom = array(
            '<a href="' . admin_url( 'admin.php?page=jeiseo' ) . '">' . __( 'Dashboard', 'jeiseo-ai-marketing-automation' ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=jeiseo-settings' ) . '">' . __( 'Settings', 'jeiseo-ai-marketing-automation' ) . '</a>',
        );

        if ( ! jeiseo()->is_pro() ) {
            $custom[] = '<a href="https://jeiseo.com/pro" target="_blank" style="color:#22c55e;font-weight:bold;">' . __( 'Upgrade to PRO', 'jeiseo-ai-marketing-automation' ) . '</a>';
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
