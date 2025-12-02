<?php

/**
 * OWBN-CC-Client Coordinator Detail Render
 * 
 * @package OWBN-CC-Client
 * @version 1.0.0
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

    ob_start();
?>
    <div id="ccc-coordinator-detail" class="ccc-coordinator-detail">

        <div id="ccc-back-link" class="ccc-back-link">
            <a href="<?php echo esc_url($back_url); ?>"><?php esc_html_e('← Back to Coordinators', 'owbn-cc-client'); ?></a>
        </div>

        <?php echo ccc_render_coordinator_header($coordinator); ?>
        <?php echo ccc_render_coordinator_info($coordinator); ?>
        <?php echo ccc_render_coordinator_description($coordinator); ?>
        <?php echo ccc_render_coordinator_subcoords($coordinator); ?>
        <?php echo ccc_render_coordinator_documents($coordinator); ?>
        <?php echo ccc_render_coordinator_email_lists($coordinator); ?>
        <?php echo ccc_render_coordinator_content($coordinator); ?>

    </div>
<?php
    return ob_get_clean();
}

/**
 * Render coordinator header.
 */
function ccc_render_coordinator_header(array $coordinator): string
{
    $title = $coordinator['title'] ?? '';
    $office_title = $coordinator['coordinator_title'] ?? '';

    ob_start();
?>
    <div id="ccc-coordinator-header" class="ccc-coordinator-header">
        <h1 id="ccc-coordinator-title" class="ccc-coordinator-title"><?php echo esc_html($title); ?></h1>
        <?php if ($office_title && $office_title !== $title) : ?>
            <div id="ccc-coordinator-office-title" class="ccc-coordinator-office-title"><?php echo esc_html($office_title); ?></div>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render coordinator contact info.
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
    <div id="ccc-coordinator-info" class="ccc-coordinator-info">
        <h2><?php esc_html_e('Coordinator Info', 'owbn-cc-client'); ?></h2>
        <div class="ccc-info-table">
            <?php if ($name) : ?>
                <div class="ccc-info-row">
                    <div class="ccc-info-label"><?php esc_html_e('Listed Name', 'owbn-cc-client'); ?></div>
                    <div class="ccc-info-value"><?php echo esc_html($name); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($email) : ?>
                <div class="ccc-info-row">
                    <div class="ccc-info-label"><?php esc_html_e('Listed Email', 'owbn-cc-client'); ?></div>
                    <div class="ccc-info-value">
                        <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render office description.
 */
function ccc_render_coordinator_description(array $coordinator): string
{
    $description = $coordinator['office_description'] ?? '';
    if (empty(trim($description))) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-coordinator-description" class="ccc-coordinator-description">
        <h2><?php esc_html_e('Office Description', 'owbn-cc-client'); ?></h2>
        <div class="ccc-content"><?php echo wp_kses_post($description); ?></div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render sub-coordinators.
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
    <div id="ccc-coordinator-subcoords" class="ccc-coordinator-subcoords">
        <h2><?php esc_html_e('Sub-Coordinators', 'owbn-cc-client'); ?></h2>
        <div class="ccc-subcoord-table">
            <div class="ccc-subcoord-header">
                <div class="ccc-subcoord-col-name"><?php esc_html_e('Name', 'owbn-cc-client'); ?></div>
                <div class="ccc-subcoord-col-role"><?php esc_html_e('Role', 'owbn-cc-client'); ?></div>
                <div class="ccc-subcoord-col-email"><?php esc_html_e('Contact', 'owbn-cc-client'); ?></div>
            </div>
            <?php foreach ($subcoords as $subcoord) : ?>
                <div class="ccc-subcoord-row">
                    <div class="ccc-subcoord-col-name"><?php echo esc_html($subcoord['display_name']); ?></div>
                    <div class="ccc-subcoord-col-role"><?php echo esc_html($subcoord['role'] ?? '—'); ?></div>
                    <div class="ccc-subcoord-col-email">
                        <?php if (!empty($subcoord['display_email'])) : ?>
                            <a href="mailto:<?php echo esc_attr($subcoord['display_email']); ?>"><?php echo esc_html($subcoord['display_email']); ?></a>
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render genre documents.
 */
function ccc_render_coordinator_documents(array $coordinator): string
{
    $documents = $coordinator['document_links'] ?? [];
    $documents = array_filter($documents, fn($d) => !empty($d['title']) || !empty($d['url']));

    if (empty($documents)) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-coordinator-documents" class="ccc-coordinator-documents">
        <h2><?php esc_html_e('Genre Documents', 'owbn-cc-client'); ?></h2>
        <div class="ccc-document-list">
            <?php foreach ($documents as $doc) : ?>
                <div class="ccc-document-item">
                    <div class="ccc-document-title">
                        <?php if (!empty($doc['url'])) : ?>
                            <a href="<?php echo esc_url($doc['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($doc['title'] ?: $doc['url']); ?></a>
                        <?php else : ?>
                            <?php echo esc_html($doc['title']); ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($doc['description'])) : ?>
                        <div class="ccc-document-description"><?php echo esc_html($doc['description']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render email lists.
 */
function ccc_render_coordinator_email_lists(array $coordinator): string
{
    $lists = $coordinator['email_lists'] ?? [];
    $lists = array_filter($lists, fn($l) => !empty($l['list_name']) || !empty($l['email_address']));

    if (empty($lists)) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-coordinator-email-lists" class="ccc-coordinator-email-lists">
        <h2><?php esc_html_e('Email Lists', 'owbn-cc-client'); ?></h2>
        <div class="ccc-email-list">
            <?php foreach ($lists as $list) : ?>
                <div class="ccc-email-item">
                    <div class="ccc-email-name">
                        <?php if (!empty($list['email_address'])) : ?>
                            <a href="mailto:<?php echo esc_attr($list['email_address']); ?>"><?php echo esc_html($list['list_name'] ?: $list['email_address']); ?></a>
                        <?php else : ?>
                            <?php echo esc_html($list['list_name']); ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($list['description'])) : ?>
                        <div class="ccc-email-description"><?php echo wp_kses_post($list['description']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render additional content.
 */
function ccc_render_coordinator_content(array $coordinator): string
{
    $content = $coordinator['content'] ?? '';
    if (empty(trim($content))) {
        return '';
    }

    ob_start();
?>
    <div id="ccc-coordinator-content" class="ccc-coordinator-content">
        <h2><?php esc_html_e('Additional Information', 'owbn-cc-client'); ?></h2>
        <div class="ccc-content"><?php echo wp_kses_post($content); ?></div>
    </div>
<?php
    return ob_get_clean();
}
