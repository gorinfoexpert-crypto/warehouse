<div class="metrika-card" style="margin-bottom: 14px;">
    <div class="card-head-tools">
        <span class="metrika-card-title" style="font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
            Ապրանքների հասանելիության ստուգում և հաշվիչ (ATP Calculator)
        </span>
        <span class="badge badge-muted">Մնացորդների և մուտքերի հաշվարկ</span>
    </div>
    <div class="metrika-card-body" style="padding: 14px 0 0 0;">
        <div class="form-row" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 2; min-width: 260px;">
                <label class="form-label" for="simProductSelect" style="font-weight: 600; margin-bottom: 4px; display: block;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    Ընտրեք ապրանքը՝
                </label>
                <select id="simProductSelect" class="form-control" style="width: 100%;">
                    <!-- Populated dynamically via API -->
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 180px;">
                <label class="form-label" for="simTargetDate" style="font-weight: 600; margin-bottom: 4px; display: block;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Առաքման նախատեսված օրը՝
                </label>
                <input type="date" id="simTargetDate" class="form-control" value="2026-09-10" style="width: 100%;">
            </div>
            <div class="form-group" style="flex: 1; min-width: 140px;">
                <label class="form-label" for="simQuantity" style="font-weight: 600; margin-bottom: 4px; display: block;">
                    Պահանջվող քանակ՝
                </label>
                <input type="number" id="simQuantity" class="form-control" value="7" min="1" step="1" style="width: 100%;">
            </div>
            <div class="form-group" style="display: flex; align-items: flex-end; min-width: 180px;">
                <button id="runSimBtn" class="btn btn-primary" style="width: 100%; justify-content: center; height: 32px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Ստուգել հասանելիությունը
                </button>
            </div>
        </div>

        <!-- Simulator Result Area -->
        <div id="simResultContainer" style="margin-top: 16px; display: none;">
            <!-- Rendered by app.js -->
        </div>
    </div>
</div>
