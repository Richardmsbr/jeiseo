<?php
/**
 * AI API Handler
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

/**
 * API class for AI providers
 */
class JeiSEO_API {

    /**
     * API provider (openai, claude, ollama)
     */
    private string $provider;

    /**
     * API key
     */
    private string $api_key;

    /**
     * Constructor
     */
    public function __construct() {
        $this->provider = get_option( 'jeiseo_api_provider', 'openai' );
        $this->api_key = get_option( 'jeiseo_api_key', '' );
    }

    /**
     * Check if API is configured
     */
    public function is_configured(): bool {
        return ! empty( $this->api_key );
    }

    /**
     * Generate text with AI
     */
    public function generate( string $prompt, array $options = array() ): array {
        if ( ! $this->is_configured() ) {
            return array(
                'success' => false,
                'error'   => __( 'API key not configured.', 'jeiseo-ai-marketing-automation' ),
            );
        }

        $defaults = array(
            'max_tokens'  => 1000,
            'temperature' => 0.7,
        );

        $options = wp_parse_args( $options, $defaults );

        switch ( $this->provider ) {
            case 'openai':
                return $this->call_openai( $prompt, $options );
            case 'claude':
                return $this->call_claude( $prompt, $options );
            default:
                return array(
                    'success' => false,
                    'error'   => __( 'Unknown API provider.', 'jeiseo-ai-marketing-automation' ),
                );
        }
    }

    /**
     * Call OpenAI API
     */
    private function call_openai( string $prompt, array $options ): array {
        $response = wp_remote_post(
            'https://api.openai.com/v1/chat/completions',
            array(
                'timeout' => 60,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                ),
                'body'    => wp_json_encode(
                    array(
                        'model'       => 'gpt-4o-mini',
                        'messages'    => array(
                            array(
                                'role'    => 'system',
                                'content' => 'You are an expert SEO and content marketing specialist. Always respond in the same language as the user prompt.',
                            ),
                            array(
                                'role'    => 'user',
                                'content' => $prompt,
                            ),
                        ),
                        'max_tokens'  => $options['max_tokens'],
                        'temperature' => $options['temperature'],
                    )
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'error'   => $response->get_error_message(),
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['error'] ) ) {
            return array(
                'success' => false,
                'error'   => $body['error']['message'] ?? __( 'API error.', 'jeiseo-ai-marketing-automation' ),
            );
        }

        return array(
            'success' => true,
            'content' => $body['choices'][0]['message']['content'] ?? '',
        );
    }

    /**
     * Call Claude API
     */
    private function call_claude( string $prompt, array $options ): array {
        $response = wp_remote_post(
            'https://api.anthropic.com/v1/messages',
            array(
                'timeout' => 60,
                'headers' => array(
                    'x-api-key'         => $this->api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type'      => 'application/json',
                ),
                'body'    => wp_json_encode(
                    array(
                        'model'      => 'claude-3-haiku-20240307',
                        'max_tokens' => $options['max_tokens'],
                        'messages'   => array(
                            array(
                                'role'    => 'user',
                                'content' => $prompt,
                            ),
                        ),
                    )
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'error'   => $response->get_error_message(),
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['error'] ) ) {
            return array(
                'success' => false,
                'error'   => $body['error']['message'] ?? __( 'API error.', 'jeiseo-ai-marketing-automation' ),
            );
        }

        return array(
            'success' => true,
            'content' => $body['content'][0]['text'] ?? '',
        );
    }

    /**
     * Generate meta description
     */
    public function generate_meta_description( string $title, string $content ): array {
        $prompt = sprintf(
            "Generate a compelling meta description (max 155 characters) for this content:\n\nTitle: %s\n\nContent: %s\n\nRespond with only the meta description, no quotes or explanation.",
            $title,
            wp_trim_words( $content, 200 )
        );

        return $this->generate( $prompt, array( 'max_tokens' => 100 ) );
    }

    /**
     * Generate alt text for image
     */
    public function generate_alt_text( string $image_url, string $context = '' ): array {
        $prompt = sprintf(
            "Generate a descriptive alt text (max 125 characters) for an image. Context: %s\n\nRespond with only the alt text, no quotes.",
            $context ?: 'general website image'
        );

        return $this->generate( $prompt, array( 'max_tokens' => 50 ) );
    }

    /**
     * Generate blog post
     */
    public function generate_blog_post( string $keyword, array $options = array() ): array {
        $defaults = array(
            'length'   => 'medium', // short, medium, long
            'tone'     => 'professional',
            'language' => 'pt_BR',
        );

        $options = wp_parse_args( $options, $defaults );

        $length_guide = array(
            'short'  => '500-800 words',
            'medium' => '1000-1500 words',
            'long'   => '2000-2500 words',
        );

        $prompt = sprintf(
            "Write a complete SEO-optimized blog post about: %s\n\n" .
            "Requirements:\n" .
            "- Length: %s\n" .
            "- Tone: %s\n" .
            "- Language: %s\n" .
            "- Include H2 and H3 headings\n" .
            "- Include a compelling introduction\n" .
            "- Include a conclusion with call-to-action\n" .
            "- Naturally include the keyword throughout\n\n" .
            "Format the response in HTML with proper heading tags.",
            $keyword,
            $length_guide[ $options['length'] ],
            $options['tone'],
            $options['language']
        );

        return $this->generate( $prompt, array( 'max_tokens' => 4000 ) );
    }
}
