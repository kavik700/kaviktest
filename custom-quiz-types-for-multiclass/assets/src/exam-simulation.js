var QuizStatisticsFilter = {
    $: null, // Will hold the jQuery object

    init: function(jQuery) {
        this.$ = jQuery;

        // Bail if we're not on the correct page
        if (!this.$('body').hasClass('sfwd-courses-template-default') || !this.$('body').hasClass('exam-simulation-active')) {
            return;
        }

        this.$(document).ready(this.onDocumentReady.bind(this));
    },

    onDocumentReady: function() {
        this.$('body').on('learndash-statistics-contentchanged', this.onContentChanged.bind(this));

        // Use setTimeout to delay our code execution
        setTimeout(this.setupStatisticClickHandler.bind(this), 100); // 100ms delay, adjust if needed

        // Add click handler for .btn-repeat
        this.$('#btn-repeat').on('click', this.handleRepeatClick.bind(this));
    },

    setupStatisticClickHandler: function() {
        // Remove all click events from all .learndash-wrapper elements
        this.$('.learndash-wrapper').off('click');

        // Find the innermost .learndash-wrapper
        var $innermostWrapper = this.$('.learndash-wrapper').last();

        // Attach our custom click handler to the innermost .learndash-wrapper
        $innermostWrapper.on('click', 'a.user_statistic', this.handleStatisticClick.bind(this));
    },

    onContentChanged: function() {
        this.checkContentAndAddCheckboxes();
    },

    addCheckboxes: function() {
        var $tableElement = this.$('.wpProQuiz_modal_window .wp-list-table');
        if ($tableElement.length && !this.$('#filter-checkboxes').length) {
            var $checkboxesDiv = this.$('<div>', { id: 'filter-checkboxes' });
            $checkboxesDiv.html(`
                <label for="wrong-questions"><input type="checkbox" id="wrong-questions" checked> ${customQuizAjax.i18n.wrongQuestions}</label>
                <label for="correct-questions"><input type="checkbox" id="correct-questions"> ${customQuizAjax.i18n.correctQuestions}</label>
            `);
            $tableElement.before($checkboxesDiv);

            this.$('#wrong-questions, #correct-questions').on('change', this.filterQuestions.bind(this));
            this.filterQuestions();
        }
    },

    filterQuestions: function() {
        var self = this;
        this.$('.wpProQuiz_modal_window .wp-list-table tbody tr').each(function() {
            var $row = self.$(this);
            var $cells = $row.find('th');
            
            // Assuming correct column is the third (index 2) and incorrect is the fourth (index 3)
            var correctPercentage = self.parsePercentage($cells.eq(3).text());
            var incorrectPercentage = self.parsePercentage($cells.eq(4).text());
            
            var isCorrect = correctPercentage > 0;
            var isIncorrect = incorrectPercentage > 0;
            
            var showWrong = self.$('#wrong-questions').is(':checked');
            var showCorrect = self.$('#correct-questions').is(':checked');

            if ((isCorrect && showCorrect) || (isIncorrect && showWrong)) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    },

    parsePercentage: function(text) {
        var match = text.match(/(\d+(?:\.\d+)?)\s*%/);
        return match ? parseFloat(match[1]) : 0;
    },

    checkContentAndAddCheckboxes: function() {
        if (this.$('.wpProQuiz_modal_window .wp-list-table').is(':visible')) {
            this.addCheckboxes();
        }
    },

    handleStatisticClick: function(e) {
        e.preventDefault();
        e.stopPropagation();

        var refId = this.$(e.currentTarget).data('ref-id');
        var quizId = this.$(e.currentTarget).data('quiz-id');
        var userId = this.$(e.currentTarget).data('user-id');
        var statistic_nonce = this.$(e.currentTarget).data('statistic-nonce');
        var post_data = {
            action: 'wp_pro_quiz_admin_ajax_statistic_load_user',
            func: 'statisticLoadUser',
            data: {
                quizId,
                userId,
                refId,
                statistic_nonce,
                avg: 0,
            },
        };

        this.$('#wpProQuiz_user_overlay, #wpProQuiz_loadUserData').show();
        var content = this.$('#wpProQuiz_user_content').hide();

        this.$.ajax({
            type: 'POST',
            url: ldVars.ajaxurl,
            dataType: 'json',
            cache: false,
            data: post_data,
            error(jqXHR, textStatus, errorThrown) {},
            success: function(reply_data) {
                if ('undefined' !== typeof reply_data.html) {
                    content.html(reply_data.html);
                    this.$('#wpProQuiz_user_content').show();

                    this.$('body').trigger('learndash-statistics-contentchanged');

                    this.$('#wpProQuiz_loadUserData').hide();

                    var mcGraded = this.$(e.currentTarget).attr('data-mc_graded') === 'yes';
                        
                    var $u_content = jQuery('#wpProQuiz_user_content');
                    var $gradeElements = $u_content.find('thead > tr > th:nth-child(3), thead > tr > th:nth-child(5), thead > tr > th:nth-child(6), thead > tr > th:nth-child(8), thead > tr > th:nth-child(9), tbody > tr > th:nth-child(3), tbody > tr > th:nth-child(5), tbody > tr > th:nth-child(6), tbody > tr > th:nth-child(8), tbody > tr > th:nth-child(9), tfoot > tr > th:nth-child(3), tfoot > tr > th:nth-child(5), tfoot > tr > th:nth-child(6), tfoot > tr > th:nth-child(8), tfoot > tr > th:nth-child(9)');

                    if( ! mcGraded ) {
                      $gradeElements.hide();
                    }else{
                      $gradeElements.show();
                    }

                    var $jq = this.$;
                    content.find('.statistic_data').on('click', function() {
                        $jq(this).parents('tr').next().toggle('fast');
                        return false;
                    });
                }
            }.bind(this),
        });

        this.$('#wpProQuiz_overlay_close').on('click', function () {
            this.$('#wpProQuiz_user_overlay').hide();
        }.bind(this));
    },

    handleRepeatClick: function(e) {
        e.preventDefault();
        
        // Get user ID and course ID from the page
        var userId = this.$('body').data('user-id');
        var courseId = this.$('body').data('course-id');
        
        if (!userId || !courseId) {
            alert('User ID or Course ID not found');
            return;
        }

        // Show loading state
        var $button = this.$(e.currentTarget);
        var originalText = $button.text();
        $button.prop('disabled', true).text('Wird verarbeitet...');

        // Make AJAX call
        this.$.ajax({
            url: ldVars.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_and_archive_quiz_data',
                nonce: customQuizAjax.delete_and_archive_quiz_data_nonce,
                user_id: userId,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    // Reload the page after successful deletion
                    const url = new URL(window.location.href);
                    url.searchParams.delete('archived');
                    url.searchParams.delete('only-answers');
                    window.location.href = url.toString();
                } else {
                    alert(response.data || 'Failed to delete quiz data');
                    $button.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred while processing your request');
                $button.prop('disabled', false).text(originalText);
            }
        });
    }
};

// Usage:
QuizStatisticsFilter.init(jQuery);

