/**
 * JeiSEO Admin JavaScript
 */

(function($) {
    'use strict';

    const JeiSEO = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Audit
            $('#jeiseo-run-audit').on('click', this.runAudit.bind(this));

            // Content
            $('#jeiseo-content-form').on('submit', this.generateContent.bind(this));
            $('#jeiseo-copy-content').on('click', this.copyContent.bind(this));
            $('#jeiseo-save-draft').on('click', this.saveDraft.bind(this));

            // License
            $('#jeiseo-activate-license').on('click', this.activateLicense.bind(this));
            $('#jeiseo-deactivate-license').on('click', this.deactivateLicense.bind(this));

            // Fix All
            $('#jeiseo-fix-all').on('click', this.fixAll.bind(this));
        },

        // Audit Functions
        runAudit: function(e) {
            e.preventDefault();

            const $button = $('#jeiseo-run-audit');
            const $progress = $('#jeiseo-audit-progress');
            const $results = $('#jeiseo-audit-results');

            $button.prop('disabled', true);
            $progress.show();
            $results.hide();

            $.ajax({
                url: jeiseo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jeiseo_run_audit',
                    nonce: jeiseo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        JeiSEO.displayAuditResults(response.data);
                    } else {
                        JeiSEO.showNotice('error', response.data.message || 'Audit failed');
                    }
                },
                error: function() {
                    JeiSEO.showNotice('error', 'Connection error. Please try again.');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $progress.hide();
                }
            });
        },

        displayAuditResults: function(data) {
            const $results = $('#jeiseo-audit-results');
            const $issuesList = $('#jeiseo-issues-list');

            $('#jeiseo-result-score').text(data.score);
            $('#jeiseo-result-issues').text(data.issues.length);

            $issuesList.empty();

            if (data.issues.length === 0) {
                $issuesList.html('<p class="jeiseo-success-message">No issues found. Great job!</p>');
            } else {
                data.issues.forEach(function(issue) {
                    const severityClass = issue.severity === 'critical' ? 'critical' :
                                         issue.severity === 'warning' ? 'warning' : 'info';

                    const $issue = $('<div class="jeiseo-issue ' + severityClass + '">' +
                        '<div class="jeiseo-issue-content">' +
                            '<div class="jeiseo-issue-title">' + JeiSEO.escapeHtml(issue.title) + '</div>' +
                            '<div class="jeiseo-issue-message">' + JeiSEO.escapeHtml(issue.message) + '</div>' +
                        '</div>' +
                        (issue.fixable ? '<button class="button jeiseo-fix-issue" data-issue="' + issue.code + '">Fix</button>' : '') +
                    '</div>');

                    $issuesList.append($issue);
                });

                // Bind fix buttons
                $('.jeiseo-fix-issue').on('click', function() {
                    JeiSEO.fixIssue($(this).data('issue'), $(this));
                });
            }

            $results.show();
        },

        fixIssue: function(issueCode, $button) {
            $button.prop('disabled', true).text('Fixing...');

            $.ajax({
                url: jeiseo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jeiseo_fix_issue',
                    nonce: jeiseo_ajax.nonce,
                    issue: issueCode
                },
                success: function(response) {
                    if (response.success) {
                        $button.closest('.jeiseo-issue').fadeOut();
                        JeiSEO.showNotice('success', 'Issue fixed successfully');
                    } else {
                        JeiSEO.showNotice('error', response.data.message || 'Could not fix issue');
                        $button.prop('disabled', false).text('Fix');
                    }
                },
                error: function() {
                    JeiSEO.showNotice('error', 'Connection error');
                    $button.prop('disabled', false).text('Fix');
                }
            });
        },

        fixAll: function(e) {
            e.preventDefault();

            const $button = $(e.target);
            $button.prop('disabled', true).text('Fixing all issues...');

            $.ajax({
                url: jeiseo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jeiseo_fix_all',
                    nonce: jeiseo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        JeiSEO.showNotice('success', response.data.message);
                        // Re-run audit to show updated results
                        $('#jeiseo-run-audit').trigger('click');
                    } else {
                        JeiSEO.showNotice('error', response.data.message || 'Could not fix issues');
                    }
                },
                error: function() {
                    JeiSEO.showNotice('error', 'Connection error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Fix All with AI');
                }
            });
        },

        // Content Functions
        generateContent: function(e) {
            e.preventDefault();

            const $form = $('#jeiseo-content-form');
            const $button = $form.find('button[type="submit"]');
            const $progress = $('#jeiseo-content-progress');
            const $result = $('#jeiseo-content-result');

            const formData = {
                action: 'jeiseo_generate_content',
                nonce: jeiseo_ajax.nonce,
                keyword: $('#jeiseo-keyword').val(),
                length: $('#jeiseo-length').val(),
                tone: $('#jeiseo-tone').val()
            };

            if (!formData.keyword) {
                JeiSEO.showNotice('error', 'Please enter a keyword or topic');
                return;
            }

            $button.prop('disabled', true);
            $progress.show();
            $result.hide();

            $.ajax({
                url: jeiseo_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        JeiSEO.displayContent(response.data);
                    } else {
                        JeiSEO.showNotice('error', response.data.message || 'Content generation failed');
                    }
                },
                error: function() {
                    JeiSEO.showNotice('error', 'Connection error. Please try again.');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $progress.hide();
                }
            });
        },

        displayContent: function(data) {
            const $result = $('#jeiseo-content-result');

            $('#jeiseo-post-title').val(data.title);
            $('#jeiseo-content-output').html(data.content);
            $('#jeiseo-content-id').val(data.id || '');

            $result.show();

            // Scroll to result
            $('html, body').animate({
                scrollTop: $result.offset().top - 50
            }, 500);
        },

        copyContent: function(e) {
            e.preventDefault();

            const content = $('#jeiseo-content-output').text();

            if (navigator.clipboard) {
                navigator.clipboard.writeText(content).then(function() {
                    JeiSEO.showNotice('success', 'Content copied to clipboard');
                });
            } else {
                // Fallback
                const $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(content).select();
                document.execCommand('copy');
                $temp.remove();
                JeiSEO.showNotice('success', 'Content copied to clipboard');
            }
        },

        saveDraft: function(e) {
            e.preventDefault();

            const $button = $(e.target);
            const title = $('#jeiseo-post-title').val();
            const content = $('#jeiseo-content-output').html();

            if (!title || !content) {
                JeiSEO.showNotice('error', 'No content to save');
                return;
            }

            $button.prop('disabled', true).text('Saving...');

            $.ajax({
                url: jeiseo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jeiseo_save_draft',
                    nonce: jeiseo_ajax.nonce,
                    title: title,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        JeiSEO.showNotice('success', 'Draft saved successfully');
                        if (response.data.edit_url) {
                            $button.after(' <a href="' + response.data.edit_url + '" class="button">Edit Post</a>');
                        }
                    } else {
                        JeiSEO.showNotice('error', response.data.message || 'Could not save draft');
                    }
                },
                error: function() {
                    JeiSEO.showNotice('error', 'Connection error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Save as Draft');
                }
            });
        },

        // License Functions
        activateLicense: function(e) {
            e.preventDefault();

            const $button = $(e.target);
            const licenseKey = $('#jeiseo-license-key').val();

            if (!licenseKey) {
                JeiSEO.showNotice('error', 'Please enter a license key');
                return;
            }

            $button.prop('disabled', true).text('Activating...');

            $.ajax({
                url: jeiseo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jeiseo_activate_license',
                    nonce: jeiseo_ajax.nonce,
                    license_key: licenseKey
                },
                success: function(response) {
                    if (response.success) {
                        JeiSEO.showNotice('success', 'License activated successfully');
                        location.reload();
                    } else {
                        JeiSEO.showNotice('error', response.data.message || 'Invalid license key');
                    }
                },
                error: function() {
                    JeiSEO.showNotice('error', 'Connection error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Activate');
                }
            });
        },

        deactivateLicense: function(e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to deactivate your license?')) {
                return;
            }

            const $button = $(e.target);
            $button.prop('disabled', true).text('Deactivating...');

            $.ajax({
                url: jeiseo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jeiseo_deactivate_license',
                    nonce: jeiseo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        JeiSEO.showNotice('success', 'License deactivated');
                        location.reload();
                    } else {
                        JeiSEO.showNotice('error', response.data.message || 'Could not deactivate');
                    }
                },
                error: function() {
                    JeiSEO.showNotice('error', 'Connection error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Deactivate');
                }
            });
        },

        // Utility Functions
        showNotice: function(type, message) {
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' +
                this.escapeHtml(message) + '</p></div>');

            $('.wrap.jeiseo-wrap > h1').after($notice);

            // Auto dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        JeiSEO.init();
    });

})(jQuery);
