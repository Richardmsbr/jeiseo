<?php
/**
 * SEO Audit Module
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

/**
 * Audit class - performs SEO checks
 */
class JeiSEO_Audit {

    /**
     * Audit checks
     */
    private array $checks = array();

    /**
     * Issues found
     */
    private array $issues = array();

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_jeiseo_run_audit', array( $this, 'ajax_run_audit' ) );
        add_action( 'wp_ajax_jeiseo_fix_issues', array( $this, 'ajax_fix_issues' ) );
        add_action( 'jeiseo_daily_audit', array( $this, 'scheduled_audit' ) );
    }

    /**
     * Run full SEO audit
     */
    public function run(): array {
        $this->issues = array();

        // Technical SEO checks
        $this->check_ssl();
        $this->check_sitemap();
        $this->check_robots();
        $this->check_permalink();

        // Content checks
        $this->check_titles();
        $this->check_meta_descriptions();
        $this->check_headings();
        $this->check_images_alt();
        $this->check_internal_links();

        // Performance checks
        $this->check_large_images();

        // Calculate score
        $score = $this->calculate_score();

        // Save audit
        $this->save_audit( $score );

        return array(
            'score'  => $score,
            'issues' => $this->issues,
            'total'  => count( $this->issues ),
        );
    }

    /**
     * Check SSL
     */
    private function check_ssl(): void {
        if ( ! is_ssl() ) {
            $this->issues[] = array(
                'type'     => 'critical',
                'category' => 'security',
                'title'    => __( 'No SSL certificate', 'jeiseo-ai-marketing-automation' ),
                'message'  => __( 'Your site is not using HTTPS. This affects SEO and security.', 'jeiseo-ai-marketing-automation' ),
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }
    }

    /**
     * Check sitemap
     */
    private function check_sitemap(): void {
        if ( ! JeiSEO_Helpers::sitemap_exists() ) {
            $this->issues[] = array(
                'type'     => 'warning',
                'category' => 'technical',
                'title'    => __( 'No sitemap found', 'jeiseo-ai-marketing-automation' ),
                'message'  => __( 'XML Sitemap helps search engines discover your pages.', 'jeiseo-ai-marketing-automation' ),
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }
    }

    /**
     * Check robots.txt
     */
    private function check_robots(): void {
        if ( ! JeiSEO_Helpers::robots_exists() ) {
            $this->issues[] = array(
                'type'     => 'info',
                'category' => 'technical',
                'title'    => __( 'No robots.txt', 'jeiseo-ai-marketing-automation' ),
                'message'  => __( 'Robots.txt helps control search engine crawling.', 'jeiseo-ai-marketing-automation' ),
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }
    }

    /**
     * Check permalink structure
     */
    private function check_permalink(): void {
        $structure = get_option( 'permalink_structure' );

        if ( empty( $structure ) ) {
            $this->issues[] = array(
                'type'     => 'critical',
                'category' => 'technical',
                'title'    => __( 'Plain permalinks', 'jeiseo-ai-marketing-automation' ),
                'message'  => __( 'Using plain permalinks hurts SEO. Use post name structure.', 'jeiseo-ai-marketing-automation' ),
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }
    }

    /**
     * Check page titles
     */
    private function check_titles(): void {
        $posts = get_posts(
            array(
                'post_type'      => array( 'post', 'page' ),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        $short_titles = array();
        $long_titles = array();
        $duplicate_titles = array();
        $title_count = array();

        foreach ( $posts as $post ) {
            $title = $post->post_title;
            $length = mb_strlen( $title );

            // Check length
            if ( $length < 30 ) {
                $short_titles[] = $post->ID;
            } elseif ( $length > 60 ) {
                $long_titles[] = $post->ID;
            }

            // Check duplicates
            $title_lower = mb_strtolower( $title );
            if ( ! isset( $title_count[ $title_lower ] ) ) {
                $title_count[ $title_lower ] = array();
            }
            $title_count[ $title_lower ][] = $post->ID;
        }

        // Find duplicates
        foreach ( $title_count as $title => $ids ) {
            if ( count( $ids ) > 1 ) {
                $duplicate_titles = array_merge( $duplicate_titles, $ids );
            }
        }

        if ( ! empty( $short_titles ) ) {
            $this->issues[] = array(
                'type'     => 'warning',
                'category' => 'content',
                /* translators: %d: number of pages */
                'title'    => sprintf( __( '%d pages with short titles', 'jeiseo-ai-marketing-automation' ), count( $short_titles ) ),
                'message'  => __( 'Titles under 30 characters may not be descriptive enough.', 'jeiseo-ai-marketing-automation' ),
                'posts'    => $short_titles,
                'fix'      => 'ai',
                'fixable'  => true,
            );
        }

        if ( ! empty( $long_titles ) ) {
            $this->issues[] = array(
                'type'     => 'warning',
                'category' => 'content',
                /* translators: %d: number of pages */
                'title'    => sprintf( __( '%d pages with long titles', 'jeiseo-ai-marketing-automation' ), count( $long_titles ) ),
                'message'  => __( 'Titles over 60 characters may be truncated in search results.', 'jeiseo-ai-marketing-automation' ),
                'posts'    => $long_titles,
                'fix'      => 'ai',
                'fixable'  => true,
            );
        }

        if ( ! empty( $duplicate_titles ) ) {
            $this->issues[] = array(
                'type'     => 'critical',
                'category' => 'content',
                /* translators: %d: number of pages */
                'title'    => sprintf( __( '%d pages with duplicate titles', 'jeiseo-ai-marketing-automation' ), count( $duplicate_titles ) ),
                'message'  => __( 'Duplicate titles confuse search engines and users.', 'jeiseo-ai-marketing-automation' ),
                'posts'    => $duplicate_titles,
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }
    }

    /**
     * Check meta descriptions
     */
    private function check_meta_descriptions(): void {
        $posts = get_posts(
            array(
                'post_type'      => array( 'post', 'page' ),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        $missing = array();
        $short = array();
        $long = array();

        foreach ( $posts as $post ) {
            $meta = JeiSEO_Helpers::get_meta_description( $post->ID );

            if ( empty( $meta ) ) {
                $missing[] = $post->ID;
            } else {
                $length = mb_strlen( $meta );
                if ( $length < 120 ) {
                    $short[] = $post->ID;
                } elseif ( $length > 160 ) {
                    $long[] = $post->ID;
                }
            }
        }

        if ( ! empty( $missing ) ) {
            $this->issues[] = array(
                'type'     => 'critical',
                'category' => 'content',
                /* translators: %d: number of pages */
                'title'    => sprintf( __( '%d pages without meta description', 'jeiseo-ai-marketing-automation' ), count( $missing ) ),
                'message'  => __( 'Meta descriptions are important for click-through rates.', 'jeiseo-ai-marketing-automation' ),
                'posts'    => $missing,
                'fix'      => 'ai',
                'fixable'  => true,
            );
        }
    }

    /**
     * Check heading structure
     */
    private function check_headings(): void {
        $posts = get_posts(
            array(
                'post_type'      => array( 'post', 'page' ),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        $no_h2 = array();
        $multiple_h1 = array();

        foreach ( $posts as $post ) {
            $content = $post->post_content;

            // Check for H1 in content (shouldn't have multiple)
            preg_match_all( '/<h1[^>]*>/i', $content, $h1_matches );
            if ( count( $h1_matches[0] ) > 0 ) {
                $multiple_h1[] = $post->ID;
            }

            // Check for H2
            preg_match_all( '/<h2[^>]*>/i', $content, $h2_matches );
            if ( count( $h2_matches[0] ) === 0 && mb_strlen( $content ) > 500 ) {
                $no_h2[] = $post->ID;
            }
        }

        if ( ! empty( $no_h2 ) ) {
            $this->issues[] = array(
                'type'     => 'warning',
                'category' => 'content',
                /* translators: %d: number of posts */
                'title'    => sprintf( __( '%d long posts without H2 headings', 'jeiseo-ai-marketing-automation' ), count( $no_h2 ) ),
                'message'  => __( 'Headings help structure content and improve readability.', 'jeiseo-ai-marketing-automation' ),
                'posts'    => $no_h2,
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }

        if ( ! empty( $multiple_h1 ) ) {
            $this->issues[] = array(
                'type'     => 'warning',
                'category' => 'content',
                /* translators: %d: number of pages */
                'title'    => sprintf( __( '%d pages with H1 in content', 'jeiseo-ai-marketing-automation' ), count( $multiple_h1 ) ),
                'message'  => __( 'Avoid H1 in content - the title is already H1.', 'jeiseo-ai-marketing-automation' ),
                'posts'    => $multiple_h1,
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }
    }

    /**
     * Check images alt text
     */
    private function check_images_alt(): void {
        $images = JeiSEO_Helpers::get_images_without_alt();

        if ( ! empty( $images ) ) {
            $image_ids = array_map(
                function ( $img ) {
                    return $img->ID;
                },
                $images
            );

            $this->issues[] = array(
                'type'     => 'critical',
                'category' => 'accessibility',
                /* translators: %d: number of images */
                'title'    => sprintf( __( '%d images without alt text', 'jeiseo-ai-marketing-automation' ), count( $images ) ),
                'message'  => __( 'Alt text improves accessibility and image SEO.', 'jeiseo-ai-marketing-automation' ),
                'images'   => $image_ids,
                'fix'      => 'ai',
                'fixable'  => true,
            );
        }
    }

    /**
     * Check internal links
     */
    private function check_internal_links(): void {
        $posts = get_posts(
            array(
                'post_type'      => array( 'post', 'page' ),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            )
        );

        $no_links = array();
        $home_url = home_url();

        foreach ( $posts as $post ) {
            preg_match_all( '/href=["\']([^"\']+)["\']/i', $post->post_content, $matches );

            $internal_links = 0;
            foreach ( $matches[1] as $url ) {
                if ( strpos( $url, $home_url ) !== false || strpos( $url, '/' ) === 0 ) {
                    $internal_links++;
                }
            }

            if ( $internal_links === 0 && mb_strlen( $post->post_content ) > 300 ) {
                $no_links[] = $post->ID;
            }
        }

        if ( ! empty( $no_links ) ) {
            $this->issues[] = array(
                'type'     => 'warning',
                'category' => 'content',
                /* translators: %d: number of pages */
                'title'    => sprintf( __( '%d pages without internal links', 'jeiseo-ai-marketing-automation' ), count( $no_links ) ),
                'message'  => __( 'Internal links help users and search engines navigate your site.', 'jeiseo-ai-marketing-automation' ),
                'posts'    => $no_links,
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }
    }

    /**
     * Check for large images
     */
    private function check_large_images(): void {
        global $wpdb;

        $images = $wpdb->get_results(
            "SELECT ID, guid FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
        );

        $large_images = array();

        foreach ( $images as $image ) {
            $file = get_attached_file( $image->ID );
            if ( $file && file_exists( $file ) ) {
                $size = filesize( $file );
                if ( $size > 500000 ) { // > 500KB
                    $large_images[] = $image->ID;
                }
            }
        }

        if ( ! empty( $large_images ) ) {
            $this->issues[] = array(
                'type'     => 'warning',
                'category' => 'performance',
                /* translators: %d: number of images */
                'title'    => sprintf( __( '%d images over 500KB', 'jeiseo-ai-marketing-automation' ), count( $large_images ) ),
                'message'  => __( 'Large images slow down your site. Consider compressing them.', 'jeiseo-ai-marketing-automation' ),
                'images'   => $large_images,
                'fix'      => 'manual',
                'fixable'  => false,
            );
        }
    }

    /**
     * Calculate SEO score
     */
    private function calculate_score(): int {
        $base_score = 100;

        foreach ( $this->issues as $issue ) {
            switch ( $issue['type'] ) {
                case 'critical':
                    $base_score -= 15;
                    break;
                case 'warning':
                    $base_score -= 5;
                    break;
                case 'info':
                    $base_score -= 2;
                    break;
            }
        }

        return max( 0, min( 100, $base_score ) );
    }

    /**
     * Save audit to database
     */
    private function save_audit( int $score ): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'jeiseo_audits',
            array(
                'audit_date'   => current_time( 'mysql' ),
                'score'        => $score,
                'issues_count' => count( $this->issues ),
                'issues_data'  => wp_json_encode( $this->issues ),
                'fixed_count'  => 0,
            ),
            array( '%s', '%d', '%d', '%s', '%d' )
        );
    }

    /**
     * Run audit via AJAX
     */
    public function ajax_run_audit(): void {
        check_ajax_referer( 'jeiseo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        // Check quota for free users
        if ( ! jeiseo()->is_pro() && jeiseo()->get_free_quota( 'audit' ) <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Free audit limit reached. Upgrade to PRO for unlimited audits.', 'jeiseo-ai-marketing-automation' ),
                    'upgrade' => true,
                )
            );
        }

        $result = $this->run();

        // Increment usage for free users
        if ( ! jeiseo()->is_pro() ) {
            jeiseo()->increment_usage( 'audit' );
        }

        wp_send_json_success( $result );
    }

    /**
     * Fix issues via AJAX (PRO only)
     */
    public function ajax_fix_issues(): void {
        check_ajax_referer( 'jeiseo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        if ( ! jeiseo()->is_pro() ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Auto-fix is a PRO feature.', 'jeiseo-ai-marketing-automation' ),
                    'upgrade' => true,
                )
            );
        }

        $issue_type = isset( $_POST['issue_type'] ) ? sanitize_text_field( wp_unslash( $_POST['issue_type'] ) ) : '';
        $post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', (array) $_POST['post_ids'] ) : array();

        $fixed = 0;
        $api = new JeiSEO_API();

        switch ( $issue_type ) {
            case 'meta_description':
                foreach ( $post_ids as $post_id ) {
                    $post = get_post( $post_id );
                    if ( $post ) {
                        $result = $api->generate_meta_description( $post->post_title, $post->post_content );
                        if ( $result['success'] ) {
                            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $result['content'] );
                            $fixed++;
                        }
                    }
                }
                break;

            case 'image_alt':
                $image_ids = isset( $_POST['image_ids'] ) ? array_map( 'intval', (array) $_POST['image_ids'] ) : array();
                foreach ( $image_ids as $image_id ) {
                    $attachment = get_post( $image_id );
                    if ( $attachment ) {
                        $result = $api->generate_alt_text( wp_get_attachment_url( $image_id ), $attachment->post_title );
                        if ( $result['success'] ) {
                            update_post_meta( $image_id, '_wp_attachment_image_alt', $result['content'] );
                            $fixed++;
                        }
                    }
                }
                break;
        }

        wp_send_json_success(
            array(
                'fixed'   => $fixed,
                /* translators: %d: number of fixed issues */
                'message' => sprintf( __( 'Fixed %d issues.', 'jeiseo-ai-marketing-automation' ), $fixed ),
            )
        );
    }

    /**
     * Scheduled daily audit
     */
    public function scheduled_audit(): void {
        if ( get_option( 'jeiseo_auto_audit', true ) ) {
            $this->run();
        }
    }
}
