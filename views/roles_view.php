<div class="metrika-card" style="margin-bottom: 14px;">
    <div class="card-head-tools" style="flex-wrap: wrap; gap: 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
        <span class="metrika-card-title" style="font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Աշխատակիցներ և Մուտքի իրավունքներ (RBAC Management)
        </span>
        <span class="badge badge-muted">Իրավունքների կառավարում</span>
    </div>
    <div class="metrika-card-body" style="padding: 10px 0 0 0;">
        <p style="color: var(--ga-text-muted); font-size: 11.5px; margin-bottom: 12px;">
            Յուրաքանչյուր պաշտոնի համար կարող եք սահմանել հստակ իրավունքներ (օրինակ՝ վաճառքի մենեջերը տեսնի և ամրագրի ապրանքը, իսկ պահեստապետը կարողանա ընդունել մատակարարումները):
        </p>

        <!-- Section 1: Employees and Assigned Roles -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <h3 style="font-size: 13.5px; font-weight: 700; color: var(--ga-text-title); display: flex; align-items: center; gap: 6px; margin: 0;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Համակարգի աշխատակիցներ
            </h3>
            <button class="btn btn-secondary btn-sm" id="syncUsersBtn" title="Ներբեռնել միայն նոր աշխատակիցներին Bitrix24-ից">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.59-9.21l-3.89 3.89"/></svg>
                Թարմացնել Bitrix24-ից
            </button>
        </div>
        <div class="table-responsive" style="margin-bottom: 16px;">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>Համար</th>
                        <th>Աշխատակից</th>
                        <th>Էլ. փոստ</th>
                        <th>Ընթացիկ պաշտոն</th>
                        <th>Կարգավիճակ</th>
                        <th>Պաշտոնի փոփոխություն</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <!-- Populated dynamically via app.js -->
                </tbody>
            </table>
        </div>

        <!-- Section 2: Permissions Matrix -->
        <h3 style="margin-bottom: 10px; font-size: 13.5px; font-weight: 700; color: var(--ga-text-title); display: flex; align-items: center; gap: 6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Իրավունքների կարգավորում ըստ պաշտոնների
        </h3>
        <div class="table-responsive" style="margin-bottom: 16px;">
            <table class="table" id="permissionsMatrixTable">
                <thead>
                    <tr>
                        <th>Գործառույթ / Հնարավորություն</th>
                        <th style="text-align: center;">Ադմինիստրատոր</th>
                        <th style="text-align: center;">Վաճառքի մենեջեր</th>
                        <th style="text-align: center;">Պահեստապետ</th>
                        <th style="text-align: center;">Դիտորդ</th>
                    </tr>
                </thead>
                <tbody id="permissionsMatrixTableBody">
                    <!-- Populated dynamically via app.js -->
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button class="btn btn-primary btn-sm" id="savePermissionsMatrixBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Պահպանել իրավունքները
            </button>
        </div>
    </div>
</div>
