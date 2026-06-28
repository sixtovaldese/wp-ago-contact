<?php

namespace AgoLab\Contact\Admin;

use AgoLab\Contact\Submission;

defined( 'ABSPATH' ) || exit;

class Submissions {

    public static function render(): void {
        ?>
        <div class="wrap ago-wrap">
            <div class="ago-header">
                <img src="<?php echo esc_url( AGOCONTACT_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" class="ago-logo">
                <div>
                    <h1><?php esc_html_e( 'Submissions', 'ago-contact' ); ?></h1>
                    <p class="ago-desc"><?php esc_html_e( 'Manage contact form submissions.', 'ago-contact' ); ?></p>
                </div>
            </div>

            <div class="ago-card">
                <div class="ago-submissions-toolbar">
                    <div class="ago-filters">
                        <button class="button ago-filter active" data-status=""><?php esc_html_e( 'All', 'ago-contact' ); ?></button>
                        <button class="button ago-filter" data-status="new"><?php esc_html_e( 'New', 'ago-contact' ); ?></button>
                        <button class="button ago-filter" data-status="read"><?php esc_html_e( 'Read', 'ago-contact' ); ?></button>
                        <button class="button ago-filter" data-status="replied"><?php esc_html_e( 'Replied', 'ago-contact' ); ?></button>
                        <button class="button ago-filter" data-status="spam"><?php esc_html_e( 'Spam', 'ago-contact' ); ?></button>
                    </div>
                    <div class="ago-actions">
                        <button id="ago-export-csv" class="button"><?php esc_html_e( 'Export CSV', 'ago-contact' ); ?></button>
                    </div>
                </div>
            </div>

            <div class="ago-card" id="ago-submissions-list">
                <div class="ago-submissions-loading"><?php esc_html_e( 'Loading...', 'ago-contact' ); ?></div>
                <table class="wp-list-table widefat striped" style="display:none">
                    <thead>
                        <tr>
                            <th style="width:20px"></th>
                            <th><?php esc_html_e( 'Name', 'ago-contact' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'ago-contact' ); ?></th>
                            <th><?php esc_html_e( 'Subject', 'ago-contact' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'ago-contact' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'ago-contact' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'ago-contact' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="ago-submissions-tbody"></tbody>
                </table>
                <div class="ago-submissions-empty" style="display:none">
                    <p><?php esc_html_e( 'No submissions found.', 'ago-contact' ); ?></p>
                </div>
                <div class="ago-pagination" id="ago-pagination"></div>
            </div>

            <div id="ago-submission-modal" class="ago-modal" style="display:none">
                <div class="ago-modal-content">
                    <div class="ago-modal-header">
                        <h2><?php esc_html_e( 'Submission Details', 'ago-contact' ); ?></h2>
                        <button class="ago-modal-close">&times;</button>
                    </div>
                    <div class="ago-modal-body" id="ago-modal-body"></div>
                    <div class="ago-modal-footer">
                        <button class="button ago-mark-replied" data-id=""><?php esc_html_e( 'Mark as Replied', 'ago-contact' ); ?></button>
                        <button class="button ago-mark-spam" data-id=""><?php esc_html_e( 'Mark as Spam', 'ago-contact' ); ?></button>
                        <button class="button ago-delete-sub" data-id=""><?php esc_html_e( 'Delete', 'ago-contact' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
