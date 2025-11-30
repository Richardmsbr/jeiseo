<?php
/**
 * Settings view
 *
 * @package JeiSEO
 */

defined( 'ABSPATH' ) || exit;

$jeiseo_is_pro = jeiseo()->is_pro();
$jeiseo_license = jeiseo()->license;
?>

<div class="wrap jeiseo-wrap">
    <h1><?php esc_html_e( 'JeiSEO Settings', 'jeiseo-ai-marketing-automation' ); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields( 'jeiseo_settings' ); ?>

        <!-- License -->
        <div class="jeiseo-card">
            <h2><?php esc_html_e( 'License', 'jeiseo-ai-marketing-automation' ); ?></h2>

            <?php if ( $jeiseo_is_pro ) : ?>
                <div class="jeiseo-license-active">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div>
                        <strong><?php esc_html_e( 'PRO License Active', 'jeiseo-ai-marketing-automation' ); ?></strong>
                        <p><?php echo esc_html( $jeiseo_license->get_masked_key() ); ?></p>
                    </div>
                    <button type="button" id="jeiseo-deactivate-license" class="button">
                        <?php esc_html_e( 'Deactivate', 'jeiseo-ai-marketing-automation' ); ?>
                    </button>
                </div>
            <?php else : ?>
                <div class="jeiseo-license-form">
                    <p><?php esc_html_e( 'Enter your license key to unlock PRO features.', 'jeiseo-ai-marketing-automation' ); ?></p>
                    <div class="jeiseo-form-inline">
                        <input type="text" id="jeiseo-license-key" placeholder="XXXX-XXXX-XXXX-XXXX">
                        <button type="button" id="jeiseo-activate-license" class="button button-primary">
                            <?php esc_html_e( 'Activate', 'jeiseo-ai-marketing-automation' ); ?>
                        </button>
                    </div>
                    <p class="description">
                        <?php esc_html_e( "Don't have a license?", 'jeiseo-ai-marketing-automation' ); ?>
                        <a href="https://jeiseo.com/pro" target="_blank"><?php esc_html_e( 'Get PRO', 'jeiseo-ai-marketing-automation' ); ?></a>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- API Settings -->
        <div class="jeiseo-card">
            <h2><?php esc_html_e( 'AI API Settings', 'jeiseo-ai-marketing-automation' ); ?></h2>
            <p><?php esc_html_e( 'Configure your AI provider to enable content generation and auto-fix features.', 'jeiseo-ai-marketing-automation' ); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="jeiseo_api_provider"><?php esc_html_e( 'API Provider', 'jeiseo-ai-marketing-automation' ); ?></label>
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
                        <label for="jeiseo_api_key"><?php esc_html_e( 'API Key', 'jeiseo-ai-marketing-automation' ); ?></label>
                    </th>
                    <td>
                        <input type="password" name="jeiseo_api_key" id="jeiseo_api_key" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'jeiseo_api_key' ) ); ?>">
                        <p class="description">
                            <?php esc_html_e( 'Get your API key from', 'jeiseo-ai-marketing-automation' ); ?>
                            <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a>
                            <?php esc_html_e( 'or', 'jeiseo-ai-marketing-automation' ); ?>
                            <a href="https://console.anthropic.com/" target="_blank">Anthropic</a>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- General Settings -->
        <div class="jeiseo-card">
            <h2><?php esc_html_e( 'General Settings', 'jeiseo-ai-marketing-automation' ); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Auto Fix Issues', 'jeiseo-ai-marketing-automation' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="jeiseo_auto_fix" value="1"
                                   <?php checked( get_option( 'jeiseo_auto_fix' ) ); ?>
                                   <?php disabled( ! $jeiseo_is_pro ); ?>>
                            <?php esc_html_e( 'Automatically fix SEO issues after audit', 'jeiseo-ai-marketing-automation' ); ?>
                            <?php if ( ! $jeiseo_is_pro ) : ?>
                                <span class="jeiseo-pro-badge"><?php esc_html_e( 'PRO', 'jeiseo-ai-marketing-automation' ); ?></span>
                            <?php endif; ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Weekly Report', 'jeiseo-ai-marketing-automation' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="jeiseo_weekly_report" value="1"
                                   <?php checked( get_option( 'jeiseo_weekly_report' ) ); ?>>
                            <?php esc_html_e( 'Send weekly SEO report to admin email', 'jeiseo-ai-marketing-automation' ); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>
</div>
