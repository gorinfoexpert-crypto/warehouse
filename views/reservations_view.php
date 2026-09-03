<div class="metrika-card" style="margin-bottom: 14px;">
    <div class="card-head-tools" style="flex-wrap: wrap; gap: 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
        <span class="metrika-card-title" style="font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Գործարքների ամրագրումներ
        </span>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button class="btn btn-secondary btn-sm" id="exportReservationsCsvBtn" title="Արտահանել Excel / CSV ֆայլ">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                CSV
            </button>
            <button class="btn btn-secondary btn-sm" id="refreshReservationsBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                Թարմացնել ցանկը
            </button>
            <button class="btn btn-primary btn-sm" id="openAddReservationModalBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Ամրագրել ապրանք
            </button>
        </div>
    </div>
    <div class="metrika-card-body" style="padding: 10px 0 0 0;">
        <div style="display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap;">
            <input type="text" id="searchReservationInput" class="form-control" placeholder="Փնտրել ըստ գործարքի, հաճախորդի, ապրանքի..." style="max-width: 340px; height: 30px;">
            <select id="filterReservationStatus" class="form-control" style="max-width: 260px; height: 30px;">
                <option value="">Բոլոր կարգավիճակները</option>
                <option value="RESERVED">Ամրագրված է</option>
                <option value="CONFIRMED">Հաստատված է</option>
                <option value="SHIPPED">Առաքված է</option>
                <option value="CANCELLED">Չեղարկված է</option>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table" id="reservationsTable">
                <thead>
                    <tr>
                        <th>Համար</th>
                        <th>Գործարք</th>
                        <th>Ապրանք / Արտիկուլ</th>
                        <th>Քանակ</th>
                        <th>Առաքման օր</th>
                        <th>Հաճախորդ / Մենեջեր</th>
                        <th>Կարգավիճակ</th>
                        <th>Ստեղծման ամսաթիվ</th>
                        <th>Գործողություն</th>
                    </tr>
                </thead>
                <tbody id="reservationsTableBody">
                    <!-- Populated dynamically via app.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Manual Reservation -->
<div class="modal-backdrop" id="addReservationModal">
    <div class="modal-dialog" style="max-width: 580px;">
        <div class="modal-header">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Նոր ամրագրման ստեղծում գործարքի համար
            </h3>
            <button class="modal-close" onclick="document.getElementById('addReservationModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addReservationForm">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="newResDealId">Գործարքի համար (Bitrix24 Deal ID)՝</label>
                        <div style="display: flex; gap: 6px;">
                            <input type="number" id="newResDealId" class="form-control" value="41983" placeholder="41983" required style="flex: 1;">
                            <button type="button" class="btn btn-secondary btn-sm" id="fetchDealInfoBtn" title="Բեռնել գործարքի տվյալները Bitrix24-ից" style="white-space: nowrap;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                Գտնել Bitrix-ում
                            </button>
                        </div>
                        <small id="dealFetchStatus" style="color: var(--b24-text-muted); font-size: 11px; margin-top: 3px; display: block;"></small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="newResDate">Առաքման նախատեսված օրը՝</label>
                        <input type="date" id="newResDate" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="newResProduct">Ապրանք՝</label>
                    <select id="newResProduct" class="form-control" required></select>
                </div>

                <div class="form-row">
                    <div class="form-group" style="max-width: 140px;">
                        <label class="form-label" for="newResQty">Ամրագրվող քանակ՝</label>
                        <input type="number" id="newResQty" class="form-control" value="5" min="1" step="1" required>
                    </div>
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label class="form-label" for="newResManager" style="margin-bottom: 0;">Մենեջեր՝</label>
                            <div style="display: flex; gap: 4px;">
                                <button type="button" class="btn btn-secondary btn-xs" id="refreshManagersBtn" title="Թարմացնել մենեջերներին Bitrix24-ից">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                </button>
                                <button type="button" class="btn btn-secondary btn-xs" id="openAddManagerModalBtn" title="Ավելացնել նոր մենեջեր">
                                    + Ավելացնել
                                </button>
                            </div>
                        </div>
                        <select id="newResManager" class="form-control" required>
                            <!-- Populated dynamically via crm_entities.php -->
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label class="form-label" for="newResCustomer" style="margin-bottom: 0;">Հաճախորդ կամ ընկերության անվանում՝</label>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn btn-secondary btn-xs" id="refreshCustomersBtn" title="Թարմացնել Bitrix24 CRM-ից">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            </button>
                            <button type="button" class="btn btn-primary btn-xs" id="openAddCrmCustomerModalBtn" title="Ավելացնել նոր հաճախորդ / ընկերություն Bitrix24 CRM-ում" style="background: #2066b0; color: #fff;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                + Ավելացնել Bitrix24-ում
                            </button>
                        </div>
                    </div>
                    <select id="newResCustomer" class="form-control" required>
                        <!-- Populated dynamically via crm_entities.php -->
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="newResNotes">Նշում ամրագրման համար՝</label>
                    <input type="text" id="newResNotes" class="form-control" placeholder="Ամրագրում ըստ պայմանագրի">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('addReservationModal').classList.remove('active')">Չեղարկել</button>
            <button class="btn btn-primary btn-sm" id="saveReservationBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Հաստատել ամրագրումը
            </button>
        </div>
    </div>
</div>

<!-- Sub-Modal: Add Customer / Company directly in Bitrix24 CRM -->
<div class="modal-backdrop" id="addCrmCustomerModal" style="z-index: 10050;">
    <div class="modal-dialog" style="max-width: 460px;">
        <div class="modal-header">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2066b0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Ավելացնել Bitrix24 CRM-ում
            </h3>
            <button class="modal-close" onclick="document.getElementById('addCrmCustomerModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Հաճախորդի տեսակը՝</label>
                <div style="display: flex; gap: 12px; margin-top: 4px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                        <input type="radio" name="crmCustomerType" value="company" checked id="typeCompanyRadio"> 🏢 Ընկերություն (Company)
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                        <input type="radio" name="crmCustomerType" value="contact" id="typeContactRadio"> 👤 Ֆիզիկական անձ / Կոնտակտ
                    </label>
                </div>
            </div>

            <!-- Fields for Company -->
            <div id="companyFieldsGroup">
                <div class="form-group">
                    <label class="form-label" for="newCrmCompanyTitle">Ընկերության անվանում՝</label>
                    <input type="text" id="newCrmCompanyTitle" class="form-control" placeholder="Օրինակ՝ «Սոֆթկոնստրակտ» ՓԲԸ">
                </div>
            </div>

            <!-- Fields for Contact -->
            <div id="contactFieldsGroup" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="newCrmContactName">Անուն՝</label>
                        <input type="text" id="newCrmContactName" class="form-control" placeholder="Արմեն">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="newCrmContactLastName">Ազգանուն՝</label>
                        <input type="text" id="newCrmContactLastName" class="form-control" placeholder="Սարգսյան">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="newCrmPhone">Հեռախոսահամար՝</label>
                    <input type="text" id="newCrmPhone" class="form-control" placeholder="+374 91 000000">
                </div>
                <div class="form-group">
                    <label class="form-label" for="newCrmEmail">Էլ. փոստ՝</label>
                    <input type="email" id="newCrmEmail" class="form-control" placeholder="info@company.am">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('addCrmCustomerModal').classList.remove('active')">Չեղարկել</button>
            <button class="btn btn-primary btn-sm" id="saveCrmCustomerBtn" style="background: #2066b0;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Ստեղծել Bitrix24-ում և ընտրել
            </button>
        </div>
    </div>
</div>

<!-- Sub-Modal: Add Manager -->
<div class="modal-backdrop" id="addCrmManagerModal" style="z-index: 10050;">
    <div class="modal-dialog" style="max-width: 440px;">
        <div class="modal-header">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Ավելացնել նոր մենեջեր
            </h3>
            <button class="modal-close" onclick="document.getElementById('addCrmManagerModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label" for="newManagerName">Անուն Ազգանուն՝</label>
                <input type="text" id="newManagerName" class="form-control" placeholder="Օրինակ՝ Արտակ Գևորգյան" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="newManagerEmail">Էլ. փոստ (Email)՝</label>
                <input type="email" id="newManagerEmail" class="form-control" placeholder="artak@company.am">
            </div>
            <div class="form-group">
                <label class="form-label" for="newManagerRole">Պաշտոն / Դեր համակարգում՝</label>
                <select id="newManagerRole" class="form-control">
                    <option value="manager" selected>Վաճառքի մենեջեր</option>
                    <option value="admin">Ադմինիստրատոր / Տնօրեն</option>
                    <option value="storekeeper">Պահեստապետ / Լոգիստ</option>
                    <option value="viewer">Դիտորդ / Աուդիտոր</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('addCrmManagerModal').classList.remove('active')">Չեղարկել</button>
            <button class="btn btn-primary btn-sm" id="saveCrmManagerBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Ավելացնել և ընտրել
            </button>
        </div>
    </div>
</div>
