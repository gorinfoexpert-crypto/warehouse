/**
 * Bitrix24 Deal Tab Widget JS
 * Natural Armenian Language (Հայերեն) & Stylish Black-and-White Vector Icons
 */

// B&W SVG Icons
const ICONS = {
    check: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    alert: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
    x: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
    lock: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
    clock: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
    calendar: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
    loader: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>'
};

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    let currentDealId = urlParams.get('deal_id') || urlParams.get('dealId') || '41983';
    let currentDeliveryDate = urlParams.get('delivery_date') || '';

    const dealTitleEl = document.getElementById('dealTitle');
    const dealAssignedEl = document.getElementById('dealAssigned');
    const dealIdInput = document.getElementById('dealIdInput');
    const deliveryDateInput = document.getElementById('deliveryDateInput');
    const loadDealBtn = document.getElementById('loadDealBtn');
    const productsContainer = document.getElementById('dealProductsContainer');
    const refreshBtn = document.getElementById('refreshDealBtn');
    const timelineModal = document.getElementById('timelineModal');
    const timelineContent = document.getElementById('timelineContent');
    const timelineProductTitle = document.getElementById('timelineProductTitle');
    const closeModalBtn = document.getElementById('closeModalBtn');

    if (dealIdInput) dealIdInput.value = currentDealId;

    // Global state
    let dealProductsData = [];
    let currentDealInfo = null;

    // Load initial deal data
    loadDealData(currentDealId, currentDeliveryDate);

    // Load Deal Button
    if (loadDealBtn) {
        loadDealBtn.addEventListener('click', () => {
            currentDealId = dealIdInput ? dealIdInput.value.trim() : currentDealId;
            currentDeliveryDate = deliveryDateInput ? deliveryDateInput.value : '';
            loadDealData(currentDealId, currentDeliveryDate);
        });
    }

    if (dealIdInput) {
        dealIdInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                currentDealId = dealIdInput.value.trim();
                loadDealData(currentDealId, currentDeliveryDate);
            }
        });
    }

    // Date change listener
    deliveryDateInput.addEventListener('change', (e) => {
        currentDeliveryDate = e.target.value;
        loadDealData(currentDealId, currentDeliveryDate);
    });

    // Refresh button listener
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            loadDealData(currentDealId, currentDeliveryDate);
        });
    }

    // Modal Close
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            timelineModal.classList.remove('active');
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === timelineModal) {
            timelineModal.classList.remove('active');
        }
    });

    /**
     * Fetch deal products & availability evaluation from backend API
     */
    async function loadDealData(id, date) {
        productsContainer.innerHTML = `
            <div style="text-align: center; padding: 40px; color: var(--b24-text-muted);">
                <div style="margin-bottom: 12px; display: inline-block;">${ICONS.loader}</div>
                <div style="font-size: 13px; font-weight: 600;">Ստուգվում է ապրանքների հասանելիությունը...</div>
            </div>
        `;

        try {
            let url = `api/deal_products.php?deal_id=${encodeURIComponent(id)}`;
            if (date) {
                url += `&delivery_date=${encodeURIComponent(date)}`;
            }

            const response = await fetch(url);
            const data = await response.json();

            if (!data.success) {
                showToast(data.error || 'Գործարքի տվյալները չհաջողվեց բեռնել', 'error');
                return;
            }

            dealProductsData = data.products;
            currentDealInfo = data.deal;

            // Update Deal Header
            if (dealTitleEl) dealTitleEl.textContent = data.deal.title;
            if (dealAssignedEl) dealAssignedEl.textContent = `Պատասխանատու մենեջեր՝ ${data.deal.assigned_by}`;
            if (deliveryDateInput && !currentDeliveryDate) {
                deliveryDateInput.value = data.deal.delivery_date;
                currentDeliveryDate = data.deal.delivery_date;
            }

            renderProducts(data);
        } catch (err) {
            productsContainer.innerHTML = `
                <div style="text-align: center; padding: 40px; color: var(--b24-danger);">
                    <div style="margin-bottom: 8px;">${ICONS.alert}</div>
                    <div>Կապի խափանում սերվերի հետ</div>
                </div>
            `;
        }
    }

    /**
     * Render product evaluation cards
     */
    function renderProducts(data) {
        if (!data.products || data.products.length === 0) {
            productsContainer.innerHTML = `
                <div style="text-align: center; padding: 40px; color: var(--b24-text-muted);">
                    Այս գործարքում դեռ կցված ապրանքներ չկան:
                </div>
            `;
            return;
        }

        let html = '';

        data.products.forEach((item) => {
            const atp = item.atp;
            const verdict = atp.verdict;
            const sb = atp.stock_breakdown;
            const req = atp.request;
            const isReserved = item.is_fully_reserved_for_deal;
            const reservedQty = item.reserved_for_deal;
            const unitName = item.measure_name || 'հատ';

            let borderClass = 'border-success';
            let badgeClass = 'badge-success';
            let statusIcon = ICONS.check;

            if (verdict.status === 'PARTIAL') {
                borderClass = 'border-warning';
                badgeClass = 'badge-warning';
                statusIcon = ICONS.alert;
            } else if (verdict.status === 'UNAVAILABLE') {
                borderClass = 'border-danger';
                badgeClass = 'badge-danger';
                statusIcon = ICONS.x;
            }

            html += `
                <div class="atp-product-card ${borderClass}" data-product-id="${item.product_id}">
                    <div class="atp-product-top">
                        <div>
                            <div class="atp-product-name">${escapeHtml(item.product_name)}</div>
                            <div class="atp-product-sku">Արտիկուլ՝ <code>${escapeHtml(atp.product.sku || '-')}</code> | Պահանջվում է՝ <strong>${item.quantity} ${unitName}</strong> մինչև <strong>${req.target_date_formatted}</strong></div>
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <span class="badge ${badgeClass}">${statusIcon} ${verdict.status_text}</span>
                            ${isReserved ? `<span class="badge badge-info">${ICONS.lock} Արդեն ամրագրված է՝ ${reservedQty} ${unitName}</span>` : ''}
                        </div>
                    </div>

                    <div class="stock-pills-grid">
                        <div class="stock-pill">
                            <span class="stock-pill-label">Առկա է պահեստում</span>
                            <span class="stock-pill-val">${sb.physical_stock} <small style="font-size: 11px;">${unitName}</small></span>
                        </div>
                        <div class="stock-pill">
                            <span class="stock-pill-label">Ամրագրված է</span>
                            <span class="stock-pill-val">${sb.physical_reserved} <small style="font-size: 11px;">${unitName}</small></span>
                        </div>
                        <div class="stock-pill highlight">
                            <span class="stock-pill-label">Ազատ է հիմա</span>
                            <span class="stock-pill-val">${sb.free_now} <small style="font-size: 11px;">${unitName}</small></span>
                        </div>
                        <div class="stock-pill">
                            <span class="stock-pill-label">Մուտքեր մինչև ${req.target_date_formatted}</span>
                            <span class="stock-pill-val" style="color: var(--b24-success-dark);">+${sb.incoming_confirmed} <small style="font-size: 11px;">${unitName}</small></span>
                        </div>
                        <div class="stock-pill ${verdict.status === 'AVAILABLE' ? 'success' : (verdict.status === 'PARTIAL' ? 'warning' : '')}">
                            <span class="stock-pill-label">Հասանելի կլինի</span>
                            <span class="stock-pill-val">${sb.atp_available} <small style="font-size: 11px;">${unitName}</small></span>
                        </div>
                    </div>

                    <div class="atp-verdict-box ${verdict.status_class}">
                        ${verdict.message.replace(/\n/g, '<br>')}
                        ${verdict.earliest_full_date_formatted ? `<br><strong>Ամբողջական քանակը հասանելի կլինի՝</strong> ${verdict.earliest_full_date_formatted}` : ''}
                    </div>

                    ${atp.planned_shipments && atp.planned_shipments.length > 0 ? `
                        <div style="background: var(--b24-warning-bg); border: 1px dashed var(--b24-warning-border); border-radius: var(--b24-radius-xs); padding: 8px 12px; margin-bottom: 12px; font-size: 12px; color: var(--b24-warning-dark);">
                            ${ICONS.alert} <strong>Նախատեսված պլանային մուտք՝</strong> ${atp.planned_shipments[0].quantity} ${unitName} (${atp.planned_shipments[0].expected_date_formatted} թ., «${escapeHtml(atp.planned_shipments[0].supplier_name)}»): Դեռևս հաստատված չէ:
                        </div>
                    ` : ''}

                    <div class="atp-actions-row">
                        <div>
                            <button class="btn btn-secondary btn-sm inspect-timeline-btn" data-product-id="${item.product_id}">
                                ${ICONS.clock} Դիտել շարժի պատմությունը
                            </button>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            ${!isReserved ? `
                                <button class="btn btn-primary btn-sm reserve-btn" 
                                    data-product-id="${item.product_id}" 
                                    data-qty="${item.quantity}" 
                                    data-available="${sb.atp_available}"
                                    data-date="${req.target_date}"
                                    ${sb.atp_available < item.quantity && sb.atp_available <= 0 ? 'disabled' : ''}>
                                    ${ICONS.lock} Ամրագրել ${item.quantity} ${unitName}
                                </button>
                            ` : `
                                <button class="btn btn-secondary btn-sm cancel-reserve-btn" data-res-id="${item.existing_reservations[0].id}">
                                    ${ICONS.x} Չեղարկել ամրագրումը (${reservedQty} ${unitName})
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            `;
        });

        productsContainer.innerHTML = html;
        bindProductActions();
    }

    /**
     * Bind button clicks on product cards
     */
    function bindProductActions() {
        // Reserve Button
        document.querySelectorAll('.reserve-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const productId = btn.getAttribute('data-product-id');
                const qty = parseFloat(btn.getAttribute('data-qty'));
                const available = parseFloat(btn.getAttribute('data-available'));
                const date = btn.getAttribute('data-date');

                let targetQty = qty;
                if (available < qty && available > 0) {
                    const confirmPartial = confirm(`Այս պահին հասանելի է միայն ${available} հատ: Ցանկանո՞ւմ եք ամրագրել առկա ${available} հատը:`);
                    if (!confirmPartial) return;
                    targetQty = available;
                }

                btn.disabled = true;
                btn.innerHTML = `${ICONS.loader} Կատարվում է ամրագրում...`;

                try {
                    const managerName = currentDealInfo ? (currentDealInfo.assigned_by || 'Գործարքի մենեջեր') : 'Գործարքի մենեջեր';
                    const customerName = currentDealInfo ? (currentDealInfo.title || 'Հաճախորդ') : 'Հաճախորդ';
                    const res = await fetch('api/reserve.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            deal_id: currentDealId,
                            product_id: productId,
                            quantity: targetQty,
                            delivery_date: date,
                            manager_name: managerName,
                            customer_name: customerName,
                            notes: `Ամրագրում Գործարք #${currentDealId}-ի քարտից`
                        })
                    });

                    const json = await res.json();
                    if (json.success) {
                        showToast(`Հաջողությամբ ամրագրվեց ${targetQty} հատ:`, 'success');
                        loadDealData(currentDealId, currentDeliveryDate);
                    } else {
                        showToast(json.error || 'Ամրագրումը չհաջողվեց', 'error');
                        btn.disabled = false;
                        btn.innerHTML = `${ICONS.lock} Ամրագրել ${qty} հատ`;
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                    btn.disabled = false;
                }
            });
        });

        // Cancel Reservation Button
        document.querySelectorAll('.cancel-reserve-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const resId = btn.getAttribute('data-res-id');
                if (!confirm('Համոզվա՞ծ եք, որ ցանկանում եք չեղարկել այս ապրանքի ամրագրումը:')) return;

                btn.disabled = true;
                try {
                    const res = await fetch(`api/reservations.php?action=cancel&id=${resId}`, {
                        method: 'POST'
                    });
                    const json = await res.json();
                    if (json.success) {
                        showToast('Ամրագրումը չեղարկվեց', 'info');
                        loadDealData(currentDealId, currentDeliveryDate);
                    } else {
                        showToast(json.error || 'Չեղարկումը չհաջողվեց', 'error');
                        btn.disabled = false;
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                    btn.disabled = false;
                }
            });
        });

        // Timeline Inspector
        document.querySelectorAll('.inspect-timeline-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const productId = parseInt(btn.getAttribute('data-product-id'));
                const item = dealProductsData.find(p => p.product_id === productId);
                if (!item) return;

                openTimelineModal(item);
            });
        });
    }

    /**
     * Display Chronological Movement Timeline Modal
     */
    function openTimelineModal(item) {
        timelineProductTitle.textContent = item.product_name;
        const timeline = item.atp.timeline || [];
        const unitName = item.measure_name || 'հատ';

        let timelineHtml = '<div class="timeline-container">';

        timeline.forEach(event => {
            let nodeType = 'initial';
            let changeClass = 'plus';
            let changeSign = '+';

            if (event.type === 'INCOMING') {
                nodeType = 'incoming';
                changeClass = 'plus';
                changeSign = '+';
            } else if (event.type === 'RESERVATION') {
                nodeType = 'reservation';
                changeClass = 'minus';
                changeSign = '';
            }

            timelineHtml += `
                <div class="timeline-node ${nodeType}">
                    <div class="timeline-body">
                        <div class="timeline-header">
                            <span class="timeline-date">${ICONS.calendar} ${event.date}</span>
                            <span class="timeline-change ${changeClass}">${changeSign}${event.change} ${unitName}</span>
                        </div>
                        <div class="timeline-title">${escapeHtml(event.title)}</div>
                        <div class="timeline-desc">${escapeHtml(event.details)}</div>
                        <div class="timeline-balance">Հասանելի մնացորդն այս քայլից հետո՝ <strong>${event.balance_after} ${unitName}</strong></div>
                    </div>
                </div>
            `;
        });

        timelineHtml += '</div>';
        timelineContent.innerHTML = timelineHtml;
        timelineModal.classList.add('active');
    }

    function showToast(message, type = 'info') {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const iconSvg = type === 'success' ? ICONS.check : (type === 'error' ? ICONS.x : ICONS.alert);
        toast.innerHTML = `<span style="display: flex; align-items: center;">${iconSvg}</span><span>${escapeHtml(message)}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3500);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
