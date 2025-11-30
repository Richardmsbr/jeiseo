<?php
/**
 * Audit view
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

$jeiseo_is_pro = jeiseo()->is_pro();
$jeiseo_free_audits = jeiseo()->get_free_quota( 'audit' );
?>

<div class="wrap jeiseo-wrap">
    <h1><?php esc_html_e( 'SEO Audit', 'jeiseo-ai-marketing-automation' ); ?></h1>

    <div class="jeiseo-audit-container">
        <div class="jeiseo-card">
            <div class="jeiseo-audit-header">
                <h2><?php esc_html_e( 'Run SEO Audit', 'jeiseo-ai-marketing-automation' ); ?></h2>
                <?php if ( ! $jeiseo_is_pro ) : ?>
                    <span class="jeiseo-quota">
                        <?php
                        printf(
                            /* translators: %d: remaining audits */
                            esc_html__( '%d audits remaining this month', 'jeiseo-ai-marketing-automation' ),
                            intval( $jeiseo_free_audits )
                        );
                        ?>
                    </span>
                <?php endif; ?>
            </div>

            <p><?php esc_html_e( 'Analyze your site for SEO issues including meta tags, headings, images, and more.', 'jeiseo-ai-marketing-automation' ); ?></p>

            <button id="jeiseo-run-audit" class="button button-primary button-hero" <?php disabled( ! $jeiseo_is_pro && $jeiseo_free_audits <= 0 ); ?>>
                <span class="dashicons dashicons-search"></span>
                <?php esc_html_e( 'Start Audit', 'jeiseo-ai-marketing-automation' ); ?>
            </button>

            <div id="jeiseo-audit-progress" class="jeiseo-progress" style="display:none;">
                <div class="jeiseo-progress-bar"></div>
                <span class="jeiseo-progress-text"><?php esc_html_e( 'Analyzing...', 'jeiseo-ai-marketing-automation' ); ?></span>
            </div>
        </div>

        <div id="jeiseo-audit-results" class="jeiseo-card" style="display:none;">
            <div class="jeiseo-results-header">
                <div class="jeiseo-score-mini">
                    <span id="jeiseo-result-score">0</span>/100
                </div>
                <div class="jeiseo-results-summary">
                    <span id="jeiseo-result-issues">0</span> <?php esc_html_e( 'issues found', 'jeiseo-ai-marketing-automation' ); ?>
                </div>
                <?php if ( $jeiseo_is_pro ) : ?>
                    <button id="jeiseo-fix-all" class="button button-primary">
                        <?php esc_html_e( 'Fix All with AI', 'jeiseo-ai-marketing-automation' ); ?>
                    </button>
                <?php else : ?>
                    <a href="https://jeiseo.com/pro" target="_blank" class="button">
                        <?php esc_html_e( 'Upgrade to Fix All', 'jeiseo-ai-marketing-automation' ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div id="jeiseo-issues-list" class="jeiseo-issues"></div>
        </div>
    </div>
</div>
