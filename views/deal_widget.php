<?php
/**
 * Deal Card Embedded View (CRM Deal Tab)
 * Natural Armenian Language (Հայերեն) & Stylish Monochrome SVG Icons
 */
?>
<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ապրանքների հասանելիություն և ամրագրում</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232066b0' stroke-width='2'><path d='M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z'></path></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- JS SDK if inside portal -->
    <script src="//api.bitrix24.com/api/v1/"></script>
</head>
<body class="widget-container">

    <!-- Top Deal Info Bar -->
    <div class="deal-header-bar">
        <div class="deal-title-area">
            <h2 id="dealTitle">Գործարքի տվյալների բեռնում...</h2>
            <p id="dealAssigned">Պահեստի և մատակարարումների ընթացիկ վիճակը</p>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div class="deal-date-control" style="display: flex; align-items: center; gap: 6px;">
                <label for="dealIdInput" style="font-weight: 600;">Գործարք №՝</label>
                <input type="number" id="dealIdInput" value="41983" min="1" style="width: 85px; padding: 4px 8px; border: 1px solid var(--b24-border-color); border-radius: var(--b24-radius-xs); font-weight: 700;">
            </div>
            <div class="deal-date-control">
                <label for="deliveryDateInput">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Առաքման օր՝
                </label>
                <input type="date" id="deliveryDateInput" value="">
            </div>
            <button class="btn btn-primary btn-sm" id="loadDealBtn" title="Ստուգել գործարքը">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Ստուգել
            </button>
            <button class="btn btn-secondary btn-sm" id="refreshDealBtn" title="Թարմացնել տվյալները">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            </button>
        </div>
    </div>

    <!-- Product Evaluation Cards Container -->
    <div id="dealProductsContainer">
        <!-- Injected dynamically by deal_widget.js -->
    </div>

    <!-- Timeline Inspection Modal -->
    <div class="modal-backdrop" id="timelineModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Ապրանքի շարժի պատմություն և ժամանակացույց. <span id="timelineProductTitle" style="color: #2066b0;"></span>
                </h3>
                <button class="modal-close" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body" id="timelineContent">
                <!-- Injected dynamically -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" onclick="document.getElementById('timelineModal').classList.remove('active')">Փակել</button>
            </div>
        </div>
    </div>

    <script src="assets/js/deal_widget.js"></script>
    <script>
        if (typeof BX24 !== 'undefined') {
            BX24.init(function() {
                BX24.resizeWindow(document.body.scrollWidth, document.body.scrollHeight + 80);
            });
        }
    </script>
</body>
</html>
