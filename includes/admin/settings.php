<?php

/**
 * OWBN-CC-Client Settings Page
 * 
 * @package OWBN-CC-Client
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// ══════════════════════════════════════════════════════════════════════════════
// ADMIN MENU
// ══════════════════════════════════════════════════════════════════════════════

add_action('admin_menu', function () {
    $client_id = ccc_get_client_id();
    $menu_slug = $client_id . '-ccc-settings';

    // Top-level menu (points to Settings by default)
    add_menu_page(
        __('OWBN CC Client', 'owbn-cc-client'),
        __('OWBN CC Client', 'owbn-cc-client'),
        'manage_options',
        $menu_slug,
        'ccc_render_settings_page',
        'dashicons-networking',
        30
    );

    // Chronicles submenu (only if enabled)
    if (get_option(ccc_option_name('enable_chronicles'), false)) {
        add_submenu_page(
            $menu_slug,
            __('Chronicles', 'owbn-cc-client'),
            __('Chronicles', 'owbn-cc-client'),
            'manage_options',
            $client_id . '-ccc-chronicles',
            'ccc_render_chronicles_page'
        );
    }

    // Coordinators submenu (only if enabled)
    if (get_option(ccc_option_name('enable_coordinators'), false)) {
        add_submenu_page(
            $menu_slug,
            __('Coordinators', 'owbn-cc-client'),
            __('Coordinators', 'owbn-cc-client'),
            'manage_options',
            $client_id . '-ccc-coordinators',
            'ccc_render_coordinators_page'
        );
    }

    // Settings submenu (rename the default)
    add_submenu_page(
        $menu_slug,
        __('Settings', 'owbn-cc-client'),
        __('Settings', 'owbn-cc-client'),
        'manage_options',
        $menu_slug,
        'ccc_render_settings_page'
    );
});

// ══════════════════════════════════════════════════════════════════════════════
// PLACEHOLDER PAGES
// ══════════════════════════════════════════════════════════════════════════════

function ccc_render_chronicles_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
?>
    <div class="wrap">
        <h1><?php esc_html_e('Chronicles', 'owbn-cc-client'); ?></h1>
        <p><?php esc_html_e('Chronicles management coming soon.', 'owbn-cc-client'); ?></p>
    </div>
<?php
}

function ccc_render_coordinators_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
?>
    <div class="wrap">
        <h1><?php esc_html_e('Coordinators', 'owbn-cc-client'); ?></h1>
        <p><?php esc_html_e('Coordinators management coming soon.', 'owbn-cc-client'); ?></p>
    </div>
<?php
}

// ══════════════════════════════════════════════════════════════════════════════
// REGISTER SETTINGS
// ══════════════════════════════════════════════════════════════════════════════

add_action('admin_init', function () {
    $group = ccc_get_client_id() . '_ccc_settings';

    // Chronicles
    register_setting($group, ccc_option_name('enable_chronicles'), [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    register_setting($group, ccc_option_name('chronicles_mode'), [
        'type' => 'string',
        'default' => 'local',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting($group, ccc_option_name('chronicles_url'), [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting($group, ccc_option_name('chronicles_api_key'), [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    // Coordinators
    register_setting($group, ccc_option_name('enable_coordinators'), [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    register_setting($group, ccc_option_name('coordinators_mode'), [
        'type' => 'string',
        'default' => 'local',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting($group, ccc_option_name('coordinators_url'), [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting($group, ccc_option_name('coordinators_api_key'), [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
});

// ══════════════════════════════════════════════════════════════════════════════
// RENDER SETTINGS PAGE
// ══════════════════════════════════════════════════════════════════════════════

function ccc_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $client_id = ccc_get_client_id();
    $group = $client_id . '_ccc_settings';

    // Current values
    $chron_enabled = get_option(ccc_option_name('enable_chronicles'), false);
    $chron_mode    = get_option(ccc_option_name('chronicles_mode'), 'local');
    $chron_url     = get_option(ccc_option_name('chronicles_url'), '');
    $chron_key     = get_option(ccc_option_name('chronicles_api_key'), '');

    $coord_enabled = get_option(ccc_option_name('enable_coordinators'), false);
    $coord_mode    = get_option(ccc_option_name('coordinators_mode'), 'local');
    $coord_url     = get_option(ccc_option_name('coordinators_url'), '');
    $coord_key     = get_option(ccc_option_name('coordinators_api_key'), '');

?>
    <div class="wrap">
        <h1><?php esc_html_e('OWBN CC Client Settings', 'owbn-cc-client'); ?></h1>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields($group); ?>

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- CHRONICLES -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <h2><?php esc_html_e('Chronicles', 'owbn-cc-client'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable', 'owbn-cc-client'); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="<?php echo esc_attr(ccc_option_name('enable_chronicles')); ?>" value="0" />
                            <input type="checkbox"
                                name="<?php echo esc_attr(ccc_option_name('enable_chronicles')); ?>"
                                id="ccc_enable_chronicles"
                                value="1"
                                <?php checked($chron_enabled); ?> />
                            <?php esc_html_e('Enable Chronicles', 'owbn-cc-client'); ?>
                        </label>
                    </td>
                </tr>
                <tr class="ccc-chronicles-options" <?php echo $chron_enabled ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('Data Source', 'owbn-cc-client'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio"
                                    name="<?php echo esc_attr(ccc_option_name('chronicles_mode')); ?>"
                                    class="ccc-chronicles-mode"
                                    value="local"
                                    <?php checked($chron_mode, 'local'); ?> />
                                <?php esc_html_e('Local (same site)', 'owbn-cc-client'); ?>
                            </label><br>
                            <label>
                                <input type="radio"
                                    name="<?php echo esc_attr(ccc_option_name('chronicles_mode')); ?>"
                                    class="ccc-chronicles-mode"
                                    value="remote"
                                    <?php checked($chron_mode, 'remote'); ?> />
                                <?php esc_html_e('Remote API', 'owbn-cc-client'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr class="ccc-chronicles-options ccc-chronicles-remote" <?php echo ($chron_enabled && $chron_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('API URL', 'owbn-cc-client'); ?></th>
                    <td>
                        <input type="url"
                            name="<?php echo esc_attr(ccc_option_name('chronicles_url')); ?>"
                            value="<?php echo esc_url($chron_url); ?>"
                            class="regular-text"
                            placeholder="https://example.com/wp-json/owbn-cc/v1/" />
                    </td>
                </tr>
                <tr class="ccc-chronicles-options ccc-chronicles-remote" <?php echo ($chron_enabled && $chron_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('API Key', 'owbn-cc-client'); ?></th>
                    <td>
                        <input type="text"
                            name="<?php echo esc_attr(ccc_option_name('chronicles_api_key')); ?>"
                            value="<?php echo esc_attr($chron_key); ?>"
                            class="regular-text code" />
                    </td>
                </tr>
                <tr class="ccc-chronicles-options ccc-chronicles-remote" <?php echo ($chron_enabled && $chron_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"></th>
                    <td>
                        <button type="button" class="button" id="ccc_test_chronicles_api">
                            <?php esc_html_e('Test Connection', 'owbn-cc-client'); ?>
                        </button>
                        <span id="ccc_chronicles_test_result"></span>
                    </td>
                </tr>
            </table>

            <hr />

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- COORDINATORS -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <h2><?php esc_html_e('Coordinators', 'owbn-cc-client'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable', 'owbn-cc-client'); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="<?php echo esc_attr(ccc_option_name('enable_coordinators')); ?>" value="0" />
                            <input type="checkbox"
                                name="<?php echo esc_attr(ccc_option_name('enable_coordinators')); ?>"
                                id="ccc_enable_coordinators"
                                value="1"
                                <?php checked($coord_enabled); ?> />
                            <?php esc_html_e('Enable Coordinators', 'owbn-cc-client'); ?>
                        </label>
                    </td>
                </tr>
                <tr class="ccc-coordinators-options" <?php echo $coord_enabled ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('Data Source', 'owbn-cc-client'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio"
                                    name="<?php echo esc_attr(ccc_option_name('coordinators_mode')); ?>"
                                    class="ccc-coordinators-mode"
                                    value="local"
                                    <?php checked($coord_mode, 'local'); ?> />
                                <?php esc_html_e('Local (same site)', 'owbn-cc-client'); ?>
                            </label><br>
                            <label>
                                <input type="radio"
                                    name="<?php echo esc_attr(ccc_option_name('coordinators_mode')); ?>"
                                    class="ccc-coordinators-mode"
                                    value="remote"
                                    <?php checked($coord_mode, 'remote'); ?> />
                                <?php esc_html_e('Remote API', 'owbn-cc-client'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr class="ccc-coordinators-options ccc-coordinators-remote" <?php echo ($coord_enabled && $coord_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('API URL', 'owbn-cc-client'); ?></th>
                    <td>
                        <input type="url"
                            name="<?php echo esc_attr(ccc_option_name('coordinators_url')); ?>"
                            value="<?php echo esc_url($coord_url); ?>"
                            class="regular-text"
                            placeholder="https://example.com/wp-json/owbn-cc/v1/" />
                    </td>
                </tr>
                <tr class="ccc-coordinators-options ccc-coordinators-remote" <?php echo ($coord_enabled && $coord_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('API Key', 'owbn-cc-client'); ?></th>
                    <td>
                        <input type="text"
                            name="<?php echo esc_attr(ccc_option_name('coordinators_api_key')); ?>"
                            value="<?php echo esc_attr($coord_key); ?>"
                            class="regular-text code" />
                    </td>
                </tr>
                <tr class="ccc-coordinators-options ccc-coordinators-remote" <?php echo ($coord_enabled && $coord_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"></th>
                    <td>
                        <button type="button" class="button" id="ccc_test_coordinators_api">
                            <?php esc_html_e('Test Connection', 'owbn-cc-client'); ?>
                        </button>
                        <span id="ccc_coordinators_test_result"></span>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>

    <script>
        (function($) {
            // Chronicles toggle
            $('#ccc_enable_chronicles').on('change', function() {
                $('.ccc-chronicles-options').toggle(this.checked);
                if (!this.checked) {
                    $('.ccc-chronicles-remote').hide();
                } else {
                    var isRemote = $('.ccc-chronicles-mode:checked').val() === 'remote';
                    $('.ccc-chronicles-remote').toggle(isRemote);
                }
            });

            $('.ccc-chronicles-mode').on('change', function() {
                $('.ccc-chronicles-remote').toggle(this.value === 'remote');
            });

            // Coordinators toggle
            $('#ccc_enable_coordinators').on('change', function() {
                $('.ccc-coordinators-options').toggle(this.checked);
                if (!this.checked) {
                    $('.ccc-coordinators-remote').hide();
                } else {
                    var isRemote = $('.ccc-coordinators-mode:checked').val() === 'remote';
                    $('.ccc-coordinators-remote').toggle(isRemote);
                }
            });

            $('.ccc-coordinators-mode').on('change', function() {
                $('.ccc-coordinators-remote').toggle(this.value === 'remote');
            });

            // Test API buttons
            $('#ccc_test_chronicles_api').on('click', function() {
                var $btn = $(this);
                var $result = $('#ccc_chronicles_test_result');
                var url = $('input[name="<?php echo esc_js(ccc_option_name('chronicles_url')); ?>"]').val();
                var key = $('input[name="<?php echo esc_js(ccc_option_name('chronicles_api_key')); ?>"]').val();

                $btn.prop('disabled', true);
                $result.html('<span style="color:#666;"><?php echo esc_js(__('Testing...', 'owbn-cc-client')); ?></span>');

                $.post(ajaxurl, {
                    action: 'ccc_test_api',
                    nonce: '<?php echo wp_create_nonce('ccc_test_api_nonce'); ?>',
                    type: 'chronicles',
                    url: url,
                    api_key: key
                }, function(response) {
                    $btn.prop('disabled', false);
                    if (response.success) {
                        $result.html('<span style="color:green;">✓ ' + response.data.message + '</span>');
                    } else {
                        $result.html('<span style="color:red;">✗ ' + response.data.message + '</span>');
                    }
                }).fail(function() {
                    $btn.prop('disabled', false);
                    $result.html('<span style="color:red;">✗ <?php echo esc_js(__('Request failed.', 'owbn-cc-client')); ?></span>');
                });
            });

            $('#ccc_test_coordinators_api').on('click', function() {
                var $btn = $(this);
                var $result = $('#ccc_coordinators_test_result');
                var url = $('input[name="<?php echo esc_js(ccc_option_name('coordinators_url')); ?>"]').val();
                var key = $('input[name="<?php echo esc_js(ccc_option_name('coordinators_api_key')); ?>"]').val();

                $btn.prop('disabled', true);
                $result.html('<span style="color:#666;"><?php echo esc_js(__('Testing...', 'owbn-cc-client')); ?></span>');

                $.post(ajaxurl, {
                    action: 'ccc_test_api',
                    nonce: '<?php echo wp_create_nonce('ccc_test_api_nonce'); ?>',
                    type: 'coordinators',
                    url: url,
                    api_key: key
                }, function(response) {
                    $btn.prop('disabled', false);
                    if (response.success) {
                        $result.html('<span style="color:green;">✓ ' + response.data.message + '</span>');
                    } else {
                        $result.html('<span style="color:red;">✗ ' + response.data.message + '</span>');
                    }
                }).fail(function() {
                    $btn.prop('disabled', false);
                    $result.html('<span style="color:red;">✗ <?php echo esc_js(__('Request failed.', 'owbn-cc-client')); ?></span>');
                });
            });
        })(jQuery);
    </script>
<?php
}

// ══════════════════════════════════════════════════════════════════════════════
// AJAX TEST HANDLER
// ══════════════════════════════════════════════════════════════════════════════

add_action('wp_ajax_ccc_test_api', 'ccc_handle_test_api');

function ccc_handle_test_api()
{
    check_ajax_referer('ccc_test_api_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission denied.', 'owbn-cc-client')]);
    }

    $type = sanitize_text_field($_POST['type'] ?? '');
    $url  = esc_url_raw($_POST['url'] ?? '');
    $key  = sanitize_text_field($_POST['api_key'] ?? '');

    if (!in_array($type, ['chronicles', 'coordinators'], true)) {
        wp_send_json_error(['message' => __('Invalid type.', 'owbn-cc-client')]);
    }

    if (empty($url)) {
        wp_send_json_error(['message' => __('URL is required.', 'owbn-cc-client')]);
    }

    // Build endpoint
    $endpoint = trailingslashit($url) . $type;

    $response = wp_remote_post($endpoint, [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key'    => $key,
        ],
        'body' => wp_json_encode([]),
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => sprintf(
                __('Connection failed: %s', 'owbn-cc-client'),
                $response->get_error_message()
            )
        ]);
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($code === 200) {
        $count = is_array($data) ? count($data) : 0;
        wp_send_json_success([
            'message' => sprintf(
                __('Success! Found %d %s.', 'owbn-cc-client'),
                $count,
                $type
            )
        ]);
    } elseif ($code === 403) {
        wp_send_json_error(['message' => __('Invalid API key.', 'owbn-cc-client')]);
    } else {
        wp_send_json_error([
            'message' => sprintf(
                __('API returned status %d.', 'owbn-cc-client'),
                $code
            )
        ]);
    }
}
