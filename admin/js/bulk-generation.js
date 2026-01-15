/**
 * Bulk Generation JavaScript
 * 
 * Handles the UI interactions for bulk image generation
 * 
 * @package All_Sources_Images
 */

/* global jQuery, allsiBulkAjax */
jQuery(document).ready(function($) {
    'use strict';

    // =====================
    // State
    // =====================
    let selectedPosts = {
        post: { mode: '', ids: [] },
        page: { mode: '', ids: [] },
        product: { mode: '', ids: [] }
    };
    let currentJobId = null;
    let statusRefreshInterval = null;

    // =====================
    // Utilities
    // =====================
    function debounce(fn, delay) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // =====================
    // Mode Checkbox Handlers
    // =====================
    $(document).on('change', '.allsi-mode-checkbox', function() {
        const $this = $(this);
        const postType = $this.data('type');
        const value = $this.val();
        const isChecked = $this.is(':checked');

        // Uncheck other modes for same post type
        if (isChecked) {
            $(`input[name="allsi_select_${postType}s_mode"]`).not(this).prop('checked', false);
            selectedPosts[postType].mode = value;
            selectedPosts[postType].ids = [];
        } else {
            selectedPosts[postType].mode = '';
            selectedPosts[postType].ids = [];
        }

        // Show/hide accordion
        const $accordion = $(`#allsi-${postType}-accordion`);
        if (value === 'custom' && isChecked) {
            $accordion.slideDown();
            expandAccordionFor(postType);
        } else {
            $accordion.slideUp();
        }

        updateSelectionSummary();
    });

    // =====================
    // Accordion & Tab Loading
    // =====================
    function expandAccordionFor(postType) {
        const collapseSelector = `#collapse-${postType}`;
        const el = document.querySelector(collapseSelector);

        if (el && typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            const inst = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
            inst.show();
        } else {
            $(`button[data-bs-target="${collapseSelector}"]`).trigger('click');
        }

        // Load initial data
        loadItems(postType, 'recent');
        loadItems(postType, 'all', '', 1);

        // Setup search handler
        $(`#search-input-${postType}`).off('keyup.allsi').on('keyup.allsi', debounce(function() {
            loadItems(postType, 'search', $(this).val(), 1);
        }, 300));
    }

    // =====================
    // Load Items via AJAX
    // =====================
    function loadItems(postType, tab, search = '', page = 1, category = '') {
        const $container = $(`#${tab}-items-${postType}`);
        const $pagination = $(`#${tab}-pagination-${postType}`);
        
        if ($container.length === 0) return;

        $container.html('<div class="text-muted p-3"><i class="bi bi-hourglass-split me-2"></i>Loading...</div>');
        if ($pagination.length) $pagination.empty();

        $.ajax({
            url: allsiBulkAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'allsi_bulk_load_items',
                nonce: allsiBulkAjax.nonce,
                post_type: postType,
                tab: tab,
                search: search,
                paged: page,
                category: category
            }
        })
        .done(function(res) {
            if (res && res.success) {
                $container.html(res.data.html || '<p class="text-muted p-3">No items found.</p>');

                // Restore checked state
                $container.find('input[type="checkbox"]').each(function() {
                    const id = parseInt($(this).val());
                    if (selectedPosts[postType].ids.includes(id)) {
                        $(this).prop('checked', true);
                    }
                });

                // Listen for selection changes
                $container.find('input[type="checkbox"]').on('change', function() {
                    handleItemSelection(postType, $(this));
                });

                // Render pagination
                if ((tab === 'all' || tab === 'search' || tab === 'category') && $pagination.length) {
                    renderPagination($pagination, res.data, postType, tab, search, category);
                }
            } else {
                $container.html('<p class="text-danger p-3">Error loading items.</p>');
            }
        })
        .fail(function() {
            $container.html('<p class="text-danger p-3">Network error loading items.</p>');
        });
    }

    function renderPagination($pagination, data, postType, tab, search, category) {
        const maxPages = Number(data.max_pages || 1);
        const currentPage = Number(data.current_page || 1);

        if (maxPages <= 1) {
            $pagination.empty();
            return;
        }

        let html = '';
        if (currentPage > 1) {
            html += `<a href="#" class="allsi-page-link" data-pt="${postType}" data-tab="${tab}" data-page="${currentPage - 1}" data-search="${search}" data-category="${category}">«</a>`;
        }
        
        // Show max 5 page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(maxPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                html += `<span class="current-page">${i}</span>`;
            } else {
                html += `<a href="#" class="allsi-page-link" data-pt="${postType}" data-tab="${tab}" data-page="${i}" data-search="${search}" data-category="${category}">${i}</a>`;
            }
        }

        if (currentPage < maxPages) {
            html += `<a href="#" class="allsi-page-link" data-pt="${postType}" data-tab="${tab}" data-page="${currentPage + 1}" data-search="${search}" data-category="${category}">»</a>`;
        }

        $pagination.html(html);
    }

    // Pagination click handler
    $(document).on('click', '.allsi-page-link', function(e) {
        e.preventDefault();
        const $this = $(this);
        loadItems(
            $this.data('pt'),
            $this.data('tab'),
            $this.data('search') || '',
            $this.data('page'),
            $this.data('category') || ''
        );
    });

    // Category select handler
    $(document).on('change', '.allsi-category-select', function() {
        const postType = $(this).data('post-type');
        const category = $(this).val();
        loadItems(postType, 'category', '', 1, category);
    });

    // =====================
    // Item Selection
    // =====================
    function handleItemSelection(postType, $checkbox) {
        const id = parseInt($checkbox.val());
        const isChecked = $checkbox.is(':checked');

        if (isChecked) {
            if (!selectedPosts[postType].ids.includes(id)) {
                selectedPosts[postType].ids.push(id);
            }
        } else {
            selectedPosts[postType].ids = selectedPosts[postType].ids.filter(i => i !== id);
        }

        updateSelectionSummary();
    }

    // Select All handler
    $(document).on('change', '.allsi-select-all', function() {
        const target = $(this).data('target');
        const isChecked = $(this).is(':checked');
        $(target).find('input[type="checkbox"]').prop('checked', isChecked).trigger('change');
    });

    // =====================
    // Selection Summary
    // =====================
    function updateSelectionSummary() {
        const lines = [];
        let totalCount = 0;

        // Posts
        if (selectedPosts.post.mode === 'all') {
            lines.push('<i class="bi bi-file-text me-1"></i> Posts: <strong>ALL</strong>');
            totalCount = -1; // Indicates we need to count on server
        } else if (selectedPosts.post.mode === 'no_featured') {
            lines.push('<i class="bi bi-file-text me-1"></i> Posts: <strong>Without Featured Image</strong>');
            totalCount = -1;
        } else if (selectedPosts.post.mode === 'custom' && selectedPosts.post.ids.length > 0) {
            lines.push(`<i class="bi bi-file-text me-1"></i> Posts: <strong>${selectedPosts.post.ids.length}</strong> selected`);
            totalCount += selectedPosts.post.ids.length;
        }

        // Pages
        if (selectedPosts.page.mode === 'all') {
            lines.push('<i class="bi bi-file-earmark-text me-1"></i> Pages: <strong>ALL</strong>');
            totalCount = -1;
        } else if (selectedPosts.page.mode === 'no_featured') {
            lines.push('<i class="bi bi-file-earmark-text me-1"></i> Pages: <strong>Without Featured Image</strong>');
            totalCount = -1;
        } else if (selectedPosts.page.mode === 'custom' && selectedPosts.page.ids.length > 0) {
            lines.push(`<i class="bi bi-file-earmark-text me-1"></i> Pages: <strong>${selectedPosts.page.ids.length}</strong> selected`);
            if (totalCount >= 0) totalCount += selectedPosts.page.ids.length;
        }

        // Products
        if (selectedPosts.product.mode === 'all') {
            lines.push('<i class="bi bi-bag-check me-1"></i> Products: <strong>ALL</strong>');
            totalCount = -1;
        } else if (selectedPosts.product.mode === 'no_featured') {
            lines.push('<i class="bi bi-bag-check me-1"></i> Products: <strong>Without Featured Image</strong>');
            totalCount = -1;
        } else if (selectedPosts.product.mode === 'custom' && selectedPosts.product.ids.length > 0) {
            lines.push(`<i class="bi bi-bag-check me-1"></i> Products: <strong>${selectedPosts.product.ids.length}</strong> selected`);
            if (totalCount >= 0) totalCount += selectedPosts.product.ids.length;
        }

        const $summary = $('#allsi-selection-summary');
        if (lines.length > 0) {
            $summary.html(lines.join('<br>'));
            $('#allsi-create-job-btn, #allsi-create-start-btn').prop('disabled', false);
        } else {
            $summary.html('<span class="text-muted">No content selected yet.</span>');
            $('#allsi-create-job-btn, #allsi-create-start-btn').prop('disabled', true);
        }
    }

    // =====================
    // Create Job
    // =====================
    function createJob(startImmediately = false) {
        const jobName = $('#allsi-job-name').val().trim() || ('Bulk Job ' + new Date().toISOString().slice(0, 19).replace('T', ' '));
        const imagesPerPost = parseInt($('#allsi-images-per-post').val()) || 1;

        // Collect selection data
        const selection = {
            posts: { mode: selectedPosts.post.mode, ids: selectedPosts.post.ids },
            pages: { mode: selectedPosts.page.mode, ids: selectedPosts.page.ids },
            products: { mode: selectedPosts.product.mode, ids: selectedPosts.product.ids }
        };

        // Check if anything selected
        const hasSelection = Object.values(selection).some(s => s.mode !== '' || s.ids.length > 0);
        if (!hasSelection) {
            alert(allsiBulkAjax.i18n.no_selection || 'Please select content to generate images for.');
            return;
        }

        // Disable buttons
        $('#allsi-create-job-btn, #allsi-create-start-btn').prop('disabled', true).addClass('loading');

        $.ajax({
            url: allsiBulkAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'allsi_bulk_create_job',
                nonce: allsiBulkAjax.nonce,
                job_name: jobName,
                images_per_post: imagesPerPost,
                selection: JSON.stringify(selection),
                start_immediately: startImmediately ? 1 : 0
            }
        })
        .done(function(res) {
            if (res && res.success) {
                // Reset form
                resetForm();
                
                // Switch to jobs tab using Bootstrap 5 API
                const jobsTabEl = document.querySelector('#jobs-list-tab');
                if (jobsTabEl && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    const tabInstance = bootstrap.Tab.getOrCreateInstance(jobsTabEl);
                    tabInstance.show();
                } else {
                    // Fallback: trigger click
                    $('#jobs-list-tab').trigger('click');
                }
                
                // Load jobs
                loadJobs();

                // If started, show details
                if (startImmediately && res.data.job_id) {
                    currentJobId = res.data.job_id;
                    viewJobDetails(res.data.job_id);
                }
            } else {
                alert(res.data.message || 'Error creating job');
            }
        })
        .fail(function() {
            alert('Network error');
        })
        .always(function() {
            $('#allsi-create-job-btn, #allsi-create-start-btn').prop('disabled', false).removeClass('loading');
        });
    }

    $('#allsi-create-job-btn').on('click', function() {
        createJob(false);
    });

    $('#allsi-create-start-btn').on('click', function() {
        createJob(true);
    });

    function resetForm() {
        // Reset form fields
        $('#allsi-job-name').val('');
        
        // Uncheck all mode checkboxes
        $('.allsi-mode-checkbox').prop('checked', false);
        
        // Hide all accordions
        $('[id^="allsi-"][id$="-accordion"]').hide();
        
        // Reset state
        selectedPosts = {
            post: { mode: '', ids: [] },
            page: { mode: '', ids: [] },
            product: { mode: '', ids: [] }
        };
        
        updateSelectionSummary();
    }

    // =====================
    // Jobs List
    // =====================
    function loadJobs(page = 1) {
        const $tbody = $('#allsi-jobs-tbody');
        // Don't show loading indicator, just fetch and update directly

        $.ajax({
            url: allsiBulkAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'allsi_bulk_get_jobs',
                nonce: allsiBulkAjax.nonce,
                page: page
            }
        })
        .done(function(res) {
            if (res && res.success) {
                renderJobsTable(res.data);
            } else {
                $tbody.html('<tr><td colspan="6" class="text-center text-danger">Error loading jobs</td></tr>');
            }
        })
        .fail(function() {
            $tbody.html('<tr><td colspan="6" class="text-center text-danger">Network error</td></tr>');
        });
    }

    function renderJobsTable(data) {
        const $tbody = $('#allsi-jobs-tbody');
        const jobs = data.jobs || [];

        if (jobs.length === 0) {
            $tbody.html('<tr><td colspan="6" class="text-center text-muted">No jobs found. Create one to get started!</td></tr>');
            return;
        }

        let html = '';
        jobs.forEach(function(job) {
            const progress = job.total_posts > 0 
                ? Math.round((job.processed_posts / job.total_posts) * 100) 
                : 0;

            html += `<tr data-job-id="${job.id}">
                <td>${job.id}</td>
                <td>${escapeHtml(job.job_name)}</td>
                <td><span class="status-badge ${job.job_status}">${job.job_status}</span></td>
                <td>
                    <div class="progress" style="height: 20px; min-width: 100px;">
                        <div class="progress-bar" style="width: ${progress}%">${progress}%</div>
                    </div>
                    <small class="text-muted">${job.processed_posts}/${job.total_posts}</small>
                </td>
                <td><small>${job.created_at}</small></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary allsi-view-job" data-job-id="${job.id}" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    ${job.job_status === 'pending' ? `
                        <button class="btn btn-sm btn-outline-success allsi-start-job" data-job-id="${job.id}" title="Start">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    ` : ''}
                    ${job.job_status === 'processing' ? `
                        <button class="btn btn-sm btn-outline-warning allsi-pause-job" data-job-id="${job.id}" title="Pause">
                            <i class="bi bi-pause-fill"></i>
                        </button>
                    ` : ''}
                    ${job.job_status === 'paused' ? `
                        <button class="btn btn-sm btn-outline-success allsi-resume-job" data-job-id="${job.id}" title="Resume">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    ` : ''}
                    ${['pending', 'processing', 'paused', 'completed', 'failed'].includes(job.job_status) ? `
                        <button class="btn btn-sm btn-outline-danger allsi-delete-job" data-job-id="${job.id}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ` : ''}
                </td>
            </tr>`;
        });

        $tbody.html(html);

        // Render pagination
        renderJobsPagination(data);
    }

    function renderJobsPagination(data) {
        const $pagination = $('#allsi-jobs-pagination');
        const maxPages = data.pages || 1;
        const currentPage = data.page || 1;

        if (maxPages <= 1) {
            $pagination.empty();
            return;
        }

        let html = '<div class="allsi-pagination">';
        if (currentPage > 1) {
            html += `<a href="#" class="allsi-jobs-page" data-page="${currentPage - 1}">«</a>`;
        }
        for (let i = 1; i <= maxPages; i++) {
            if (i === currentPage) {
                html += `<span class="current-page">${i}</span>`;
            } else {
                html += `<a href="#" class="allsi-jobs-page" data-page="${i}">${i}</a>`;
            }
        }
        if (currentPage < maxPages) {
            html += `<a href="#" class="allsi-jobs-page" data-page="${currentPage + 1}">»</a>`;
        }
        html += '</div>';

        $pagination.html(html);
    }

    $(document).on('click', '.allsi-jobs-page', function(e) {
        e.preventDefault();
        loadJobs($(this).data('page'));
    });

    $('#allsi-refresh-jobs').on('click', function() {
        loadJobs();
    });

    // Load jobs when tab is shown
    $('button[data-bs-target="#jobs-list"]').on('shown.bs.tab', function() {
        loadJobs();
    });

    // =====================
    // Job Actions
    // =====================
    $(document).on('click', '.allsi-view-job', function() {
        const jobId = $(this).data('job-id');
        viewJobDetails(jobId);
    });

    $(document).on('click', '.allsi-start-job, .allsi-resume-job', function() {
        const jobId = $(this).data('job-id');
        startJob(jobId);
    });

    $(document).on('click', '.allsi-pause-job', function() {
        const jobId = $(this).data('job-id');
        pauseJob(jobId);
    });

    $(document).on('click', '.allsi-delete-job', function() {
        const jobId = $(this).data('job-id');
        if (confirm(allsiBulkAjax.i18n.confirm_delete || 'Are you sure you want to delete this job?')) {
            deleteJob(jobId);
        }
    });

    $('#allsi-close-job-details').on('click', function() {
        $('#allsi-job-details').slideUp();
        stopStatusRefresh();
    });

    function viewJobDetails(jobId) {
        currentJobId = jobId;
        $('#allsi-job-details').slideDown();
        loadJobDetails(jobId);
        startStatusRefresh(jobId);
    }

    function loadJobDetails(jobId, postsPage = 1) {
        $.ajax({
            url: allsiBulkAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'allsi_bulk_get_job_details',
                nonce: allsiBulkAjax.nonce,
                job_id: jobId,
                posts_page: postsPage
            }
        })
        .done(function(res) {
            if (res && res.success) {
                renderJobDetails(res.data);
            }
        });
    }

    function renderJobDetails(data) {
        const job = data.job;
        const stats = data.stats;
        const posts = data.posts;

        // Title
        $('#allsi-job-details-title').text(`Job #${job.id}: ${job.job_name}`);

        // Progress
        const progress = job.total_posts > 0 
            ? Math.round((job.processed_posts / job.total_posts) * 100) 
            : 0;
        $('#allsi-job-progress-bar').css('width', progress + '%');
        $('#allsi-job-progress-text').text(progress + '%');

        // Stats
        $('#allsi-job-stat-total').text(stats.total);
        $('#allsi-job-stat-pending').text(stats.pending);
        $('#allsi-job-stat-completed').text(stats.completed);
        $('#allsi-job-stat-failed').text(stats.failed);

        // Posts table
        const $tbody = $('#allsi-job-posts-tbody');
        if (posts.posts.length === 0) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted">No posts in this job</td></tr>');
        } else {
            let html = '';
            posts.posts.forEach(function(post) {
                // Build images HTML (supports multiple images)
                let imagesHtml = '-';
                if (post.image_urls && post.image_urls.length > 0) {
                    imagesHtml = '<div style="display: flex; gap: 4px; flex-wrap: wrap;">';
                    post.image_urls.forEach(function(url, index) {
                        imagesHtml += '<img src="' + url + '" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;" title="Image ' + (index + 1) + '">';
                    });
                    imagesHtml += '</div>';
                } else if (post.thumbnail_url) {
                    // Fallback to single thumbnail
                    imagesHtml = '<img src="' + post.thumbnail_url + '" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">';
                } else if (post.featured_image_id) {
                    imagesHtml = '<i class="bi bi-check-circle text-success" title="Image ID: ' + post.featured_image_id + '"></i>';
                }
                
                // Build status HTML with retry info and error details
                let statusHtml = `<span class="status-badge ${post.status}">${post.status}</span>`;
                if (post.retry_count && post.retry_count > 0) {
                    statusHtml += `<br><small class="text-muted">Retry ${post.retry_count}/3</small>`;
                }
                if (post.error_message) {
                    // Show error for failed status, or partial errors for completed with issues
                    if (post.status === 'failed') {
                        statusHtml += `<br><small class="text-danger" title="${escapeHtml(post.error_message)}">⚠ ${escapeHtml(truncateText(post.error_message, 50))}</small>`;
                    } else if (post.status === 'completed' && post.error_message.includes('Partial')) {
                        statusHtml += `<br><small class="text-warning" title="${escapeHtml(post.error_message)}">⚠ Some blocks failed</small>`;
                    }
                }
                
                html += `<tr>
                    <td>
                        <a href="${allsiBulkAjax.edit_url}?post=${post.post_id}&action=edit" target="_blank">
                            ${escapeHtml(post.post_title)}
                        </a>
                    </td>
                    <td>${post.post_type}</td>
                    <td>${statusHtml}</td>
                    <td>${imagesHtml}</td>
                    <td>${post.image_source || '-'}</td>
                </tr>`;
            });
            $tbody.html(html);
        }

        // Posts pagination
        renderJobPostsPagination(posts);
    }

    function renderJobPostsPagination(posts) {
        const $pagination = $('#allsi-job-posts-pagination');
        const maxPages = posts.pages || 1;
        const currentPage = posts.page || 1;

        if (maxPages <= 1) {
            $pagination.empty();
            return;
        }

        let html = '<div class="allsi-pagination">';
        if (currentPage > 1) {
            html += `<a href="#" class="allsi-job-posts-page" data-page="${currentPage - 1}">«</a>`;
        }
        for (let i = 1; i <= Math.min(maxPages, 10); i++) {
            if (i === currentPage) {
                html += `<span class="current-page">${i}</span>`;
            } else {
                html += `<a href="#" class="allsi-job-posts-page" data-page="${i}">${i}</a>`;
            }
        }
        if (currentPage < maxPages) {
            html += `<a href="#" class="allsi-job-posts-page" data-page="${currentPage + 1}">»</a>`;
        }
        html += '</div>';

        $pagination.html(html);
    }

    $(document).on('click', '.allsi-job-posts-page', function(e) {
        e.preventDefault();
        if (currentJobId) {
            loadJobDetails(currentJobId, $(this).data('page'));
        }
    });

    // =====================
    // Job Control
    // =====================
    function startJob(jobId) {
        $.ajax({
            url: allsiBulkAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'allsi_bulk_start_job',
                nonce: allsiBulkAjax.nonce,
                job_id: jobId
            }
        })
        .done(function(res) {
            if (res && res.success) {
                loadJobs();
                if (currentJobId === jobId) {
                    startStatusRefresh(jobId);
                }
            } else {
                alert(res.data.message || 'Error starting job');
            }
        });
    }

    function pauseJob(jobId) {
        $.ajax({
            url: allsiBulkAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'allsi_bulk_pause_job',
                nonce: allsiBulkAjax.nonce,
                job_id: jobId
            }
        })
        .done(function(res) {
            if (res && res.success) {
                loadJobs();
                stopStatusRefresh();
            }
        });
    }

    function deleteJob(jobId) {
        $.ajax({
            url: allsiBulkAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'allsi_bulk_delete_job',
                nonce: allsiBulkAjax.nonce,
                job_id: jobId
            }
        })
        .done(function(res) {
            if (res && res.success) {
                loadJobs();
                if (currentJobId === jobId) {
                    $('#allsi-job-details').slideUp();
                    stopStatusRefresh();
                }
            }
        });
    }

    // =====================
    // Status Refresh
    // =====================
    function startStatusRefresh(jobId) {
        stopStatusRefresh();
        statusRefreshInterval = setInterval(function() {
            loadJobDetails(jobId);
            loadJobs(); // Also refresh the list
        }, 3000);
    }

    function stopStatusRefresh() {
        if (statusRefreshInterval) {
            clearInterval(statusRefreshInterval);
            statusRefreshInterval = null;
        }
    }

    // =====================
    // Helpers
    // =====================
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function truncateText(text, maxLength) {
        if (!text) return '';
        text = String(text);
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    // =====================
    // Auto-generate from URL (bulk action from posts list)
    // =====================
    function checkAutoGenerate() {
        const urlParams = new URLSearchParams(window.location.search);
        const autoIds = urlParams.get('auto_generate_ids');
        
        if (!autoIds) return;
        
        // Show loading overlay
        const $overlay = $(`
            <div id="allsi-auto-generate-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255,255,255,0.95);
                z-index: 99999;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            ">
                <div style="text-align: center;">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h4>${allsiBulkAjax.i18n.creating_job || 'Creating job...'}</h4>
                    <p class="text-muted">${allsiBulkAjax.i18n.please_wait || 'Please wait while we set up your image generation job.'}</p>
                </div>
            </div>
        `);
        $('body').append($overlay);
        
        // Create job via AJAX
        $.ajax({
            url: allsiBulkAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'allsi_bulk_create_job_from_ids',
                nonce: allsiBulkAjax.nonce,
                post_ids: autoIds
            }
        })
        .done(function(res) {
            if (res && res.success) {
                // Remove URL parameter to prevent re-triggering
                const newUrl = window.location.pathname + '?page=allsi-new-bulk-generation';
                window.history.replaceState({}, document.title, newUrl);
                
                // Switch to jobs tab
                const jobsTabEl = document.querySelector('#jobs-list-tab');
                if (jobsTabEl && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    const tabInstance = bootstrap.Tab.getOrCreateInstance(jobsTabEl);
                    tabInstance.show();
                } else {
                    $('#jobs-list-tab').trigger('click');
                }
                
                // Load jobs and show details
                loadJobs();
                
                if (res.data.job_id) {
                    currentJobId = res.data.job_id;
                    setTimeout(function() {
                        viewJobDetails(res.data.job_id);
                    }, 500);
                }
                
                // Remove overlay
                $overlay.fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                $overlay.find('h4').text(allsiBulkAjax.i18n.error || 'Error');
                $overlay.find('p').text(res.data.message || 'Failed to create job');
                $overlay.find('.spinner-border').hide();
                
                setTimeout(function() {
                    $overlay.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        })
        .fail(function() {
            $overlay.find('h4').text(allsiBulkAjax.i18n.error || 'Error');
            $overlay.find('p').text(allsiBulkAjax.i18n.network_error || 'Network error');
            $overlay.find('.spinner-border').hide();
            
            setTimeout(function() {
                $overlay.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        });
    }

    // =====================
    // Initialize
    // =====================
    // Check for auto-generate from URL
    checkAutoGenerate();
    
    // Load jobs on page load if on jobs tab
    if ($('#jobs-list').hasClass('active')) {
        loadJobs();
    }
});
