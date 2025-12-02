<?php

/**
 * OWBN-CC-Client Chronicle Detail Render
 * 
 * @package OWBN-CC-Client
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Render chronicle detail.
 *
 * @param array $chronicle Chronicle data
 * @return string HTML output
 */
function ccc_render_chronicle_detail(array $chronicle): string
{
    if (empty($chronicle) || isset($chronicle['error'])) {
        return '<p class="ccc-error">' . esc_html($chronicle['error'] ?? __('Chronicle not found.', 'owbn-cc-client')) . '</p>';
    }

    $back_url = home_url('/' . ccc_get_chronicles_slug() . '/');

    ob_start();
?>
    <div id="ccc-chronicle-detail" class="ccc-chronicle-detail">

        <div id="ccc-back-link" class="ccc-back-link">
            <a href="<?php echo esc_url($back_url); ?>"><?php esc_html_e('← Back to Chronicles', 'owbn-cc-client'); ?></a>
        </div>

        <?php echo ccc_render_chronicle_header($chronicle); ?>
        <?php echo ccc_render_chronicle_quick_info($chronicle); ?>
        <?php echo ccc_render_chronicle_description($chronicle); ?>
        <?php echo ccc_render_chronicle_game_info($chronicle); ?>
        <?php echo ccc_render_chronicle_traveler_info($chronicle); ?>
        <?php echo ccc_render_chronicle_staff($chronicle); ?>
        <?php echo ccc_render_chronicle_sessions($chronicle); ?>
        <?php echo ccc_render_chronicle_links($chronicle); ?>
        <?php echo ccc_render_chronicle_locations($chronicle); ?>

    </div>
<?php
    return ob_get_clean();
}

/**
 * Render chronicle header.
 */
function ccc_render_chronicle_header(array $chronicle): string
{
    $title = $chronicle['title'] ?? '';
    $ooc = $chronicle['ooc_locations'] ?? [];
    $location = ccc_format_location($ooc);

    $badges = [];
    if (!empty($chronicle['chronicle_probationary']) && $chronicle['chronicle_probationary'] !== '0') {
        $badges[] = '<span class="ccc-badge ccc-badge-probationary">' . esc_html__('Probationary', 'owbn-cc-client') . '</span>';
    }
    if (!empty($chronicle['chronicle_satellite']) && $chronicle['chronicle_satellite'] !== '0') {
        $badges[] = '<span class="ccc-badge ccc-badge-satellite">' . esc_html__('Satellite', 'owbn-cc-client') . '</span>';
        if (!empty($chronicle['chronicle_parent'])) {
            $badges[] = '<span class="ccc-badge ccc-badge-parent">' . esc_html__('Parent: ', 'owbn-cc-client') . esc_html($chronicle['chronicle_parent']) . '</span>';
        }
    }

    ob_start();
?>
    <div id="ccc-chronicle-header" class="ccc-chronicle-header">
        <h1 id="ccc-chronicle-title" class="ccc-chronicle-title"><?php echo esc_html($title); ?></h1>

        <?php if ($location) : ?>
            <div id="ccc-chronicle-location" class="ccc-chronicle-location"><?php echo esc_html($location); ?></div>
        <?php endif; ?>

        <?php if (!empty($badges)) : ?>
            <div id="ccc-chronicle-badges" class="ccc-chronicle-badges"><?php echo implode(' ', $badges); ?></div>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render quick info section.
 */
function ccc_render_chronicle_quick_info(array $chronicle): string
{
    $genres = $chronicle['genres'] ?? [];
    $genres_display = is_array($genres) ? implode(', ', $genres) : $genres;

    ob_start();
?>
    <div id="ccc-chronicle-quick-info" class="ccc-chronicle-quick-info">
        <?php echo ccc_render_info_item(__('Genre(s)', 'owbn-cc-client'), $genres_display, 'ccc-info-genres'); ?>
        <?php echo ccc_render_info_item(__('Game Type', 'owbn-cc-client'), $chronicle['game_type'] ?? '', 'ccc-info-game-type'); ?>
        <?php echo ccc_render_info_item(__('Number of Players', 'owbn-cc-client'), $chronicle['active_player_count'] ?? '', 'ccc-info-player-count'); ?>
        <?php echo ccc_render_info_item(__('OWBN Region', 'owbn-cc-client'), $chronicle['chronicle_region'] ?? '', 'ccc-info-region'); ?>
        <?php echo ccc_render_info_item(__('Chronicle Start Date', 'owbn-cc-client'), $chronicle['chronicle_start_date'] ?? '', 'ccc-info-start-date'); ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render description/content section.
 */
function ccc_render_chronicle_description(array $chronicle): string
{
    $content = $chronicle['content'] ?? '';
    if (empty(trim($content))) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-chronicle-description" class="ccc-chronicle-description">
        <h2><?php esc_html_e('About', 'owbn-cc-client'); ?></h2>
        <div class="ccc-content"><?php echo wp_kses_post($content); ?></div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render game info (premise, theme, mood).
 */
function ccc_render_chronicle_game_info(array $chronicle): string
{
    $premise = $chronicle['premise'] ?? '';
    $theme = $chronicle['game_theme'] ?? '';
    $mood = $chronicle['game_mood'] ?? '';

    if (empty($premise) && empty($theme) && empty($mood)) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-chronicle-game-info" class="ccc-chronicle-game-info">
        <h2><?php esc_html_e('Game Information', 'owbn-cc-client'); ?></h2>
        <?php echo ccc_render_content_section(__('Premise', 'owbn-cc-client'), $premise, 'ccc-premise'); ?>
        <?php echo ccc_render_content_section(__('Theme', 'owbn-cc-client'), $theme, 'ccc-theme'); ?>
        <?php echo ccc_render_content_section(__('Mood', 'owbn-cc-client'), $mood, 'ccc-mood'); ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render traveler info.
 */
function ccc_render_chronicle_traveler_info(array $chronicle): string
{
    $info = $chronicle['traveler_info'] ?? '';
    if (empty(trim($info))) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-chronicle-traveler-info" class="ccc-chronicle-traveler-info">
        <h2><?php esc_html_e('Information for Travelers', 'owbn-cc-client'); ?></h2>
        <div class="ccc-content"><?php echo wp_kses_post($info); ?></div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render staff section.
 */
function ccc_render_chronicle_staff(array $chronicle): string
{
    $hst = $chronicle['hst_info'] ?? [];
    $cm = $chronicle['cm_info'] ?? [];
    $ast_list = $chronicle['ast_list'] ?? [];
    $admin = $chronicle['admin_contact'] ?? [];

    $has_staff = !empty($hst['display_name']) || !empty($cm['display_name']) ||
        !empty($admin['display_name']) || !empty(array_filter($ast_list, fn($a) => !empty($a['display_name'])));

    if (!$has_staff) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-chronicle-staff" class="ccc-chronicle-staff">
        <h2><?php esc_html_e('Staff', 'owbn-cc-client'); ?></h2>

        <div class="ccc-staff-grid">
            <?php echo ccc_render_staff_block($hst, __('Head Storyteller', 'owbn-cc-client'), 'ccc-staff-hst'); ?>
            <?php echo ccc_render_staff_block($cm, __('Council Member', 'owbn-cc-client'), 'ccc-staff-cm'); ?>
            <?php echo ccc_render_staff_block($admin, __('Admin Contact', 'owbn-cc-client'), 'ccc-staff-admin'); ?>
        </div>

        <?php echo ccc_render_ast_list($ast_list); ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render game sessions.
 */
function ccc_render_chronicle_sessions(array $chronicle): string
{
    $sessions = $chronicle['session_list'] ?? [];
    $sessions = array_filter($sessions, fn($s) => !empty($s['day']) || !empty($s['session_type']));

    if (empty($sessions)) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-chronicle-sessions" class="ccc-chronicle-sessions">
        <h2><?php esc_html_e('Game Sessions', 'owbn-cc-client'); ?></h2>
        <?php echo ccc_render_session_list($sessions); ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render links and resources.
 */
function ccc_render_chronicle_links(array $chronicle): string
{
    $web_url = $chronicle['web_url'] ?? '';
    $social_urls = array_filter($chronicle['social_urls'] ?? [], fn($s) => !empty($s['url']));
    $document_links = array_filter($chronicle['document_links'] ?? [], fn($d) => !empty($d['link']));
    $email_lists = array_filter($chronicle['email_lists'] ?? [], fn($e) => !empty($e['list_name']) || !empty($e['list_email']));

    if (empty($web_url) && empty($social_urls) && empty($document_links) && empty($email_lists)) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-chronicle-links" class="ccc-chronicle-links">
        <h2><?php esc_html_e('Links & Resources', 'owbn-cc-client'); ?></h2>

        <?php if ($web_url) : ?>
            <div class="ccc-link-item" id="ccc-website">
                <span class="ccc-link-label"><?php esc_html_e('Website', 'owbn-cc-client'); ?></span>
                <a href="<?php echo esc_url($web_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($web_url); ?></a>
            </div>
        <?php endif; ?>

        <?php if (!empty($social_urls)) : ?>
            <div class="ccc-social-links" id="ccc-social-urls">
                <h3><?php esc_html_e('Social Media', 'owbn-cc-client'); ?></h3>
                <?php echo ccc_render_social_links($social_urls); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($document_links)) : ?>
            <div class="ccc-document-links" id="ccc-document-links">
                <h3><?php esc_html_e('Documents', 'owbn-cc-client'); ?></h3>
                <?php echo ccc_render_document_links($document_links); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($email_lists)) : ?>
            <div class="ccc-email-lists" id="ccc-email-lists">
                <h3><?php esc_html_e('Email Lists', 'owbn-cc-client'); ?></h3>
                <?php echo ccc_render_email_lists($email_lists); ?>
            </div>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render locations (IC and game sites).
 */
function ccc_render_chronicle_locations(array $chronicle): string
{
    $game_sites = array_filter($chronicle['game_site_list'] ?? [], fn($l) => !empty($l['name']) || !empty($l['url']) || !empty($l['city']));
    $ic_locations = array_filter($chronicle['ic_location_list'] ?? [], fn($l) => !empty($l['name']) || !empty($l['city']));

    if (empty($game_sites) && empty($ic_locations)) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-chronicle-locations" class="ccc-chronicle-locations">
        <h2><?php esc_html_e('Locations', 'owbn-cc-client'); ?></h2>

        <?php if (!empty($game_sites)) : ?>
            <div class="ccc-game-sites" id="ccc-game-sites">
                <h3><?php esc_html_e('Game Sites', 'owbn-cc-client'); ?></h3>
                <?php echo ccc_render_location_list($game_sites, 'game_site'); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($ic_locations)) : ?>
            <div class="ccc-ic-locations" id="ccc-ic-locations">
                <h3><?php esc_html_e('IC Locations', 'owbn-cc-client'); ?></h3>
                <?php echo ccc_render_location_list($ic_locations, 'ic_location'); ?>
            </div>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}
