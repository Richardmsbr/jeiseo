<?php
/**
 * Dashboard functionality
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dashboard class
 */
class JeiSEO_Dashboard {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_jeiseo_get_stats', array( $this, 'ajax_get_stats' ) );
    }

    /**
     * Get dashboard stats
     */
    public function get_stats(): array {
        global $wpdb;

        // Get last audit
        $last_audit = $wpdb->get_row(
            "SELECT * FROM {$wpdb->prefix}jeiseo_audits ORDER BY audit_date DESC LIMIT 1"
        );

        // Get post counts
        $total_posts = wp_count_posts( 'post' )->publish;
        $total_pages = wp_count_posts( 'page' )->publish;

        // Get images without alt
        $images_no_alt = count( JeiSEO_Helpers::get_images_without_alt() );

        // Get posts without meta description
        $posts_no_meta = $this->count_posts_without_meta();

        return array(
            'score'          => $last_audit ? (int) $last_audit->score : 0,
            'issues'         => $last_audit ? (int) $last_audit->issues_count : 0,
            'fixed'          => $last_audit ? (int) $last_audit->fixed_count : 0,
            'last_audit'     => $last_audit ? $last_audit->audit_date : null,
            'total_posts'    => (int) $total_posts,
            'total_pages'    => (int) $total_pages,
            'images_no_alt'  => $images_no_alt,
            'posts_no_meta'  => $posts_no_meta,
            'has_sitemap'    => JeiSEO_Helpers::sitemap_exists(),
            'has_robots'     => JeiSEO_Helpers::robots_exists(),
            'has_ssl'        => JeiSEO_Helpers::has_ssl(),
            'is_pro'         => jeiseo()->is_pro(),
            'free_audits'    => jeiseo()->get_free_quota( 'audit' ),
            'free_content'   => jeiseo()->get_free_quota( 'content' ),
        );
    }

    /**
     * Count posts without meta description
     */
    private function count_posts_without_meta(): int {
        $posts = get_posts(
            array(
                'post_type'      => array( 'post', 'page' ),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            )
        );

        $count = 0;
        foreach ( $posts as $post_id ) {
            if ( empty( JeiSEO_Helpers::get_meta_description( $post_id ) ) ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get stats via AJAX
     */
    public function ajax_get_stats(): void {
        check_ajax_referer( 'jeiseo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }

        wp_send_json_success( $this->get_stats() );
    }

    /**
     * Get recent activity
     */
    public function get_activity( int $limit = 10 ): array {
        global $wpdb;

        $audits = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 'audit' as type, audit_date as date, score, issues_count as details
                FROM {$wpdb->prefix}jeiseo_audits
                ORDER BY audit_date DESC
                LIMIT %d",
                $limit
            ),
            ARRAY_A
        );

        $content = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 'content' as type, created_at as date, content_type, status as details
                FROM {$wpdb->prefix}jeiseo_content
                ORDER BY created_at DESC
                LIMIT %d",
                $limit
            ),
            ARRAY_A
        );

        $activity = array_merge( $audits, $content );

        usort(
            $activity,
            function ( $a, $b ) {
                return strtotime( $b['date'] ) - strtotime( $a['date'] );
            }
        );

        return array_slice( $activity, 0, $limit );
    }
}
