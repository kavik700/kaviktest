<?php

defined('ABSPATH') || exit;
?>

<div id="report-problem-container" data-resource-id="<?php echo esc_attr( $resource_id ); ?>" data-resource-type="<?php echo esc_attr( $resource_type ); ?>">
    <a href="#report-problem-form" rel="modal:open">
        <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#004aad">
            <path d="M14 14V6M14 14L20.1023 17.487C20.5023 17.7156 21 17.4268 21 16.9661V3.03391C21 2.57321 20.5023 2.28439 20.1023 2.51296L14 6M14 14H7C4.79086 14 3 12.2091 3 10V10C3 7.79086 4.79086 6 7 6H14" stroke="#004aad" stroke-width="1.5"/>
            <path d="M7.75716 19.3001L7 14H11L11.6772 18.7401C11.8476 19.9329 10.922 21 9.71716 21C8.73186 21 7.8965 20.2755 7.75716 19.3001Z" stroke="#004aad" stroke-width="1.5"/>
        </svg>
    </a>
    <form id="report-problem-form">
        <!-- Loading State -->
        <div class="report-problem-loading-wrapper" style="display: none;">
            <div class="report-problem-loading-text">
                <?php echo esc_html__( 'Dein Hinweis wird gesendet...', 'custom-quiz-types-for-multiclass' ); ?>
            </div>
            <div class="report-problem-loading-bar">
                <div class="report-problem-loading-bar-fill"></div>
            </div>
        </div>

        <!-- Success Notification -->
        <div class="report-problem-notification report-problem-notification-success" style="display: none;">
            <?php echo esc_html__( 'Your report has been submitted successfully.', 'custom-quiz-types-for-multiclass' ); ?>
        </div>

        <!-- Error Notification -->
        <div class="report-problem-notification report-problem-notification-error" style="display: none;">
            <?php echo esc_html__( 'An error occurred while submitting your report. Please try again. If the error persists, send an email to info@multiclass.ch detailing your issue.', 'custom-quiz-types-for-multiclass' ); ?>
        </div>

        <h2 id="popup-title"><?php echo esc_html__( 'Report a Problem', 'custom-quiz-types-for-multiclass' ); ?></h2>
        <p><?php echo esc_html__( "Help us improve by letting us know what's wrong. Select the issue from the options below.", 'custom-quiz-types-for-multiclass' ); ?></p>

        <div class="checkbox-group">
            <?php foreach( $report_problem_options as $key => $option ) { ?>
                <div class="checkbox-button">
                    <input type="checkbox" id="<?php echo esc_attr( $key ); ?>" name="issues[]" value="<?php echo esc_attr( $option ); ?>">
                    <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option ); ?></label>
                </div>
            <?php } ?>
        </div>
        <div id="no-selection-notification">
            <?php echo esc_html__( 'Please select at least one issue.', 'custom-quiz-types-for-multiclass' ); ?>
        </div>

        <textarea name="report-problem-text" placeholder="<?php echo esc_attr__( 'Please describe the issue in detail. (Optional)', 'custom-quiz-types-for-multiclass' ); ?>"></textarea>

        <div class="report-problem-buttons">
            <button class="report-problem-send" type="submit">
                <span class="btn-text"><?php echo esc_html__( 'Send', 'custom-quiz-types-for-multiclass' ); ?></span>
                <div class="spinner"></div>
            </button>
            <a href="#" class="report-problem-cancel" type="button" rel="modal:close"><?php echo esc_html__( 'Cancel', 'custom-quiz-types-for-multiclass' ); ?></a>
        </div>
    </form>
</div>