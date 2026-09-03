<div class="metrika-card" style="margin-bottom: 14px;">
    <div class="card-head-tools" style="flex-wrap: wrap; gap: 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
        <span class="metrika-card-title" style="font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            Սպասվող մատակարարումներ
        </span>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button class="btn btn-secondary btn-sm" id="exportShipmentsCsvBtn" title="Արտահանել Excel / CSV ֆայլ">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                CSV
            </button>
            <button class="btn btn-secondary btn-sm" id="refreshShipmentsBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                Թարմացնել ցանկը
            </button>
            <button class="btn btn-primary btn-sm" id="openAddShipmentModalBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Ավելացնել նոր մատակարարում
            </button>
        </div>
    </div>
    <div class="metrika-card-body" style="padding: 10px 0 0 0;">
        <div style="display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap;">
            <input type="text" id="searchShipmentInput" class="form-control" placeholder="Փնտրել ըստ մատակարարի կամ ապրանքի..." style="max-width: 340px; height: 30px;">
            <select id="filterShipmentStatus" class="form-control" style="max-width: 260px; height: 30px;">
                <option value="">Բոլոր կարգավիճակները</option>
                <option value="CONFIRMED">Հաստատված</option>
                <option value="IN_TRANSIT">Ճանապարհին է</option>
                <option value="PLANNED">Պլանային</option>
                <option value="RECEIVED">Ընդունված է պահեստ</option>
                <option value="CANCELLED">Չեղարկված</option>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table" id="shipmentsTable">
                <thead>
                    <tr>
                        <th>Համար</th>
                        <th>Ապրանք / Արտիկուլ</th>
                        <th>Մատակարար</th>
                        <th>Քանակ</th>
                        <th>Սպասվող օր</th>
                        <th>Պահեստ</th>
                        <th>Կարգավիճակ</th>
                        <th>Մուտքի փաստաթուղթ</th>
                        <th>Գործողություն</th>
                    </tr>
                </thead>
                <tbody id="shipmentsTableBody">
                    <!-- Populated dynamically via app.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Incoming Shipment -->
<div class="modal-backdrop" id="addShipmentModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Նոր մատակարարման գրանցում
            </h3>
            <button class="modal-close" onclick="document.getElementById('addShipmentModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addShipmentForm">
                <div class="form-group">
                    <label class="form-label" for="newShipmentProduct">Ապրանք՝</label>
                    <select id="newShipmentProduct" class="form-control" required></select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="newShipmentQty">Քանակ՝</label>
                        <input type="number" id="newShipmentQty" class="form-control" value="20" min="1" step="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="newShipmentDate">Սպասվող մուտքի օրը՝</label>
                        <input type="date" id="newShipmentDate" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="newShipmentSupplier">Մատակարար ընկերություն՝</label>
                        <input type="text" id="newShipmentSupplier" class="form-control" placeholder="«Էլեկտրոնիկս Դիստրիբյուշն» ՍՊԸ" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="newShipmentStatus">Կարգավիճակ՝</label>
                        <select id="newShipmentStatus" class="form-control">
                            <option value="CONFIRMED" selected>Հաստատված (հաշվի է առնվում հասանելիության մեջ)</option>
                            <option value="IN_TRANSIT">Ճանապարհին է (հաշվի է առնվում հասանելիության մեջ)</option>
                            <option value="PLANNED">Պլանային (նախնական գնում)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="newShipmentNotes">Լրացուցիչ նշում կամ պատվերի համար՝</label>
                    <input type="text" id="newShipmentNotes" class="form-control" placeholder="Պատվեր №1092">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('addShipmentModal').classList.remove('active')">Չեղարկել</button>
            <button class="btn btn-primary btn-sm" id="saveShipmentBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Գրանցել մատակարարումը
            </button>
        </div>
    </div>
</div>
