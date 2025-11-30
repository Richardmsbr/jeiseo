<?php
/**
 * Plugin Name: JeiSEO
 * Plugin URI: https://github.com/Richardmsbr/jeiseo
 * Description: Transform your WordPress into a complete marketing agency. Automated SEO audits, AI content generation, auto-fix issues, and real ROI tracking.
 * Version: 1.0.0
 * Author: Richard
 * Author URI: https://github.com/Richardmsbr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jeiseo-ai-marketing-automation
 * Requires at least: 6.0
 * Requires PHP: 8.1
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'JEISEO_VERSION', '1.0.0' );
define( 'JEISEO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JEISEO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JEISEO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
final class JeiSEO {

    /**
     * Single instance
     */
    private static ?JeiSEO $instance = null;

    /**
     * License manager
     */
    public ?JeiSEO_License $license = null;

    /**
     * SEO Audit module
     */
    public ?JeiSEO_Audit $audit = null;

    /**
     * Content AI module
     */
    public ?JeiSEO_Content $content = null;

    /**
     * Get instance
     */
    public static function instance(): JeiSEO {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies(): void {
        // Core
        require_once JEISEO_PLUGIN_DIR . 'includes/class-jeiseo-license.php';
        require_once JEISEO_PLUGIN_DIR . 'includes/class-jeiseo-helpers.php';
        require_once JEISEO_PLUGIN_DIR . 'includes/class-jeiseo-api.php';

        // Admin
        require_once JEISEO_PLUGIN_DIR . 'admin/class-jeiseo-admin.php';
        require_once JEISEO_PLUGIN_DIR . 'admin/class-jeiseo-dashboard.php';

        // Modules
        require_once JEISEO_PLUGIN_DIR . 'modules/audit/class-jeiseo-audit.php';
        require_once JEISEO_PLUGIN_DIR . 'modules/content/class-jeiseo-content.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks(): void {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize plugin
     */
    public function init(): void {
        // Initialize license manager
        $this->license = new JeiSEO_License();

        // Initialize modules
        $this->audit = new JeiSEO_Audit();
        $this->content = new JeiSEO_Content();

        // Initialize admin
        if ( is_admin() ) {
            new JeiSEO_Admin();
            new JeiSEO_Dashboard();
        }
    }

    /**
     * Activation hook
     */
    public function activate(): void {
        // Create database tables
        $this->create_tables();

        // Set default options
        $this->set_defaults();

        // Schedule cron events
        if ( ! wp_next_scheduled( 'jeiseo_daily_audit' ) ) {
            wp_schedule_event( time(), 'daily', 'jeiseo_daily_audit' );
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Deactivation hook
     */
    public function deactivate(): void {
        // Clear scheduled events
        wp_clear_scheduled_hook( 'jeiseo_daily_audit' );

        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private function create_tables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jeiseo_audits (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            audit_date datetime NOT NULL,
            score int(3) NOT NULL DEFAULT 0,
            issues_count int(5) NOT NULL DEFAULT 0,
            issues_data longtext,
            fixed_count int(5) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY audit_date (audit_date)
        ) $charset_collate;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jeiseo_content (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED,
            content_type varchar(50) NOT NULL,
            prompt text,
            generated_content longtext,
            created_at datetime NOT NULL,
            status varchar(20) DEFAULT 'draft',
            PRIMARY KEY (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Set default options
     */
    private function set_defaults(): void {
        $defaults = array(
            'jeiseo_version'        => JEISEO_VERSION,
            'jeiseo_license_key'    => '',
            'jeiseo_license_status' => 'free',
            'jeiseo_api_provider'   => 'openai',
            'jeiseo_api_key'        => '',
            'jeiseo_auto_fix'       => false,
            'jeiseo_weekly_report'  => true,
            'jeiseo_last_audit'     => '',
            'jeiseo_audit_count'    => 0,
            'jeiseo_content_count'  => 0,
        );

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }

    /**
     * Check if PRO version
     */
    public function is_pro(): bool {
        return $this->license && $this->license->is_valid();
    }

    /**
     * Get remaining free quota
     */
    public function get_free_quota( string $type ): int {
        $month = gmdate( 'Y-m' );
        $key = "jeiseo_{$type}_count_{$month}";
        $count = (int) get_option( $key, 0 );

        $limits = array(
            'audit'   => 4,  // 1 per week
            'content' => 3,  // 3 posts per month
        );

        $limit = $limits[ $type ] ?? 0;

        return max( 0, $limit - $count );
    }

    /**
     * Increment usage counter
     */
    public function increment_usage( string $type ): void {
        $month = gmdate( 'Y-m' );
        $key = "jeiseo_{$type}_count_{$month}";
        $count = (int) get_option( $key, 0 );
        update_option( $key, $count + 1 );
    }
}

/**
 * Returns main plugin instance
 */
function jeiseo(): JeiSEO {
    return JeiSEO::instance();
}

// Initialize plugin
jeiseo();
