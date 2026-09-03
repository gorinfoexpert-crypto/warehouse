<div class="metrika-card" style="margin-bottom: 14px;">
    <div class="card-head-tools" style="flex-wrap: wrap; gap: 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
        <span class="metrika-card-title" style="font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            Պահեստի մնացորդներ և ծանուցման շեմեր
        </span>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button class="btn btn-secondary btn-sm" id="exportProductsCsvBtn" title="Արտահանել Excel / CSV ֆայլ">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                CSV
            </button>
            <button class="btn btn-secondary btn-sm" id="refreshProductsBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                Թարմացնել ցանկը
            </button>
            <button class="btn btn-primary btn-sm" id="syncBitrixBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                Թարմացնել մնացորդները
            </button>
        </div>
    </div>
    <div class="metrika-card-body" style="padding: 10px 0 0 0;">
        <p style="color: var(--ga-text-muted); font-size: 11.5px; margin-bottom: 10px;">
            Փաստացի մնացորդները համաժամեցվում են պահեստից: Ապրանքների սպասվող մուտքերն ու ապագա ամրագրումները հաշվարկվում են ավտոմատ:
        </p>

        <div class="table-responsive">
            <table class="table" id="productsTable">
                <thead>
                    <tr>
                        <th>Ապրանքի համար</th>
                        <th>Ապրանքի անվանում</th>
                        <th>Արտիկուլ / Կոդ</th>
                        <th>Առկա է պահեստում</th>
                        <th>Ամրագրված</th>
                        <th>Ազատ է հիմա</th>
                        <th>Սպասվող մուտքեր</th>
                        <th>Min շեմ (ծանուցում)</th>
                        <th>Մատակարարում (օր)</th>
                        <th>Գին</th>
                        <th style="text-align: center;">Գործողություն</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <!-- Populated dynamically via app.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Edit Product Min Threshold & Delivery Days -->
<div class="modal-backdrop" id="editProductModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="editProductModalTitle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                Ապրանքի ծանուցման շեմ և մատակարարման ժամկետ
            </h3>
            <button class="modal-close" onclick="document.getElementById('editProductModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editProductForm">
                <input type="hidden" id="editProductBitrixId">
                <div class="form-group">
                    <label class="form-label" for="editProductName">Ապրանքի անվանում՝</label>
                    <input type="text" id="editProductName" class="form-control" readonly style="background: #f8fafc; font-weight: 600;">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="editProductMinStock">
                            Նվազագույն քանակ (ծանուցման համար)՝
                        </label>
                        <input type="number" id="editProductMinStock" class="form-control" min="0" step="1" required>
                        <small style="color: var(--ga-text-muted); font-size: 10.5px;">Եթե ազատ մնացորդն իջնի այս շեմից, համակարգը կտա ծանուցում:</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editProductDeliveryDays">
                            Մատակարարման տևողությունը (օրերով)՝
                        </label>
                        <input type="number" id="editProductDeliveryDays" class="form-control" min="1" step="1" required>
                        <small style="color: var(--ga-text-muted); font-size: 10.5px;">Քանի օր է տևում ապրանքի մատակարարումը պատվիրելու պահից:</small>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editProductMaxStock">Առավելագույն ցանկալի մնացորդ (Max)՝</label>
                    <input type="number" id="editProductMaxStock" class="form-control" min="0" step="1">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('editProductModal').classList.remove('active')">Չեղարկել</button>
            <button class="btn btn-primary btn-sm" id="saveProductThresholdsBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Պահպանել
            </button>
        </div>
    </div>
</div>
