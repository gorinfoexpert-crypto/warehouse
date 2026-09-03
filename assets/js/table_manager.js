/**
 * Universal Data Table Enhancer
 * Provides:
 * 1. Sorting (Сортировка) - Click to sort Asc / Desc with smart numeric, date, and text detection.
 * 2. Drag & Drop Column Reordering (Перенос колонок) with localStorage persistence.
 * 3. Column Visibility / Hiding (Скрытие колонок) with dropdown checkbox menu and localStorage persistence.
 * 4. Premium modern Metrika/SaaS header design with grip handles and illuminated sort indicators.
 */

(function() {
    'use strict';

    class EnhancedTable {
        constructor(tableId, options = {}) {
            this.table = typeof tableId === 'string' ? document.getElementById(tableId) : tableId;
            if (!this.table) return;

            this.tableId = this.table.id || 'tbl_' + Math.random().toString(36).substr(2, 9);
            this.options = Object.assign({
                storageKeyPrefix: 'sklad_table_',
                sortable: true,
                reorderable: true,
                hidable: true,
                toolbarContainer: null,
                buttonText: 'Սյուներ',
                onSort: null,
                onReorder: null,
                onVisibilityChange: null,
                nonHidableCols: [0] // Don't allow hiding main ID/Name
            }, options);

            this.storageKeyOrder = this.options.storageKeyPrefix + 'order_' + this.tableId;
            this.storageKeyHidden = this.options.storageKeyPrefix + 'hidden_' + this.tableId;

            this.currentSort = { colKey: null, direction: 'neutral' }; // 'asc', 'desc', 'neutral'
            this.columns = [];
            this.dropdownEl = null;

            this.init();
        }

        init() {
            this.table.classList.add('enhanced-table');
            this.scanColumns();
            this.decorateHeaders();
            this.createVisibilityDropdown();
            this.loadPreferences();
            this.applyPreferences();
        }

        /**
         * Scan <th> elements and register initial columns
         */
        scanColumns() {
            const thead = this.table.querySelector('thead');
            if (!thead) return;

            const headerRow = thead.querySelector('tr');
            if (!headerRow) return;

            const ths = Array.from(headerRow.querySelectorAll('th'));
            this.columns = ths.map((th, idx) => {
                const key = th.getAttribute('data-col') || th.getAttribute('data-sort') || 'col_' + idx;
                th.setAttribute('data-col-key', key);
                th.setAttribute('data-orig-idx', idx);

                const cleanTitle = th.innerText.replace(/[⇅▲▼⋮⋮]/g, '').trim();
                return {
                    key: key,
                    origIdx: idx,
                    title: cleanTitle || ('Սյուն ' + (idx + 1)),
                    element: th,
                    align: th.classList.contains('text-right') ? 'right' : (th.classList.contains('text-center') ? 'center' : 'left'),
                    hidable: !this.options.nonHidableCols.includes(idx) && idx !== (ths.length - 1)
                };
            });
        }

        /**
         * Decorate <th> elements with modern grip handles, title, and illuminated sort arrows
         */
        decorateHeaders() {
            const thead = this.table.querySelector('thead');
            if (!thead) return;

            const ths = Array.from(thead.querySelectorAll('tr th'));
            ths.forEach((th, idx) => {
                const colInfo = this.columns.find(c => c.key === th.getAttribute('data-col-key')) || { title: th.innerText.trim() };
                const rawTitle = colInfo.title;

                th.innerHTML = `
                    <div class="th-content-wrapper">
                        <span class="th-drag-handle" title="Տեղափոխել սյունը (Drag to reorder)">
                            <svg width="10" height="14" viewBox="0 0 10 16" fill="currentColor">
                                <circle cx="3" cy="2" r="1.3"/>
                                <circle cx="7" cy="2" r="1.3"/>
                                <circle cx="3" cy="8" r="1.3"/>
                                <circle cx="7" cy="8" r="1.3"/>
                                <circle cx="3" cy="14" r="1.3"/>
                                <circle cx="7" cy="14" r="1.3"/>
                            </svg>
                        </span>
                        <span class="th-label-text">${rawTitle}</span>
                        <span class="th-sort-indicator" title="Սորտավորել">
                            <span class="sort-arrow arrow-asc">▲</span>
                            <span class="sort-arrow arrow-desc">▼</span>
                        </span>
                    </div>
                `;

                // Sorting Click Handler
                if (this.options.sortable) {
                    th.style.cursor = 'pointer';
                    th.addEventListener('click', (e) => {
                        // Ignore if dragging or clicking drag handle specifically
                        if (e.target.closest('.th-drag-handle') || this.isDragging) return;
                        this.handleHeaderClick(th);
                    });
                }

                // Drag and Drop Reordering Handlers
                if (this.options.reorderable) {
                    th.setAttribute('draggable', 'true');
                    th.addEventListener('dragstart', (e) => this.onDragStart(e, th));
                    th.addEventListener('dragover', (e) => this.onDragOver(e, th));
                    th.addEventListener('dragleave', (e) => this.onDragLeave(e, th));
                    th.addEventListener('drop', (e) => this.onDrop(e, th));
                    th.addEventListener('dragend', (e) => this.onDragEnd(e, th));
                }
            });
        }

        /**
         * Handle Column Sorting
         */
        handleHeaderClick(th) {
            const colKey = th.getAttribute('data-col-key');
            let newDirection = 'asc';

            if (this.currentSort.colKey === colKey) {
                if (this.currentSort.direction === 'asc') {
                    newDirection = 'desc';
                } else if (this.currentSort.direction === 'desc') {
                    newDirection = 'neutral';
                } else {
                    newDirection = 'asc';
                }
            }

            this.currentSort = { colKey: newDirection === 'neutral' ? null : colKey, direction: newDirection };
            this.updateSortIndicators();

            if (typeof this.options.onSort === 'function') {
                this.options.onSort(this.currentSort.colKey, this.currentSort.direction);
            } else {
                this.sortDomRows();
            }
        }

        updateSortIndicators() {
            const ths = this.table.querySelectorAll('thead th');
            ths.forEach(th => {
                th.classList.remove('sorted-asc', 'sorted-desc');
                const key = th.getAttribute('data-col-key');
                if (this.currentSort.colKey === key) {
                    if (this.currentSort.direction === 'asc') {
                        th.classList.add('sorted-asc');
                    } else if (this.currentSort.direction === 'desc') {
                        th.classList.add('sorted-desc');
                    }
                }
            });
        }

        /**
         * Sort DOM Rows in <tbody>
         */
        sortDomRows() {
            const tbody = this.table.querySelector('tbody');
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('tr')).filter(tr => !tr.classList.contains('no-sort') && tr.children.length > 1);
            if (rows.length === 0) return;

            if (this.currentSort.direction === 'neutral' || !this.currentSort.colKey) {
                // Restore original rows order if saved
                if (this._originalRows) {
                    this._originalRows.forEach(r => tbody.appendChild(r));
                }
                return;
            }

            if (!this._originalRows) {
                this._originalRows = [...rows];
            }

            // Find current DOM column index of the sorted header
            const th = this.table.querySelector(`thead th[data-col-key="${this.currentSort.colKey}"]`);
            if (!th) return;

            const colIndex = Array.from(th.parentNode.children).indexOf(th);
            const isDesc = this.currentSort.direction === 'desc';

            rows.sort((a, b) => {
                const cellA = a.children[colIndex];
                const cellB = b.children[colIndex];
                if (!cellA || !cellB) return 0;

                const valA = (cellA.getAttribute('data-sort-value') || cellA.innerText).trim();
                const valB = (cellB.getAttribute('data-sort-value') || cellB.innerText).trim();

                // Clean numbers
                const numA = this.parseNumeric(valA);
                const numB = this.parseNumeric(valB);

                if (numA !== null && numB !== null) {
                    return isDesc ? numB - numA : numA - numB;
                }

                // Clean Dates
                const dateA = this.parseDate(valA);
                const dateB = this.parseDate(valB);
                if (dateA && dateB) {
                    return isDesc ? dateB - dateA : dateA - dateB;
                }

                // Strings comparison
                const cmp = valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
                return isDesc ? -cmp : cmp;
            });

            rows.forEach(r => tbody.appendChild(r));
        }

        parseNumeric(str) {
            if (!str) return null;
            // Remove currency symbols, commas, spaces, units
            const cleaned = str.replace(/[֏\$\€\%\s]/g, '')
                               .replace(/հատ|օր|AMD|֏/gi, '')
                               .replace(/,/g, '')
                               .trim();
            if (/^-?\d+(\.\d+)?$/.test(cleaned)) {
                return parseFloat(cleaned);
            }
            return null;
        }

        parseDate(str) {
            if (!str || str.length < 8) return null;
            // Matches YYYY-MM-DD or DD.MM.YYYY
            const iso = str.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (iso) return new Date(iso[0]).getTime();

            const dmy = str.match(/^(\d{2})\.(\d{2})\.(\d{4})/);
            if (dmy) return new Date(`${dmy[3]}-${dmy[2]}-${dmy[1]}`).getTime();

            return null;
        }

        /**
         * Drag & Drop Column Reordering Implementation
         */
        onDragStart(e, th) {
            this.isDragging = true;
            this.draggedTh = th;
            th.classList.add('th-is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', th.getAttribute('data-col-key'));
        }

        onDragOver(e, th) {
            if (!this.draggedTh || this.draggedTh === th) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const rect = th.getBoundingClientRect();
            const relX = e.clientX - rect.left;
            th.classList.remove('drop-target-left', 'drop-target-right');

            if (relX < rect.width / 2) {
                th.classList.add('drop-target-left');
            } else {
                th.classList.add('drop-target-right');
            }
        }

        onDragLeave(e, th) {
            th.classList.remove('drop-target-left', 'drop-target-right');
        }

        onDrop(e, th) {
            e.preventDefault();
            if (!this.draggedTh || this.draggedTh === th) return;

            const parentRow = th.parentNode;
            const ths = Array.from(parentRow.children);
            const fromIdx = ths.indexOf(this.draggedTh);
            let toIdx = ths.indexOf(th);

            const isRight = th.classList.contains('drop-target-right');
            th.classList.remove('drop-target-left', 'drop-target-right');

            if (isRight) toIdx++;
            if (fromIdx < toIdx) toIdx--;

            // Move <th> in header
            if (toIdx >= ths.length - 1) {
                parentRow.appendChild(this.draggedTh);
            } else {
                parentRow.insertBefore(this.draggedTh, parentRow.children[toIdx]);
            }

            // Move <td> in all body rows
            const tbody = this.table.querySelector('tbody');
            if (tbody) {
                tbody.querySelectorAll('tr').forEach(tr => {
                    const cells = Array.from(tr.children);
                    if (cells.length > fromIdx && cells.length > toIdx) {
                        const cellToMove = cells[fromIdx];
                        if (toIdx >= cells.length - 1) {
                            tr.appendChild(cellToMove);
                        } else {
                            tr.insertBefore(cellToMove, tr.children[toIdx]);
                        }
                    }
                });
            }

            this.saveOrder();
            if (typeof this.options.onReorder === 'function') {
                this.options.onReorder();
            }
        }

        onDragEnd(e, th) {
            this.isDragging = false;
            this.draggedTh = null;
            this.table.querySelectorAll('thead th').forEach(h => {
                h.classList.remove('th-is-dragging', 'drop-target-left', 'drop-target-right');
            });
        }

        saveOrder() {
            const ths = Array.from(this.table.querySelectorAll('thead th'));
            const orderKeys = ths.map(th => th.getAttribute('data-col-key')).filter(Boolean);
            try {
                localStorage.setItem(this.storageKeyOrder, JSON.stringify(orderKeys));
            } catch (e) {}
        }

        saveHidden() {
            try {
                localStorage.setItem(this.storageKeyHidden, JSON.stringify(this.hiddenKeys || []));
            } catch (e) {}
        }

        loadPreferences() {
            try {
                const savedOrder = JSON.parse(localStorage.getItem(this.storageKeyOrder) || 'null');
                if (Array.isArray(savedOrder) && savedOrder.length > 0) {
                    this.orderKeys = savedOrder;
                }
            } catch (e) {
                this.orderKeys = null;
            }

            try {
                const savedHidden = JSON.parse(localStorage.getItem(this.storageKeyHidden) || 'null');
                this.hiddenKeys = Array.isArray(savedHidden) ? savedHidden : [];
            } catch (e) {
                this.hiddenKeys = [];
            }
        }

        /**
         * Re-apply column order and visibility to DOM
         */
        applyPreferences() {
            const thead = this.table.querySelector('thead');
            const tbody = this.table.querySelector('tbody');
            if (!thead) return;

            const headerRow = thead.querySelector('tr');
            if (!headerRow) return;

            // 1. Apply Order if saved
            if (this.orderKeys && this.orderKeys.length > 0) {
                const thMap = {};
                Array.from(headerRow.children).forEach(th => {
                    const k = th.getAttribute('data-col-key');
                    if (k) thMap[k] = th;
                });

                this.orderKeys.forEach(k => {
                    if (thMap[k]) headerRow.appendChild(thMap[k]);
                });

                // Reorder tbody cells to match header
                if (tbody) {
                    const currentThs = Array.from(headerRow.children);
                    tbody.querySelectorAll('tr').forEach(tr => {
                        const cells = Array.from(tr.children);
                        if (cells.length === currentThs.length) {
                            // Find original index mapping
                            const orderedCells = [];
                            currentThs.forEach(th => {
                                const origIdx = parseInt(th.getAttribute('data-orig-idx'), 10);
                                if (!isNaN(origIdx) && cells[origIdx]) {
                                    orderedCells.push(cells[origIdx]);
                                }
                            });
                            if (orderedCells.length === cells.length) {
                                orderedCells.forEach(c => tr.appendChild(c));
                            }
                        }
                    });
                }
            }

            // 2. Apply Visibility
            const currentThs = Array.from(headerRow.children);
            currentThs.forEach((th, idx) => {
                const k = th.getAttribute('data-col-key');
                const isHidden = this.hiddenKeys && this.hiddenKeys.includes(k);

                if (isHidden) {
                    th.classList.add('col-hidden');
                } else {
                    th.classList.remove('col-hidden');
                }

                if (tbody) {
                    tbody.querySelectorAll('tr').forEach(tr => {
                        const td = tr.children[idx];
                        if (td) {
                            if (isHidden) td.classList.add('col-hidden');
                            else td.classList.remove('col-hidden');
                        }
                    });
                }
            });

            this.updateDropdownCheckboxes();
        }

        /**
         * Create Column Visibility Dropdown Menu Button in Toolbar
         */
        createVisibilityDropdown() {
            let container = this.options.toolbarContainer;
            if (!container) {
                // Look for closest card-head-tools or table-card toolbar
                const card = this.table.closest('.metrika-card') || this.table.closest('.table-responsive')?.parentNode;
                if (card) {
                    container = card.querySelector('.card-head-tools > div:last-child') || card.querySelector('.card-head-tools');
                }
            }
            if (!container) return;

            // Remove existing dropdown if any
            const existing = container.querySelector(`.col-vis-dropdown[data-table="${this.tableId}"]`);
            if (existing) existing.remove();

            const dropdownWrapper = document.createElement('div');
            dropdownWrapper.className = 'col-vis-dropdown';
            dropdownWrapper.setAttribute('data-table', this.tableId);
            dropdownWrapper.innerHTML = `
                <button type="button" class="btn btn-secondary btn-sm col-vis-toggle" title="Կարգավորել սյուների տեսանելիությունը">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="3" x2="9" y2="21"/>
                        <line x1="15" y1="3" x2="15" y2="21"/>
                    </svg>
                    <span>${this.options.buttonText}</span>
                    <span class="col-vis-count-badge"></span>
                </button>
                <div class="col-vis-menu">
                    <div class="col-vis-menu-header">
                        <span style="font-weight: 700; font-size: 12px; color: var(--ga-text-title, #1e293b);">Սյուների ցուցադրում</span>
                        <span class="col-vis-menu-stats" style="font-size: 11px; color: #64748b;"></span>
                    </div>
                    <div class="col-vis-search-box">
                        <input type="text" class="col-vis-search-input" placeholder="Փնտրել սյուն..." style="width: 100%; font-size: 11px; padding: 4px 8px; border: 1px solid #e2e8f0; border-radius: 4px;">
                    </div>
                    <div class="col-vis-items-list">
                        <!-- Populated by updateDropdownCheckboxes -->
                    </div>
                    <div class="col-vis-menu-footer">
                        <button type="button" class="btn-text col-vis-show-all">Ցուցադրել բոլորը</button>
                        <button type="button" class="btn-text col-vis-reset">Լռելյայն</button>
                    </div>
                </div>
            `;

            container.appendChild(dropdownWrapper);
            this.dropdownEl = dropdownWrapper;

            const toggleBtn = dropdownWrapper.querySelector('.col-vis-toggle');
            const menu = dropdownWrapper.querySelector('.col-vis-menu');

            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close other open menus
                document.querySelectorAll('.col-vis-menu.active').forEach(m => {
                    if (m !== menu) m.classList.remove('active');
                });
                menu.classList.toggle('active');
            });

            document.addEventListener('click', (e) => {
                if (!dropdownWrapper.contains(e.target)) {
                    menu.classList.remove('active');
                }
            });

            // Filter columns search input
            const searchInput = dropdownWrapper.querySelector('.col-vis-search-input');
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                dropdownWrapper.querySelectorAll('.col-vis-item').forEach(item => {
                    const text = item.innerText.toLowerCase();
                    item.style.display = text.includes(term) ? 'flex' : 'none';
                });
            });

            // Show All
            dropdownWrapper.querySelector('.col-vis-show-all').addEventListener('click', () => {
                this.hiddenKeys = [];
                this.saveHidden();
                this.applyPreferences();
            });

            // Reset Default (order and visibility)
            dropdownWrapper.querySelector('.col-vis-reset').addEventListener('click', () => {
                this.hiddenKeys = [];
                this.orderKeys = null;
                localStorage.removeItem(this.storageKeyOrder);
                localStorage.removeItem(this.storageKeyHidden);
                
                // Re-render table original layout
                const headerRow = this.table.querySelector('thead tr');
                if (headerRow) {
                    const sortedOrig = [...this.columns].sort((a, b) => a.origIdx - b.origIdx);
                    sortedOrig.forEach(c => {
                        if (c.element) headerRow.appendChild(c.element);
                    });
                }
                this.applyPreferences();
            });

            this.updateDropdownCheckboxes();
        }

        updateDropdownCheckboxes() {
            if (!this.dropdownEl) return;
            const listEl = this.dropdownEl.querySelector('.col-vis-items-list');
            if (!listEl) return;

            const thead = this.table.querySelector('thead tr');
            const currentThs = thead ? Array.from(thead.children) : [];

            let visibleCount = 0;
            let totalCount = 0;

            listEl.innerHTML = '';
            currentThs.forEach((th, idx) => {
                const key = th.getAttribute('data-col-key');
                if (!key) return;

                const col = this.columns.find(c => c.key === key) || { title: th.innerText.trim(), hidable: true };
                if (idx === currentThs.length - 1 && col.title === 'Գործողություն') {
                    // Skip action column from hiding
                    return;
                }

                totalCount++;
                const isHidden = this.hiddenKeys && this.hiddenKeys.includes(key);
                if (!isHidden) visibleCount++;

                const item = document.createElement('label');
                item.className = 'col-vis-item';
                item.innerHTML = `
                    <input type="checkbox" data-col-key="${key}" ${isHidden ? '' : 'checked'} ${!col.hidable ? 'disabled' : ''}>
                    <span class="col-vis-title">${col.title}</span>
                `;

                const checkbox = item.querySelector('input');
                checkbox.addEventListener('change', (e) => {
                    const isChecked = e.target.checked;
                    if (isChecked) {
                        this.hiddenKeys = this.hiddenKeys.filter(k => k !== key);
                    } else {
                        if (!this.hiddenKeys.includes(key)) {
                            this.hiddenKeys.push(key);
                        }
                    }
                    this.saveHidden();
                    this.applyPreferences();
                    if (typeof this.options.onVisibilityChange === 'function') {
                        this.options.onVisibilityChange(key, isChecked);
                    }
                });

                listEl.appendChild(item);
            });

            const countBadge = this.dropdownEl.querySelector('.col-vis-count-badge');
            if (countBadge) {
                countBadge.innerText = `${visibleCount}/${totalCount}`;
            }
            const stats = this.dropdownEl.querySelector('.col-vis-menu-stats');
            if (stats) {
                stats.innerText = `${visibleCount} ցուցադրված ${totalCount}-ից`;
            }
        }
    }

    // Global Registry of Table Enhancers
    window.TableEnhancers = window.TableEnhancers || {};

    window.enhanceTable = function(tableId, options) {
        if (!window.TableEnhancers[tableId]) {
            window.TableEnhancers[tableId] = new EnhancedTable(tableId, options);
        } else {
            window.TableEnhancers[tableId].applyPreferences();
        }
        return window.TableEnhancers[tableId];
    };

    window.applyTablePreferences = function(tableId) {
        if (window.TableEnhancers[tableId]) {
            window.TableEnhancers[tableId].applyPreferences();
        }
    };

    // Auto-init tables on DOM load
    document.addEventListener('DOMContentLoaded', () => {
        ['dashProductTable', 'productsTable', 'reservationsTable', 'shipmentsTable', 'usersTable'].forEach(tId => {
            if (document.getElementById(tId)) {
                window.enhanceTable(tId);
            }
        });
    });

})();
