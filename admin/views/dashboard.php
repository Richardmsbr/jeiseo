<?php
/**
 * Dashboard view
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

$jeiseo_dashboard = new JeiSEO_Dashboard();
$jeiseo_stats = $jeiseo_dashboard->get_stats();
$jeiseo_score_color = JeiSEO_Helpers::score_color( $jeiseo_stats['score'] );
$jeiseo_score_label = JeiSEO_Helpers::score_label( $jeiseo_stats['score'] );
?>

<div class="wrap jeiseo-wrap">
    <h1 class="jeiseo-title">
        JeiSEO
        <?php if ( ! $jeiseo_stats['is_pro'] ) : ?>
            <a href="https://jeiseo.com/pro" target="_blank" class="jeiseo-badge-pro">
                <?php esc_html_e( 'Upgrade to PRO', 'jeiseo-ai-marketing-automation' ); ?>
            </a>
        <?php else : ?>
            <span class="jeiseo-badge-active"><?php esc_html_e( 'PRO', 'jeiseo-ai-marketing-automation' ); ?></span>
        <?php endif; ?>
    </h1>

    <div class="jeiseo-dashboard">
        <!-- Score Card -->
        <div class="jeiseo-card jeiseo-score-card">
            <div class="jeiseo-score-circle" style="--score-color: <?php echo esc_attr( $jeiseo_score_color ); ?>">
                <span class="jeiseo-score-value"><?php echo esc_html( $jeiseo_stats['score'] ); ?></span>
                <span class="jeiseo-score-max">/100</span>
            </div>
            <div class="jeiseo-score-info">
                <h2><?php esc_html_e( 'SEO Health Score', 'jeiseo-ai-marketing-automation' ); ?></h2>
                <p class="jeiseo-score-label" style="color: <?php echo esc_attr( $jeiseo_score_color ); ?>">
                    <?php echo esc_html( $jeiseo_score_label ); ?>
                </p>
                <?php if ( $jeiseo_stats['last_audit'] ) : ?>
                    <p class="jeiseo-last-audit">
                        <?php
                        printf(
                            /* translators: %s: time ago */
                            esc_html__( 'Last audit: %s', 'jeiseo-ai-marketing-automation' ),
                            esc_html( human_time_diff( strtotime( $jeiseo_stats['last_audit'] ) ) . ' ago' )
                        );
                        ?>
                    </p>
                <?php endif; ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-audit' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Run New Audit', 'jeiseo-ai-marketing-automation' ); ?>
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="jeiseo-card">
            <h3><?php esc_html_e( 'Quick Stats', 'jeiseo-ai-marketing-automation' ); ?></h3>
            <div class="jeiseo-stats-grid">
                <div class="jeiseo-stat">
                    <span class="jeiseo-stat-value"><?php echo esc_html( $jeiseo_stats['issues'] ); ?></span>
                    <span class="jeiseo-stat-label"><?php esc_html_e( 'Issues Found', 'jeiseo-ai-marketing-automation' ); ?></span>
                </div>
                <div class="jeiseo-stat">
                    <span class="jeiseo-stat-value"><?php echo esc_html( $jeiseo_stats['fixed'] ); ?></span>
                    <span class="jeiseo-stat-label"><?php esc_html_e( 'Issues Fixed', 'jeiseo-ai-marketing-automation' ); ?></span>
                </div>
                <div class="jeiseo-stat">
                    <span class="jeiseo-stat-value"><?php echo esc_html( $jeiseo_stats['total_posts'] + $jeiseo_stats['total_pages'] ); ?></span>
                    <span class="jeiseo-stat-label"><?php esc_html_e( 'Total Content', 'jeiseo-ai-marketing-automation' ); ?></span>
                </div>
                <div class="jeiseo-stat">
                    <span class="jeiseo-stat-value"><?php echo esc_html( $jeiseo_stats['images_no_alt'] ); ?></span>
                    <span class="jeiseo-stat-label"><?php esc_html_e( 'Images No Alt', 'jeiseo-ai-marketing-automation' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Technical Status -->
        <div class="jeiseo-card">
            <h3><?php esc_html_e( 'Technical Status', 'jeiseo-ai-marketing-automation' ); ?></h3>
            <ul class="jeiseo-checklist">
                <li class="<?php echo $jeiseo_stats['has_ssl'] ? 'success' : 'error'; ?>">
                    <?php esc_html_e( 'SSL Certificate', 'jeiseo-ai-marketing-automation' ); ?>
                </li>
                <li class="<?php echo $jeiseo_stats['has_sitemap'] ? 'success' : 'warning'; ?>">
                    <?php esc_html_e( 'XML Sitemap', 'jeiseo-ai-marketing-automation' ); ?>
                </li>
                <li class="<?php echo $jeiseo_stats['has_robots'] ? 'success' : 'warning'; ?>">
                    <?php esc_html_e( 'Robots.txt', 'jeiseo-ai-marketing-automation' ); ?>
                </li>
            </ul>
        </div>

        <!-- Usage (Free only) -->
        <?php if ( ! $jeiseo_stats['is_pro'] ) : ?>
        <div class="jeiseo-card jeiseo-usage-card">
            <h3><?php esc_html_e( 'Free Plan Usage', 'jeiseo-ai-marketing-automation' ); ?></h3>
            <div class="jeiseo-usage">
                <div class="jeiseo-usage-item">
                    <span class="jeiseo-usage-label"><?php esc_html_e( 'Audits this month', 'jeiseo-ai-marketing-automation' ); ?></span>
                    <span class="jeiseo-usage-value">
                        <?php echo esc_html( 4 - $jeiseo_stats['free_audits'] ); ?>/4
                    </span>
                </div>
                <div class="jeiseo-usage-item">
                    <span class="jeiseo-usage-label"><?php esc_html_e( 'AI Content this month', 'jeiseo-ai-marketing-automation' ); ?></span>
                    <span class="jeiseo-usage-value">
                        <?php echo esc_html( 3 - $jeiseo_stats['free_content'] ); ?>/3
                    </span>
                </div>
            </div>
            <a href="https://jeiseo.com/pro" target="_blank" class="button">
                <?php esc_html_e( 'Upgrade for Unlimited', 'jeiseo-ai-marketing-automation' ); ?>
            </a>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="jeiseo-card">
            <h3><?php esc_html_e( 'Quick Actions', 'jeiseo-ai-marketing-automation' ); ?></h3>
            <div class="jeiseo-actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-audit' ) ); ?>" class="jeiseo-action">
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e( 'Run SEO Audit', 'jeiseo-ai-marketing-automation' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-content' ) ); ?>" class="jeiseo-action">
                    <span class="dashicons dashicons-edit"></span>
                    <?php esc_html_e( 'Generate Content', 'jeiseo-ai-marketing-automation' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-settings' ) ); ?>" class="jeiseo-action">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e( 'Settings', 'jeiseo-ai-marketing-automation' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>
