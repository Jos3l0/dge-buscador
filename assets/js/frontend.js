/**
 * DGE Buscador - Frontend JavaScript
 */
(function($) {
    'use strict';
    
    // Alert to verify script is running
    console.log('DGE Buscador: Script loaded');
    
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    class DGEBuscador {
        constructor($container) {
            console.log('DGE Buscador: Widget initializing');
            
            this.$container = $container;
            this.$searchInput = $container.find('.dge-buscador__search-input');
            this.$filters = $container.find('.dge-buscador__filters');
            this.$resultsGrid = $container.find('.dge-buscador__results-grid');
            this.$loading = $container.find('.dge-buscador__loading');
            this.$noResults = $container.find('.dge-buscador__no-results');
            this.$pagination = $container.find('.dge-buscador__pagination');
            this.$resultsCount = $container.find('.dge-buscador__results-count');
            this.$resultsText = $container.find('.dge-buscador__results-text');
            this.$sortSelect = $container.find('.dge-buscador__sort-select');
            this.$clearFiltersBtn = $container.find('.dge-buscador__filters-clear');

            this.settings = {
                post_type: $container.data('post_type') || 'recurso',
                per_page: parseInt($container.data('per_page')) || 12,
                columns: parseInt($container.data('columns')) || 3,
                show_search: ($container.data('show_search') + '') === '1' || $container.data('show_search') === true,
                show_filters: ($container.data('show_filters') + '') === '1' || $container.data('show_filters') === true,
                show_sort: ($container.data('show_sort') + '') === '1' || $container.data('show_sort') === true,
                taxonomias: $container.data('taxonomias') || ''
            };

            this.currentPage = 1;
            this.currentSearch = '';
            this.currentSort = 'date_desc';
            this.currentTaxonomies = {};

            console.log('DGE Buscador: Settings loaded', this.settings);
            this.init();
        }

        init() {
            console.log('DGE Buscador: init called');
            this.bindEvents();
            this.initialLoad();
        }

        bindEvents() {
            console.log('DGE Buscador: Binding events');
            
            if (this.settings.show_search && this.$searchInput.length) {
                console.log('DGE Buscador: Binding search input');
                const debouncedSearch = debounce(() => this.search(), 300);
                
                // Direct event binding for testing
                this.$searchInput.on('input', (e) => {
                    console.log('DGE Buscador: Input detected:', $(e.target).val());
                    this.search();
                });

                this.$container.find('.dge-buscador__search-clear').on('click', () => {
                    console.log('DGE Buscador: Clear button clicked');
                    this.$searchInput.val('');
                    this.search();
                });
            }

            if (this.settings.show_filters && this.$filters.length) {
                this.$filters.find('input[type="checkbox"]').on('change', () => {
                    this.updateTaxonomiesFromCheckboxes();
                    this.search();
                });

                this.$filters.find('select').on('change', () => {
                    this.updateTaxonomiesFromSelects();
                    this.search();
                });

                this.$clearFiltersBtn.on('click', () => this.clearFilters());
            }

            if (this.settings.show_sort && this.$sortSelect.length) {
                this.$sortSelect.on('change', () => {
                    this.currentSort = this.$sortSelect.val();
                    this.search();
                });
            }
        }

        updateTaxonomiesFromCheckboxes() {
            this.currentTaxonomies = {};
            this.$filters.find('.dge-buscador__filter-options--checkboxes').each((i, group) => {
                const $group = $(group);
                const taxonomy = $group.closest('.dge-buscador__filter-group').data('taxonomy');
                const checked = $group.find('input:checked').map((_, el) => $(el).val()).get();
                if (checked.length > 0) {
                    this.currentTaxonomies[taxonomy] = checked;
                }
            });
        }

        updateTaxonomiesFromSelects() {
            this.$filters.find('.dge-buscador__filter-select').each((i, select) => {
                const $select = $(select);
                const taxonomy = $select.attr('name');
                const value = $select.val();
                if (value) {
                    this.currentTaxonomies[taxonomy] = [value];
                } else if (this.currentTaxonomies[taxonomy]) {
                    delete this.currentTaxonomies[taxonomy];
                }
            });
        }

        clearFilters() {
            this.$filters.find('input:checked').prop('checked', false);
            this.$filters.find('select').val('');
            this.currentTaxonomies = {};
            this.search();
        }

        initialLoad() {
            console.log('DGE Buscador: Initial load');
            this.search();
        }

        search(page = 1) {
            console.log('DGE Buscador: Search called, page:', page);
            this.currentPage = page;

            this.$resultsGrid.hide();
            this.$noResults.hide();
            this.$loading.show();
            this.$container.addClass('dge-buscador--loading');

            const data = {
                action: 'dge_buscador_search',
                nonce: dgeBuscadorData.nonce,
                search: this.$searchInput.val() || '',
                post_type: this.settings.post_type,
                taxonomies: this.currentTaxonomies,
                sort: this.currentSort,
                page: this.currentPage,
                per_page: this.settings.per_page
            };

            console.log('DGE Buscador: AJAX data:', data);

            $.ajax({
                url: dgeBuscadorData.ajaxUrl,
                method: 'POST',
                data: data,
                success: (response) => {
                    console.log('DGE Buscador: Response:', response);
                    if (response.success) {
                        this.renderResults(response.data);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.log('DGE Buscador: Error:', error);
                    this.showError('Error: ' + error);
                }
            });
        }

        renderResults(data) {
            console.log('DGE Buscador: Rendering', data);
            this.$loading.hide();
            this.$container.removeClass('dge-buscador--loading');

            const countText = data.total === 1 ? 'result' : 'results';
            this.$resultsCount.text(data.total);
            this.$resultsText.text(countText);

            if (data.posts.length === 0) {
                this.$resultsGrid.hide();
                this.$noResults.show();
                this.$pagination.hide();
                return;
            }

            this.$noResults.hide();
            this.$resultsGrid.show();
            this.$resultsGrid.html(data.posts.map(post => this.renderCard(post)).join(''));

            if (data.pages > 1) {
                this.renderPagination(data);
            } else {
                this.$pagination.hide();
            }
        }

        renderCard(post) {
            const thumbnailHtml = post.thumbnail 
                ? `<div class="dge-buscador__card-image"><img src="${post.thumbnail}" alt="${post.title}" loading="lazy"></div>`
                : `<div class="dge-buscador__card-image"><div class="dge-buscador__card-image-placeholder"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg></div></div>`;

            let termsHtml = '';
            for (const tax in post.terms) {
                if (post.terms[tax].length > 0) {
                    termsHtml += `<span class="dge-buscador__card-term">${post.terms[tax][0].name}</span>`;
                }
            }

            return `<article class="dge-buscador__card"><a href="${post.url}">${thumbnailHtml}<div class="dge-buscador__card-body"><h3 class="dge-buscador__card-title"><a href="${post.url}">${post.title}</a></h3><p class="dge-buscador__card-excerpt">${post.excerpt}</p><div class="dge-buscador__card-meta">${termsHtml}</div><span class="dge-buscador__card-date">${post.date}</span></div></a></article>`;
        }

        renderPagination(data) {
            this.$pagination.show();
            let html = '';
            if (data.current_page > 1) {
                html += '<button class="dge-buscador__pagination-btn" data-page="' + (data.current_page - 1) + '">‹</button>';
            }
            for (let i = 1; i <= data.pages; i++) {
                const activeClass = i === data.current_page ? ' dge-buscador__pagination-btn--active' : '';
                html += '<button class="dge-buscador__pagination-btn' + activeClass + '" data-page="' + i + '">' + i + '</button>';
            }
            if (data.current_page < data.pages) {
                html += '<button class="dge-buscador__pagination-btn" data-page="' + (data.current_page + 1) + '">›</button>';
            }
            this.$pagination.html(html);
            this.$pagination.find('button[data-page]').on('click', (e) => {
                this.search(parseInt($(e.target).data('page')));
            });
        }

        showError(message) {
            this.$loading.hide();
            this.$resultsGrid.hide();
            this.$noResults.show().find('p').text(message);
        }
    }

    $(document).ready(function() {
        console.log('DGE Buscador: Document ready');
        $('.dge-buscador').each(function() {
            console.log('DGE Buscador: Creating widget');
            new DGEBuscador($(this));
        });
    });

})(jQuery);
