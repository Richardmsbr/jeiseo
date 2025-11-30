<?php
/**
 * Helper functions
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helper class
 */
class JeiSEO_Helpers {

    /**
     * Get site health score color
     */
    public static function score_color( int $score ): string {
        if ( $score >= 80 ) {
            return '#22c55e'; // Green
        } elseif ( $score >= 50 ) {
            return '#eab308'; // Yellow
        }
        return '#ef4444'; // Red
    }

    /**
     * Get score label
     */
    public static function score_label( int $score ): string {
        if ( $score >= 80 ) {
            return __( 'Good', 'jeiseo-ai-marketing-automation' );
        } elseif ( $score >= 50 ) {
            return __( 'Needs Improvement', 'jeiseo-ai-marketing-automation' );
        }
        return __( 'Poor', 'jeiseo-ai-marketing-automation' );
    }

    /**
     * Format number with K/M suffix
     */
    public static function format_number( int $number ): string {
        if ( $number >= 1000000 ) {
            return round( $number / 1000000, 1 ) . 'M';
        } elseif ( $number >= 1000 ) {
            return round( $number / 1000, 1 ) . 'K';
        }
        return (string) $number;
    }

    /**
     * Get all public posts
     */
    public static function get_public_posts( int $limit = -1 ): array {
        return get_posts(
            array(
                'post_type'      => array( 'post', 'page' ),
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
            )
        );
    }

    /**
     * Get post meta description
     */
    public static function get_meta_description( int $post_id ): string {
        // Check Yoast
        $yoast = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
        if ( $yoast ) {
            return $yoast;
        }

        // Check Rank Math
        $rank = get_post_meta( $post_id, 'rank_math_description', true );
        if ( $rank ) {
            return $rank;
        }

        // Check AIOSEO
        $aio = get_post_meta( $post_id, '_aioseo_description', true );
        if ( $aio ) {
            return $aio;
        }

        return '';
    }

    /**
     * Get post focus keyword
     */
    public static function get_focus_keyword( int $post_id ): string {
        // Check Yoast
        $yoast = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
        if ( $yoast ) {
            return $yoast;
        }

        // Check Rank Math
        $rank = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
        if ( $rank ) {
            return $rank;
        }

        return '';
    }

    /**
     * Check if image has alt text
     */
    public static function image_has_alt( int $attachment_id ): bool {
        $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
        return ! empty( $alt );
    }

    /**
     * Get all images without alt
     */
    public static function get_images_without_alt(): array {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe query, no user input
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, p.post_title, p.guid
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
                WHERE p.post_type = %s
                AND p.post_mime_type LIKE %s
                AND (pm.meta_value IS NULL OR pm.meta_value = '')",
                '_wp_attachment_image_alt',
                'attachment',
                'image/%'
            )
        );
    }

    /**
     * Get site speed estimate
     */
    public static function estimate_site_speed(): array {
        $start = microtime( true );
        wp_remote_get( home_url( '/' ), array( 'timeout' => 10 ) );
        $time = microtime( true ) - $start;

        return array(
            'time'   => round( $time, 2 ),
            'status' => $time < 1 ? 'good' : ( $time < 3 ? 'moderate' : 'slow' ),
        );
    }

    /**
     * Check if sitemap exists
     */
    public static function sitemap_exists(): bool {
        $sitemap_urls = array(
            home_url( '/sitemap.xml' ),
            home_url( '/sitemap_index.xml' ),
            home_url( '/wp-sitemap.xml' ),
        );

        foreach ( $sitemap_urls as $url ) {
            $response = wp_remote_head( $url, array( 'timeout' => 5 ) );
            if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if robots.txt exists
     */
    public static function robots_exists(): bool {
        $response = wp_remote_head( home_url( '/robots.txt' ), array( 'timeout' => 5 ) );
        return ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );
    }

    /**
     * Check SSL status
     */
    public static function has_ssl(): bool {
        return is_ssl();
    }

    /**
     * Sanitize and validate URL
     */
    public static function sanitize_url( string $url ): string {
        return esc_url_raw( $url );
    }
}
