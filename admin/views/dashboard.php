<?php
/**
 * Dashboard view
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

$dashboard = new JeiSEO_Dashboard();
$stats = $dashboard->get_stats();
$score_color = JeiSEO_Helpers::score_color( $stats['score'] );
$score_label = JeiSEO_Helpers::score_label( $stats['score'] );
?>

<div class="wrap jeiseo-wrap">
    <h1 class="jeiseo-title">
        JeiSEO
        <?php if ( ! $stats['is_pro'] ) : ?>
            <a href="https://jeiseo.com/pro" target="_blank" class="jeiseo-badge-pro">
                <?php esc_html_e( 'Upgrade to PRO', 'jeiseo' ); ?>
            </a>
        <?php else : ?>
            <span class="jeiseo-badge-active"><?php esc_html_e( 'PRO', 'jeiseo' ); ?></span>
        <?php endif; ?>
    </h1>

    <div class="jeiseo-dashboard">
        <!-- Score Card -->
        <div class="jeiseo-card jeiseo-score-card">
            <div class="jeiseo-score-circle" style="--score-color: <?php echo esc_attr( $score_color ); ?>">
                <span class="jeiseo-score-value"><?php echo esc_html( $stats['score'] ); ?></span>
                <span class="jeiseo-score-max">/100</span>
            </div>
            <div class="jeiseo-score-info">
                <h2><?php esc_html_e( 'SEO Health Score', 'jeiseo' ); ?></h2>
                <p class="jeiseo-score-label" style="color: <?php echo esc_attr( $score_color ); ?>">
                    <?php echo esc_html( $score_label ); ?>
                </p>
                <?php if ( $stats['last_audit'] ) : ?>
                    <p class="jeiseo-last-audit">
                        <?php
                        printf(
                            /* translators: %s: time ago */
                            esc_html__( 'Last audit: %s', 'jeiseo' ),
                            esc_html( human_time_diff( strtotime( $stats['last_audit'] ) ) . ' ago' )
                        );
                        ?>
                    </p>
                <?php endif; ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-audit' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Run New Audit', 'jeiseo' ); ?>
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="jeiseo-card">
            <h3><?php esc_html_e( 'Quick Stats', 'jeiseo' ); ?></h3>
            <div class="jeiseo-stats-grid">
                <div class="jeiseo-stat">
                    <span class="jeiseo-stat-value"><?php echo esc_html( $stats['issues'] ); ?></span>
                    <span class="jeiseo-stat-label"><?php esc_html_e( 'Issues Found', 'jeiseo' ); ?></span>
                </div>
                <div class="jeiseo-stat">
                    <span class="jeiseo-stat-value"><?php echo esc_html( $stats['fixed'] ); ?></span>
                    <span class="jeiseo-stat-label"><?php esc_html_e( 'Issues Fixed', 'jeiseo' ); ?></span>
                </div>
                <div class="jeiseo-stat">
                    <span class="jeiseo-stat-value"><?php echo esc_html( $stats['total_posts'] + $stats['total_pages'] ); ?></span>
                    <span class="jeiseo-stat-label"><?php esc_html_e( 'Total Content', 'jeiseo' ); ?></span>
                </div>
                <div class="jeiseo-stat">
                    <span class="jeiseo-stat-value"><?php echo esc_html( $stats['images_no_alt'] ); ?></span>
                    <span class="jeiseo-stat-label"><?php esc_html_e( 'Images No Alt', 'jeiseo' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Technical Status -->
        <div class="jeiseo-card">
            <h3><?php esc_html_e( 'Technical Status', 'jeiseo' ); ?></h3>
            <ul class="jeiseo-checklist">
                <li class="<?php echo $stats['has_ssl'] ? 'success' : 'error'; ?>">
                    <?php esc_html_e( 'SSL Certificate', 'jeiseo' ); ?>
                </li>
                <li class="<?php echo $stats['has_sitemap'] ? 'success' : 'warning'; ?>">
                    <?php esc_html_e( 'XML Sitemap', 'jeiseo' ); ?>
                </li>
                <li class="<?php echo $stats['has_robots'] ? 'success' : 'warning'; ?>">
                    <?php esc_html_e( 'Robots.txt', 'jeiseo' ); ?>
                </li>
            </ul>
        </div>

        <!-- Usage (Free only) -->
        <?php if ( ! $stats['is_pro'] ) : ?>
        <div class="jeiseo-card jeiseo-usage-card">
            <h3><?php esc_html_e( 'Free Plan Usage', 'jeiseo' ); ?></h3>
            <div class="jeiseo-usage">
                <div class="jeiseo-usage-item">
                    <span class="jeiseo-usage-label"><?php esc_html_e( 'Audits this month', 'jeiseo' ); ?></span>
                    <span class="jeiseo-usage-value">
                        <?php echo esc_html( 4 - $stats['free_audits'] ); ?>/4
                    </span>
                </div>
                <div class="jeiseo-usage-item">
                    <span class="jeiseo-usage-label"><?php esc_html_e( 'AI Content this month', 'jeiseo' ); ?></span>
                    <span class="jeiseo-usage-value">
                        <?php echo esc_html( 3 - $stats['free_content'] ); ?>/3
                    </span>
                </div>
            </div>
            <a href="https://jeiseo.com/pro" target="_blank" class="button">
                <?php esc_html_e( 'Upgrade for Unlimited', 'jeiseo' ); ?>
            </a>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="jeiseo-card">
            <h3><?php esc_html_e( 'Quick Actions', 'jeiseo' ); ?></h3>
            <div class="jeiseo-actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-audit' ) ); ?>" class="jeiseo-action">
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e( 'Run SEO Audit', 'jeiseo' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-content' ) ); ?>" class="jeiseo-action">
                    <span class="dashicons dashicons-edit"></span>
                    <?php esc_html_e( 'Generate Content', 'jeiseo' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jeiseo-settings' ) ); ?>" class="jeiseo-action">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e( 'Settings', 'jeiseo' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>
