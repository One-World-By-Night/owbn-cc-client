<?php

/**
 * OWBN-CC-Client Template Loader
 * 
 * @package OWBN-CC-Client
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

add_action('template_redirect', 'ccc_template_redirect');

function ccc_template_redirect()
{
    $route  = get_query_var('ccc_route');
    $action = get_query_var('ccc_action');
    $slug   = get_query_var('ccc_slug');

    if (empty($route)) {
        return;
    }

    if (!in_array($route, ['chronicles', 'coordinators'], true)) {
        return;
    }

    $option = $route === 'chronicles' ? 'enable_chronicles' : 'enable_coordinators';
    if (!get_option(ccc_option_name($option), false)) {
        return;
    }

    // Fetch and render
    if ($action === 'list') {
        $data = ccc_fetch_list($route);
        ccc_render_page($route, 'list', $data);
    } elseif ($action === 'detail' && !empty($slug)) {
        $data = ccc_fetch_detail($route, sanitize_title($slug));
        ccc_render_page($route, 'detail', $data, $slug);
    }

    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// RENDER PAGE
// ══════════════════════════════════════════════════════════════════════════════

function ccc_render_page(string $route, string $action, $data, string $slug = '')
{
    // Check for errors
    if (isset($data['error'])) {
        wp_die(esc_html($data['error']), esc_html__('Error', 'owbn-cc-client'), ['response' => 500]);
    }

    // Page title
    if ($action === 'list') {
        $title = $route === 'chronicles'
            ? __('Chronicles', 'owbn-cc-client')
            : __('Coordinators', 'owbn-cc-client');
    } else {
        $title = $data['title'] ?? ucfirst($slug);
    }

    // Render content
    if ($action === 'list') {
        $content = $route === 'chronicles'
            ? ccc_render_chronicles_list($data)
            : ccc_render_coordinators_list($data);
    } else {
        $content = $route === 'chronicles'
            ? ccc_render_chronicle_detail($data)
            : ccc_render_coordinator_detail($data);
    }

    // Output with theme wrapper
    ccc_output_page($title, $content);
}

// WITH THIS:
function ccc_output_page(string $title, string $content)
{
    // Set page title
    add_filter('document_title_parts', fn($parts) => array_merge($parts, ['title' => $title]));

    get_header();
?>
    <main id="ccc-main" class="ccc-content-area">
        <div class="ccc-container">
            <?php echo $content; ?>
        </div>
    </main>
<?php
    get_footer();
}

// ══════════════════════════════════════════════════════════════════════════════
// DATA FETCH
// ══════════════════════════════════════════════════════════════════════════════

function ccc_fetch_list(string $route)
{
    $mode = get_option(ccc_option_name($route . '_mode'), 'local');

    if ($mode === 'local') {
        return ccc_fetch_local_list($route);
    }
    return ccc_fetch_remote_list($route);
}

function ccc_fetch_detail(string $route, string $slug)
{
    $mode = get_option(ccc_option_name($route . '_mode'), 'local');

    if ($mode === 'local') {
        return ccc_fetch_local_detail($route, $slug);
    }
    return ccc_fetch_remote_detail($route, $slug);
}

// ══════════════════════════════════════════════════════════════════════════════
// LOCAL FETCH
// ══════════════════════════════════════════════════════════════════════════════

function ccc_fetch_local_list(string $route)
{
    $func = $route === 'chronicles' ? 'owbn_api_get_chronicles' : 'owbn_api_get_coordinators';

    if (!function_exists($func)) {
        return ['error' => 'Local source not available'];
    }

    $request = new WP_REST_Request('POST');
    $response = $func($request);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    return $response->get_data();
}

function ccc_fetch_local_detail(string $route, string $slug)
{
    $func = $route === 'chronicles' ? 'owbn_api_get_chronicle_detail' : 'owbn_api_get_coordinator_detail';

    if (!function_exists($func)) {
        return ['error' => 'Local source not available'];
    }

    $request = new WP_REST_Request('POST');
    $request->set_header('Content-Type', 'application/json');
    $request->set_body(wp_json_encode(['slug' => $slug]));
    $response = $func($request);

    return is_wp_error($response) ? ['error' => $response->get_error_message()] : $response->get_data();
}

// ══════════════════════════════════════════════════════════════════════════════
// REMOTE FETCH
// ══════════════════════════════════════════════════════════════════════════════

function ccc_fetch_remote_list(string $route)
{
    $url = get_option(ccc_option_name($route . '_url'), '');
    $key = get_option(ccc_option_name($route . '_api_key'), '');

    if (empty($url)) {
        return ['error' => 'Remote URL not configured'];
    }

    $response = wp_remote_post(trailingslashit($url) . $route, [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key'    => $key,
        ],
        'body' => wp_json_encode([]),
    ]);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    return json_decode(wp_remote_retrieve_body($response), true) ?: [];
}

function ccc_fetch_remote_detail(string $route, string $slug)
{
    $url = get_option(ccc_option_name($route . '_url'), '');
    $key = get_option(ccc_option_name($route . '_api_key'), '');

    if (empty($url)) {
        return ['error' => 'Remote URL not configured'];
    }

    $endpoint = $route === 'chronicles' ? 'chronicle-detail' : 'coordinator-detail';

    $response = wp_remote_post(trailingslashit($url) . $endpoint, [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key'    => $key,
        ],
        'body' => wp_json_encode(['slug' => $slug]),
    ]);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    return json_decode(wp_remote_retrieve_body($response), true) ?: ['error' => 'Not found'];
}
