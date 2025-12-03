<?php

/**
 * OWBN-CC-Client Coordinator Detail Render
 * 
 * @package OWBN-CC-Client
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

/**
 * Render coordinator detail.
 */
function ccc_render_coordinator_detail(array $coordinator): string
{
    if (empty($coordinator) || isset($coordinator['error'])) {
        return '<p class="ccc-error">' . esc_html($coordinator['error'] ?? __('Coordinator not found.', 'owbn-cc-client')) . '</p>';
    }

    $back_url = home_url('/' . ccc_get_coordinators_slug() . '/');
    $has_documents = !empty(array_filter($coordinator['document_links'] ?? [], fn($d) => !empty($d['url'])));

    ob_start();
?>
    <div id="ccc-coordinator-detail" class="ccc-coordinator-detail">

        <div id="ccc-back-link" class="ccc-back-link">
            <a href="<?php echo esc_url($back_url); ?>"><?php esc_html_e('← Back to Coordinators', 'owbn-cc-client'); ?></a>
        </div>

        <?php echo ccc_render_coordinator_header($coordinator); ?>
        <?php echo ccc_render_coordinator_description($coordinator); ?>

        <div class="ccc-coord-row <?php echo $has_documents ? '' : 'ccc-no-sidebar'; ?>">
            <div class="ccc-coord-main">
                <?php echo ccc_render_coordinator_info($coordinator); ?>
                <?php echo ccc_render_coordinator_subcoords($coordinator); ?>
            </div>
            <?php if ($has_documents) : ?>
                <div class="ccc-coord-sidebar">
                    <?php echo ccc_render_coordinator_documents($coordinator); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="ccc-coord-row">
            <div class="ccc-coord-main">
                <?php echo ccc_render_coordinator_player_lists($coordinator); ?>
            </div>
            <div class="ccc-coord-sidebar">
                <?php echo ccc_render_coordinator_contact_lists($coordinator); ?>
            </div>
        </div>

    </div>
<?php
    return ob_get_clean();
}

/**
 * Render coordinator header (title only).
 */
function ccc_render_coordinator_header(array $coordinator): string
{
    $title = $coordinator['title'] ?? $coordinator['coordinator_title'] ?? '';

    ob_start();
?>
    <div class="ccc-coordinator-header">
        <h1 class="ccc-coordinator-title"><?php echo esc_html($title); ?></h1>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render office description (no header).
 */
function ccc_render_coordinator_description(array $coordinator): string
{
    $description = $coordinator['office_description'] ?? '';
    if (empty(trim($description))) {
        return '';
    }

    ob_start();
?>
    <div class="ccc-coordinator-description">
        <div class="ccc-content"><?php echo wp_kses_post($description); ?></div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render coordinator info (Name | Email).
 */
function ccc_render_coordinator_info(array $coordinator): string
{
    $coord_info = $coordinator['coord_info'] ?? [];
    $name = $coord_info['display_name'] ?? '';
    $email = $coord_info['display_email'] ?? '';

    if (empty($name) && empty($email)) {
        return '';
    }

    ob_start();
?>
    <div class="ccc-coordinator-info">
        <h3><?php esc_html_e('Coordinator', 'owbn-cc-client'); ?></h3>
        <div class="ccc-inline-table">
            <div class="ccc-inline-row">
                <span class="ccc-inline-name"><?php echo esc_html($name); ?></span>
                <?php if ($email) : ?>
                    <a class="ccc-inline-email" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render sub-coordinators (Name | Role | Contact) - no header.
 */
function ccc_render_coordinator_subcoords(array $coordinator): string
{
    $subcoords = $coordinator['subcoord_list'] ?? [];
    $subcoords = array_filter($subcoords, fn($s) => !empty($s['display_name']));

    if (empty($subcoords)) {
        return '';
    }

    ob_start();
?>
    <div class="ccc-coordinator-subcoords">
        <h3><?php esc_html_e('Subcoordinators', 'owbn-cc-client'); ?></h3>
        <div class="ccc-inline-table">
            <div class="ccc-inline-row ccc-inline-header">
                <span class="ccc-inline-name"><?php esc_html_e('Name', 'owbn-cc-client'); ?></span>
                <span class="ccc-inline-role"><?php esc_html_e('Role', 'owbn-cc-client'); ?></span>
                <span class="ccc-inline-email"><?php esc_html_e('Contact', 'owbn-cc-client'); ?></span>
            </div>
            <?php foreach ($subcoords as $subcoord) : ?>
                <div class="ccc-inline-row">
                    <span class="ccc-inline-name"><?php echo esc_html($subcoord['display_name']); ?></span>
                    <span class="ccc-inline-role"><?php echo esc_html($subcoord['role'] ?? ''); ?></span>
                    <span class="ccc-inline-email">
                        <?php if (!empty($subcoord['display_email'])) : ?>
                            <a href="mailto:<?php echo esc_attr($subcoord['display_email']); ?>"><?php echo esc_html($subcoord['display_email']); ?></a>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function ccc_render_coordinator_documents(array $coordinator): string
{
    $documents = $coordinator['document_links'] ?? [];
    $documents = array_filter($documents, fn($d) => !empty($d['url']));

    if (empty($documents)) {
        return '';
    }

    $is_logged_in = is_user_logged_in();

    ob_start();
?>
    <div class="ccc-coordinator-documents ccc-info-box">
        <h3><?php esc_html_e('Genre Documents', 'owbn-cc-client'); ?></h3>
        <?php foreach ($documents as $doc) : ?>
            <div class="ccc-document-item">
                <?php if ($is_logged_in) : ?>
                    <a href="<?php echo esc_url($doc['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($doc['title'] ?: $doc['url']); ?></a>
                <?php else : ?>
                    <?php echo esc_html($doc['title'] ?: __('Document', 'owbn-cc-client')); ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (!$is_logged_in) : ?>
            <p class="ccc-auth-notice"><?php esc_html_e('Downloads only available to authenticated users.', 'owbn-cc-client'); ?></p>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render player lists (Name | Access | IC/OOC | Moderator | Link).
 */
function ccc_render_coordinator_player_lists(array $coordinator): string
{
    $lists = $coordinator['player_lists'] ?? [];
    $lists = array_filter($lists, fn($l) => !empty($l['list_name']));

    if (empty($lists)) {
        return '';
    }

    ob_start();
?>
    <div class="ccc-coordinator-player-lists">
        <h3><?php esc_html_e('Player Lists', 'owbn-cc-client'); ?></h3>
        <div class="ccc-player-list-table">
            <div class="ccc-player-list-row ccc-player-list-header">
                <span class="ccc-pl-name"><?php esc_html_e('Name', 'owbn-cc-client'); ?></span>
                <span class="ccc-pl-access"><?php esc_html_e('Access', 'owbn-cc-client'); ?></span>
                <span class="ccc-pl-type"><?php esc_html_e('IC/OOC', 'owbn-cc-client'); ?></span>
                <span class="ccc-pl-moderator"><?php esc_html_e('Moderator', 'owbn-cc-client'); ?></span>
                <span class="ccc-pl-link"><?php esc_html_e('Link', 'owbn-cc-client'); ?></span>
            </div>
            <?php foreach ($lists as $list) : ?>
                <div class="ccc-player-list-row">
                    <span class="ccc-pl-name">
                        <?php if (!empty($list['address'])) : ?>
                            <a href="mailto:<?php echo esc_attr($list['address']); ?>"><?php echo esc_html($list['list_name']); ?></a>
                        <?php else : ?>
                            <?php echo esc_html($list['list_name']); ?>
                        <?php endif; ?>
                    </span>
                    <span class="ccc-pl-access"><?php echo esc_html($list['access'] ?? ''); ?></span>
                    <span class="ccc-pl-type"><?php echo esc_html($list['ic_ooc'] ?? ''); ?></span>
                    <span class="ccc-pl-moderator">
                        <?php if (!empty($list['moderate_address'])) : ?>
                            <a href="mailto:<?php echo esc_attr($list['moderate_address']); ?>"><?php echo esc_html($list['moderate_address']); ?></a>
                        <?php endif; ?>
                    </span>
                    <span class="ccc-pl-link">
                        <?php if (!empty($list['signup_url'])) : ?>
                            <a href="<?php echo esc_url($list['signup_url']); ?>" target="_blank" rel="noopener"><?php esc_html_e('Link', 'owbn-cc-client'); ?></a>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render contact lists (email lists).
 */
function ccc_render_coordinator_contact_lists(array $coordinator): string
{
    $lists = $coordinator['email_lists'] ?? [];
    $lists = array_filter($lists, fn($l) => !empty($l['list_name']) || !empty($l['email_address']));

    if (empty($lists)) {
        return '';
    }

    ob_start();
?>
    <div class="ccc-coordinator-contact-lists ccc-info-box">
        <h3><?php esc_html_e('Contact Lists', 'owbn-cc-client'); ?></h3>
        <?php foreach ($lists as $list) : ?>
            <div class="ccc-contact-item">
                <?php if (!empty($list['email_address'])) : ?>
                    <a href="mailto:<?php echo esc_attr($list['email_address']); ?>"><?php echo esc_html($list['list_name'] ?: $list['email_address']); ?></a>
                <?php else : ?>
                    <?php echo esc_html($list['list_name']); ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
}
