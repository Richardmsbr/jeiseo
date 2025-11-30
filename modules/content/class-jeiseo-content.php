<?php
/**
 * AI Content Module
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

/**
 * Content class - AI content generation
 */
class JeiSEO_Content {

    /**
     * API instance
     */
    private ?JeiSEO_API $api = null;

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_jeiseo_generate_content', array( $this, 'ajax_generate' ) );
        add_action( 'wp_ajax_jeiseo_save_content', array( $this, 'ajax_save' ) );
    }

    /**
     * Get API instance
     */
    private function get_api(): JeiSEO_API {
        if ( null === $this->api ) {
            $this->api = new JeiSEO_API();
        }
        return $this->api;
    }

    /**
     * Generate content via AJAX
     */
    public function ajax_generate(): void {
        check_ajax_referer( 'jeiseo_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        // Check quota for free users
        if ( ! jeiseo()->is_pro() && jeiseo()->get_free_quota( 'content' ) <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Free content limit reached. Upgrade to PRO for unlimited content.', 'jeiseo-ai-marketing-automation' ),
                    'upgrade' => true,
                )
            );
        }

        $keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
        $type = isset( $_POST['content_type'] ) ? sanitize_text_field( wp_unslash( $_POST['content_type'] ) ) : 'blog_post';

        if ( empty( $keyword ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a keyword or topic.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        $api = $this->get_api();

        if ( ! $api->is_configured() ) {
            wp_send_json_error( array( 'message' => __( 'API key not configured. Go to Settings.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        $options = array(
            'length'   => isset( $_POST['length'] ) ? sanitize_text_field( wp_unslash( $_POST['length'] ) ) : 'medium',
            'tone'     => isset( $_POST['tone'] ) ? sanitize_text_field( wp_unslash( $_POST['tone'] ) ) : 'professional',
            'language' => get_locale(),
        );

        $result = $api->generate_blog_post( $keyword, $options );

        if ( ! $result['success'] ) {
            wp_send_json_error( array( 'message' => $result['error'] ) );
        }

        // Save to database
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'jeiseo_content',
            array(
                'content_type'      => $type,
                'prompt'            => $keyword,
                'generated_content' => $result['content'],
                'created_at'        => current_time( 'mysql' ),
                'status'            => 'draft',
            ),
            array( '%s', '%s', '%s', '%s', '%s' )
        );

        $content_id = $wpdb->insert_id;

        // Increment usage for free users
        if ( ! jeiseo()->is_pro() ) {
            jeiseo()->increment_usage( 'content' );
        }

        wp_send_json_success(
            array(
                'content_id' => $content_id,
                'content'    => $result['content'],
                'remaining'  => jeiseo()->is_pro() ? -1 : jeiseo()->get_free_quota( 'content' ),
            )
        );
    }

    /**
     * Save content as post via AJAX
     */
    public function ajax_save(): void {
        check_ajax_referer( 'jeiseo_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        $content_id = isset( $_POST['content_id'] ) ? intval( $_POST['content_id'] ) : 0;
        $title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
        $status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'draft';

        if ( empty( $title ) || empty( $content ) ) {
            wp_send_json_error( array( 'message' => __( 'Title and content are required.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        // Create post
        $post_id = wp_insert_post(
            array(
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => $status,
                'post_type'    => 'post',
                'post_author'  => get_current_user_id(),
            )
        );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
        }

        // Update content record
        if ( $content_id ) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'jeiseo_content',
                array(
                    'post_id' => $post_id,
                    'status'  => 'published',
                ),
                array( 'id' => $content_id ),
                array( '%d', '%s' ),
                array( '%d' )
            );
        }

        wp_send_json_success(
            array(
                'post_id'  => $post_id,
                'edit_url' => get_edit_post_link( $post_id, 'raw' ),
                'view_url' => get_permalink( $post_id ),
                'message'  => __( 'Post created successfully!', 'jeiseo-ai-marketing-automation' ),
            )
        );
    }

    /**
     * Get content history
     */
    public function get_history( int $limit = 20 ): array {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}jeiseo_content ORDER BY created_at DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
    }
}
