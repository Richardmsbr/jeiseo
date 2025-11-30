<?php
/**
 * License Manager
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

/**
 * License management class
 */
class JeiSEO_License {

    /**
     * License key
     */
    private string $license_key = '';

    /**
     * License status
     */
    private string $status = 'free';

    /**
     * Constructor
     */
    public function __construct() {
        $this->license_key = get_option( 'jeiseo_license_key', '' );
        $this->status = get_option( 'jeiseo_license_status', 'free' );

        add_action( 'wp_ajax_jeiseo_activate_license', array( $this, 'ajax_activate' ) );
        add_action( 'wp_ajax_jeiseo_deactivate_license', array( $this, 'ajax_deactivate' ) );
    }

    /**
     * Check if license is valid (PRO)
     */
    public function is_valid(): bool {
        return 'valid' === $this->status && ! empty( $this->license_key );
    }

    /**
     * Get current plan
     */
    public function get_plan(): string {
        if ( $this->is_valid() ) {
            return 'pro';
        }
        return 'free';
    }

    /**
     * Activate license via AJAX
     */
    public function ajax_activate(): void {
        check_ajax_referer( 'jeiseo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a license key.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        // Validate license with remote server
        $result = $this->validate_license( $license_key );

        if ( $result['valid'] ) {
            update_option( 'jeiseo_license_key', $license_key );
            update_option( 'jeiseo_license_status', 'valid' );
            wp_send_json_success( array( 'message' => __( 'License activated successfully!', 'jeiseo-ai-marketing-automation' ) ) );
        } else {
            wp_send_json_error( array( 'message' => $result['message'] ) );
        }
    }

    /**
     * Deactivate license via AJAX
     */
    public function ajax_deactivate(): void {
        check_ajax_referer( 'jeiseo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'jeiseo-ai-marketing-automation' ) ) );
        }

        update_option( 'jeiseo_license_key', '' );
        update_option( 'jeiseo_license_status', 'free' );

        wp_send_json_success( array( 'message' => __( 'License deactivated.', 'jeiseo-ai-marketing-automation' ) ) );
    }

    /**
     * Validate license with remote server
     */
    private function validate_license( string $license_key ): array {
        // For now, accept any key that matches pattern XXXX-XXXX-XXXX-XXXX
        // In production, this would call your license server
        if ( preg_match( '/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key ) ) {
            return array(
                'valid'   => true,
                'message' => '',
            );
        }

        return array(
            'valid'   => false,
            'message' => __( 'Invalid license key format.', 'jeiseo-ai-marketing-automation' ),
        );
    }

    /**
     * Get license key (masked)
     */
    public function get_masked_key(): string {
        if ( empty( $this->license_key ) ) {
            return '';
        }

        return substr( $this->license_key, 0, 4 ) . '-****-****-' . substr( $this->license_key, -4 );
    }
}
