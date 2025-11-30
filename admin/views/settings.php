<?php
/**
 * Settings view
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

$is_pro = jeiseo()->is_pro();
$license = jeiseo()->license;
?>

<div class="wrap jeiseo-wrap">
    <h1><?php esc_html_e( 'JeiSEO Settings', 'jeiseo' ); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields( 'jeiseo_settings' ); ?>

        <!-- License -->
        <div class="jeiseo-card">
            <h2><?php esc_html_e( 'License', 'jeiseo' ); ?></h2>

            <?php if ( $is_pro ) : ?>
                <div class="jeiseo-license-active">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div>
                        <strong><?php esc_html_e( 'PRO License Active', 'jeiseo' ); ?></strong>
                        <p><?php echo esc_html( $license->get_masked_key() ); ?></p>
                    </div>
                    <button type="button" id="jeiseo-deactivate-license" class="button">
                        <?php esc_html_e( 'Deactivate', 'jeiseo' ); ?>
                    </button>
                </div>
            <?php else : ?>
                <div class="jeiseo-license-form">
                    <p><?php esc_html_e( 'Enter your license key to unlock PRO features.', 'jeiseo' ); ?></p>
                    <div class="jeiseo-form-inline">
                        <input type="text" id="jeiseo-license-key" placeholder="XXXX-XXXX-XXXX-XXXX">
                        <button type="button" id="jeiseo-activate-license" class="button button-primary">
                            <?php esc_html_e( 'Activate', 'jeiseo' ); ?>
                        </button>
                    </div>
                    <p class="description">
                        <?php esc_html_e( "Don't have a license?", 'jeiseo' ); ?>
                        <a href="https://jeiseo.com/pro" target="_blank"><?php esc_html_e( 'Get PRO', 'jeiseo' ); ?></a>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- API Settings -->
        <div class="jeiseo-card">
            <h2><?php esc_html_e( 'AI API Settings', 'jeiseo' ); ?></h2>
            <p><?php esc_html_e( 'Configure your AI provider to enable content generation and auto-fix features.', 'jeiseo' ); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="jeiseo_api_provider"><?php esc_html_e( 'API Provider', 'jeiseo' ); ?></label>
                    </th>
                    <td>
                        <select name="jeiseo_api_provider" id="jeiseo_api_provider">
                            <option value="openai" <?php selected( get_option( 'jeiseo_api_provider' ), 'openai' ); ?>>
                                OpenAI (GPT-4)
                            </option>
                            <option value="claude" <?php selected( get_option( 'jeiseo_api_provider' ), 'claude' ); ?>>
                                Anthropic (Claude)
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="jeiseo_api_key"><?php esc_html_e( 'API Key', 'jeiseo' ); ?></label>
                    </th>
                    <td>
                        <input type="password" name="jeiseo_api_key" id="jeiseo_api_key" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'jeiseo_api_key' ) ); ?>">
                        <p class="description">
                            <?php esc_html_e( 'Get your API key from', 'jeiseo' ); ?>
                            <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a>
                            <?php esc_html_e( 'or', 'jeiseo' ); ?>
                            <a href="https://console.anthropic.com/" target="_blank">Anthropic</a>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- General Settings -->
        <div class="jeiseo-card">
            <h2><?php esc_html_e( 'General Settings', 'jeiseo' ); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Auto Fix Issues', 'jeiseo' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="jeiseo_auto_fix" value="1"
                                   <?php checked( get_option( 'jeiseo_auto_fix' ) ); ?>
                                   <?php disabled( ! $is_pro ); ?>>
                            <?php esc_html_e( 'Automatically fix SEO issues after audit', 'jeiseo' ); ?>
                            <?php if ( ! $is_pro ) : ?>
                                <span class="jeiseo-pro-badge"><?php esc_html_e( 'PRO', 'jeiseo' ); ?></span>
                            <?php endif; ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Weekly Report', 'jeiseo' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="jeiseo_weekly_report" value="1"
                                   <?php checked( get_option( 'jeiseo_weekly_report' ) ); ?>>
                            <?php esc_html_e( 'Send weekly SEO report to admin email', 'jeiseo' ); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>
</div>
