<?php
/**
 * Content generator view
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

$jeiseo_is_pro = jeiseo()->is_pro();
$jeiseo_free_content = jeiseo()->get_free_quota( 'content' );
$jeiseo_api = new JeiSEO_API();
$jeiseo_api_configured = $jeiseo_api->is_configured();
?>

<div class="wrap jeiseo-wrap">
    <h1><?php esc_html_e( 'AI Content Generator', 'jeiseo-ai-marketing-automation' ); ?></h1>

    <?php if ( ! $jeiseo_api_configured ) : ?>
        <div class="notice notice-warning">
            <p>
                <?php esc_html_e( 'API key not configured.', 'jeiseo-ai-marketing-automation' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-settings' ) ); ?>">
                    <?php esc_html_e( 'Go to Settings', 'jeiseo-ai-marketing-automation' ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>

    <div class="jeiseo-content-container">
        <div class="jeiseo-card jeiseo-generator">
            <h2><?php esc_html_e( 'Generate Blog Post', 'jeiseo-ai-marketing-automation' ); ?></h2>

            <?php if ( ! $jeiseo_is_pro ) : ?>
                <p class="jeiseo-quota">
                    <?php
                    printf(
                        /* translators: %d: remaining content */
                        esc_html__( '%d posts remaining this month', 'jeiseo-ai-marketing-automation' ),
                        intval( $jeiseo_free_content )
                    );
                    ?>
                </p>
            <?php endif; ?>

            <form id="jeiseo-content-form">
                <div class="jeiseo-form-row">
                    <label for="jeiseo-keyword"><?php esc_html_e( 'Keyword or Topic', 'jeiseo-ai-marketing-automation' ); ?></label>
                    <input type="text" id="jeiseo-keyword" name="keyword" placeholder="<?php esc_attr_e( 'e.g., How to improve website SEO', 'jeiseo-ai-marketing-automation' ); ?>" required>
                </div>

                <div class="jeiseo-form-row jeiseo-form-inline">
                    <div>
                        <label for="jeiseo-length"><?php esc_html_e( 'Length', 'jeiseo-ai-marketing-automation' ); ?></label>
                        <select id="jeiseo-length" name="length">
                            <option value="short"><?php esc_html_e( 'Short (500-800 words)', 'jeiseo-ai-marketing-automation' ); ?></option>
                            <option value="medium" selected><?php esc_html_e( 'Medium (1000-1500 words)', 'jeiseo-ai-marketing-automation' ); ?></option>
                            <option value="long"><?php esc_html_e( 'Long (2000+ words)', 'jeiseo-ai-marketing-automation' ); ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="jeiseo-tone"><?php esc_html_e( 'Tone', 'jeiseo-ai-marketing-automation' ); ?></label>
                        <select id="jeiseo-tone" name="tone">
                            <option value="professional"><?php esc_html_e( 'Professional', 'jeiseo-ai-marketing-automation' ); ?></option>
                            <option value="casual"><?php esc_html_e( 'Casual', 'jeiseo-ai-marketing-automation' ); ?></option>
                            <option value="friendly"><?php esc_html_e( 'Friendly', 'jeiseo-ai-marketing-automation' ); ?></option>
                            <option value="authoritative"><?php esc_html_e( 'Authoritative', 'jeiseo-ai-marketing-automation' ); ?></option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="button button-primary button-hero" <?php disabled( ! $jeiseo_api_configured || ( ! $jeiseo_is_pro && $jeiseo_free_content <= 0 ) ); ?>>
                    <span class="dashicons dashicons-edit"></span>
                    <?php esc_html_e( 'Generate Content', 'jeiseo-ai-marketing-automation' ); ?>
                </button>
            </form>

            <div id="jeiseo-content-progress" class="jeiseo-progress" style="display:none;">
                <div class="jeiseo-progress-bar"></div>
                <span class="jeiseo-progress-text"><?php esc_html_e( 'Generating...', 'jeiseo-ai-marketing-automation' ); ?></span>
            </div>
        </div>

        <div id="jeiseo-content-result" class="jeiseo-card" style="display:none;">
            <div class="jeiseo-content-header">
                <h2><?php esc_html_e( 'Generated Content', 'jeiseo-ai-marketing-automation' ); ?></h2>
                <div class="jeiseo-content-actions">
                    <button id="jeiseo-copy-content" class="button">
                        <span class="dashicons dashicons-clipboard"></span>
                        <?php esc_html_e( 'Copy', 'jeiseo-ai-marketing-automation' ); ?>
                    </button>
                    <button id="jeiseo-save-draft" class="button button-primary">
                        <?php esc_html_e( 'Save as Draft', 'jeiseo-ai-marketing-automation' ); ?>
                    </button>
                </div>
            </div>

            <div class="jeiseo-form-row">
                <label for="jeiseo-post-title"><?php esc_html_e( 'Post Title', 'jeiseo-ai-marketing-automation' ); ?></label>
                <input type="text" id="jeiseo-post-title" name="title">
            </div>

            <div id="jeiseo-content-output" class="jeiseo-content-preview"></div>
            <input type="hidden" id="jeiseo-content-id" value="">
        </div>
    </div>
</div>
