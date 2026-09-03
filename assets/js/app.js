/**
 * Main Warehouse Stock & Availability Management Dashboard JS
 * Pure Natural Armenian Language (Հայերեն) & Stylish Black-and-White Vector Icons
 * Role-Based Access Control (RBAC) UI Enforcement
 */

// B&W SVG Vector Icons Library
const ICONS = {
    check: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    x: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
    alert: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
    box: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
    truck: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
    lock: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
    zap: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
    refresh: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>',
    calendar: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
    download: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>',
    send: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>',
    search: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
    user: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
    clock: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
    loader: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>'
};

document.addEventListener('DOMContentLoaded', () => {
    // Navigation Tabs
    const navTabs = document.querySelectorAll('.nav-tab');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Global State
    let cachedProducts = [];
    let cachedShipments = [];
    let cachedReservations = [];
    let currentUser = null;
    let rolesData = [];
    let usersData = [];
    let allPermissions = {};

    // Initialize
    initAuthAndApp();

    async function initAuthAndApp() {
        await loadAuthData();
        initTabs();
        loadDashboard();
        loadKpis();
        loadProducts();
        loadShipments();
        loadReservations();
        initSimulator();
        initModals();
        initRolesView();
        initSettingsForm();
        initUserSwitcher();
        initAppInteractions();
    }

    function initAppInteractions() {
        // Quick Add Button
        document.getElementById('quickAddBtn')?.addEventListener('click', () => {
            const tabBtn = document.querySelector('.nav-tab[data-tab="tab-reservations"]');
            if (tabBtn) tabBtn.click();
            const addResModal = document.getElementById('addReservationModal');
            if (addResModal) {
                addResModal.classList.add('active');
                showToast('Ընտրեք ապրանքը նոր ամրագրում ավելացնելու համար', 'info');
            }
        });

        // Header Refresh Button
        document.getElementById('headerRefreshBtn')?.addEventListener('click', async () => {
            showToast('Տվյալները թարմացվում են...', 'info');
            await Promise.all([loadDashboard(), loadKpis(), loadProducts(), loadShipments(), loadReservations()]);
            showToast('Տվյալները հաջողությամբ թարմացվեցին:', 'success');
        });

        // Header Settings Button
        document.getElementById('headerSettingsBtn')?.addEventListener('click', () => {
            const tabBtn = document.querySelector('.nav-tab[data-tab="tab-settings"]');
            if (tabBtn) tabBtn.click();
        });

        // Header Filter Button
        document.getElementById('headerFilterBtn')?.addEventListener('click', () => {
            const searchInput = document.getElementById('dashTableSearch');
            if (searchInput) {
                searchInput.focus();
                showToast('Մուտքագրեք որոնման բառը', 'info');
            }
        });

        // Date Range Pill
        document.getElementById('dateRangePill')?.addEventListener('click', () => {
            showToast('Ընտրված է ընթացիկ ցուցանիշների ամիսը (01.09.2026 — 30.09.2026)', 'info');
        });

        // Sidebar Actions (by Explicit IDs)
        // 0. Brand Logo Home
        document.getElementById('brandLogoBtn')?.addEventListener('click', () => {
            document.querySelector('.nav-tab[data-tab="tab-dashboard"]')?.click();
        });

        // 1. Search
        document.getElementById('sidebarSearchBtn')?.addEventListener('click', () => {
            document.getElementById('dashTableSearch')?.focus();
            showToast('Մուտքագրեք որոնման բառը', 'info');
        });

        // 2. Theme Toggle
        document.getElementById('sidebarThemeToggle')?.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            showToast(isDark ? 'Ակտիվացվեց մուգ ռեժիմը' : 'Ակտիվացվեց լուսավոր ռեժիմը', 'success');
        });

        // 3. Plus Add Sidebar
        document.getElementById('sidebarAddBtn')?.addEventListener('click', () => {
            document.getElementById('quickAddBtn')?.click();
        });

        // 4. Stars / Favorites
        document.getElementById('sidebarFavoritesBtn')?.addEventListener('click', () => {
            const statusSelect = document.getElementById('dashFilterStatus');
            if (statusSelect) {
                statusSelect.value = statusSelect.value === 'shortage' ? 'ALL' : 'shortage';
                statusSelect.dispatchEvent(new Event('change'));
                showToast('Ցուցադրված են ռիսկային ապրանքները', 'info');
            }
        });

        // 5. Grid Dashboard Tab
        document.getElementById('sidebarGridBtn')?.addEventListener('click', () => {
            document.querySelector('.nav-tab[data-tab="tab-dashboard"]')?.click();
        });

        // 6. Menu / Nav Toggle
        document.getElementById('sidebarMenuBtn')?.addEventListener('click', () => {
            const nav = document.querySelector('.nav-tabs-wrapper');
            if (nav) {
                nav.style.display = (nav.style.display === 'none') ? 'flex' : 'none';
            }
        });

        // 7. Profile Modal
        document.getElementById('sidebarAvatarBtn')?.addEventListener('click', () => {
            const u = currentUser || { name: 'Աշխատակից', role_name: 'Մենեջեր' };
            alert(`👤 Օգտատիրոջ Պրոֆիլ:\n\nԱնուն՝ ${u.name}\nՊաշտոն՝ ${u.role_name}\nՀամակարգ՝ Պահեստային Dash 2026\nԿարգավիճակ՝ Ակտիվ միացում`);
        });

        // 8. Help Guide Modal
        document.getElementById('sidebarHelpBtn')?.addEventListener('click', () => {
            alert(`ℹ️ Պահեստային Համակարգի Ուղեցույց:\n\n1. «Վահանակ» — Իրական ժամանակում ցույց է տալիս ապրանքների ընդհանուր, ազատ և ամրագրված մնացորդները:\n2. «Ստուգել հասանելիությունը» — ATP հաշվարկ ապագա ցանկացած ամսաթվի համար:\n3. «Մատակարարումներ» — Մատակարարներից սպասվող ապրանքների գրանցում և ընդունում պահեստ:\n4. «Ամրագրումներ» — Bitrix24 գործարքներով ապրանքների պահում:\n5. «Պահեստի մնացորդներ» — Ապրանքների նվազագույն ծանուցման շեմերի և մատակարարման օրերի կարգավորում:`);
        });
    }

    /**
     * Load Current User & Roles Data
     */
    async function loadAuthData() {
        try {
            const res = await fetch('api/roles.php?action=list_all');
            const data = await res.json();
            if (data.success) {
                currentUser = data.current_user;
                rolesData = data.roles;
                usersData = data.users;
                allPermissions = data.all_permissions;
                applyUIPermissions();
                updateUserSwitcherOptions();
            }
        } catch (e) {
            console.error('Auth load error', e);
        }
    }

    function updateUserSwitcherOptions() {
        const select = document.getElementById('currentUserSelect');
        if (!select) return;

        let html = '';
        usersData.forEach(u => {
            html += `<option value="${u.id}" ${u.id == currentUser.id ? 'selected' : ''}>
                ${escapeHtml(u.name)} (${escapeHtml(u.role_name)})
            </option>`;
        });
        select.innerHTML = html;
    }

    /**
     * Helper to check permission
     */
    function can(perm) {
        if (!currentUser || !currentUser.permissions_array) return true;
        return currentUser.permissions_array.includes(perm);
    }

    /**
     * Apply UI restrictions based on current role permissions
     */
    function applyUIPermissions() {
        // Tab visibility
        navTabs.forEach(tab => {
            const perm = tab.getAttribute('data-permission');
            if (perm && !can(perm)) {
                tab.style.display = 'none';
                if (tab.classList.contains('active')) {
                    tab.classList.remove('active');
                    const firstVisible = Array.from(navTabs).find(t => t.style.display !== 'none');
                    if (firstVisible) {
                        firstVisible.click();
                    }
                }
            } else {
                tab.style.display = 'flex';
            }
        });

        // Action Buttons
        const syncBtn = document.getElementById('syncBitrixBtn');
        if (syncBtn) syncBtn.style.display = can('sync_bitrix') ? 'inline-flex' : 'none';

        const addShipmentBtn = document.getElementById('openAddShipmentModalBtn');
        if (addShipmentBtn) addShipmentBtn.style.display = can('manage_shipments') ? 'inline-flex' : 'none';

        const addResBtn = document.getElementById('openAddReservationModalBtn');
        if (addResBtn) addResBtn.style.display = can('create_reservations') ? 'inline-flex' : 'none';
    }

    /**
     * User Switcher (Top Bar)
     */
    function initUserSwitcher() {
        const select = document.getElementById('currentUserSelect');
        if (!select) return;

        select.addEventListener('change', async (e) => {
            const userId = e.target.value;
            try {
                const res = await fetch('api/roles.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'switch_user', user_id: userId })
                });
                const data = await res.json();
                if (data.success) {
                    currentUser = data.current_user;
                    showToast(`Աշխատակիցը փոխվեց՝ ${currentUser.name} (${currentUser.role_name})`, 'info');
                    applyUIPermissions();
                    loadDashboard();
                    loadShipments();
                    loadReservations();
                    loadProducts();
                }
            } catch (err) {
                showToast('Աշխատակցի փոփոխման սխալ', 'error');
            }
        });
    }

    /**
     * Auto Refresh (Every 60 seconds)
     */
    function initAutoRefresh() {
        setInterval(() => {
            const activeTab = document.querySelector('.nav-tab.active');
            if (!activeTab) return;
            const targetTab = activeTab.getAttribute('data-tab');
            
            if (targetTab === 'tab-dashboard') { loadDashboard(); loadKpis(); }
            else if (targetTab === 'tab-shipments') { loadShipments(); loadKpis(); }
            else if (targetTab === 'tab-reservations') { loadReservations(); loadKpis(); }
            else if (targetTab === 'tab-products') { loadProducts(); loadKpis(); }
            else if (targetTab === 'tab-roles') renderRolesView();
        }, 60000);
    }

    /**
     * Tab Navigation
     */
    function initTabs() {
        navTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.getAttribute('data-tab');

                navTabs.forEach(t => t.classList.remove('active'));
                tabPanes.forEach(p => p.style.display = 'none');

                tab.classList.add('active');
                const activePane = document.getElementById(targetTab);
                if (activePane) {
                    activePane.style.display = 'block';
                }

                // Refresh active tab data
                if (targetTab === 'tab-dashboard') loadDashboard();
                if (targetTab === 'tab-shipments') loadShipments();
                if (targetTab === 'tab-reservations') loadReservations();
                if (targetTab === 'tab-products') loadProducts();
                if (targetTab === 'tab-roles') renderRolesView();
                if (targetTab === 'tab-settings') initSettingsForm();
            });
        });
    }

    /**
     * Render Roles & Permissions Management View
     */
    function initRolesView() {
        const syncUsersBtn = document.getElementById('syncUsersBtn');
        if (syncUsersBtn) {
            syncUsersBtn.addEventListener('click', async () => {
                if (!can('manage_roles')) {
                    showToast('Դուք չունեք աշխատակիցներին թարմացնելու հասանելիություն', 'error');
                    return;
                }
                const orig = syncUsersBtn.innerHTML;
                syncUsersBtn.innerHTML = `<svg class="spin-icon" style="animation: spin 1s linear infinite; margin-right: 4px;" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg> Թարմացվում է...`;
                syncUsersBtn.disabled = true;
                try {
                    const res = await fetch('api/bitrix_sync.php?action=users');
                    const json = await res.json();
                    if (json.success) {
                        showToast(json.message, 'success');
                        await loadAuthData();
                        renderRolesView();
                    } else {
                        showToast(json.error || 'Թարմացման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                }
                syncUsersBtn.innerHTML = orig;
                syncUsersBtn.disabled = false;
            });
        }

        const saveMatrixBtn = document.getElementById('savePermissionsMatrixBtn');
        if (saveMatrixBtn) {
            saveMatrixBtn.addEventListener('click', async () => {
                if (!can('manage_roles')) {
                    showToast('Դուք չունեք իրավունքները խմբագրելու հասանելիություն', 'error');
                    return;
                }

                saveMatrixBtn.disabled = true;
                saveMatrixBtn.innerHTML = `${ICONS.loader} Պահպանվում է...`;

                try {
                    const updates = [];
                    rolesData.forEach(role => {
                        const checkedPerms = [];
                        document.querySelectorAll(`.perm-checkbox[data-role="${role.code}"]:checked`).forEach(cb => {
                            checkedPerms.push(cb.getAttribute('data-perm'));
                        });
                        updates.push(fetch('api/roles.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'update_permissions',
                                role_code: role.code,
                                permissions: checkedPerms
                            })
                        }));
                    });

                    await Promise.all(updates);
                    showToast('Իրավունքների փոփոխությունները պահպանվեցին:', 'success');
                    await loadAuthData();
                    renderRolesView();
                } catch (e) {
                    showToast('Պահպանման սխալ', 'error');
                } finally {
                    saveMatrixBtn.disabled = false;
                    saveMatrixBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg> Պահպանել իրավունքները`;
                }
            });
        }
    }

    function renderRolesView() {
        const usersTbody = document.getElementById('usersTableBody');
        const matrixTbody = document.getElementById('permissionsMatrixTableBody');

        // 1. Render Users Table
        if (usersTbody) {
            let html = '';
            usersData.forEach(u => {
                const roleOptions = rolesData.map(r => `
                    <option value="${r.code}" ${r.code === u.role_code ? 'selected' : ''}>${escapeHtml(r.name)}</option>
                `).join('');

                html += `
                    <tr>
                        <td><strong>#${u.id}</strong></td>
                        <td><strong>${escapeHtml(u.name)}</strong></td>
                        <td><code>${escapeHtml(u.email || '-')}</code></td>
                        <td><span class="badge badge-info">${escapeHtml(u.role_name)}</span></td>
                        <td><span class="badge badge-success">${ICONS.check} Ակտիվ</span></td>
                        <td>
                            <select class="form-control user-role-select" data-user-id="${u.id}" style="padding: 4px 8px; font-size: 12px;" ${!can('manage_roles') ? 'disabled' : ''}>
                                ${roleOptions}
                            </select>
                        </td>
                    </tr>
                `;
            });
            usersTbody.innerHTML = html;
            if (window.applyTablePreferences) window.applyTablePreferences('usersTable');

            // Bind user role change
            document.querySelectorAll('.user-role-select').forEach(sel => {
                sel.addEventListener('change', async (e) => {
                    const userId = sel.getAttribute('data-user-id');
                    const newRole = sel.value;

                    try {
                        const res = await fetch('api/roles.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'update_user_role', user_id: userId, role_code: newRole })
                        });
                        const data = await res.json();
                        if (data.success) {
                            showToast('Աշխատակցի պաշտոնը հաջողությամբ փոխվեց', 'success');
                            await loadAuthData();
                        } else {
                            showToast(data.error || 'Սխալ', 'error');
                        }
                    } catch (err) {
                        showToast('Հարցման սխալ', 'error');
                    }
                });
            });
        }

        // 2. Render Permissions Matrix Table
        if (matrixTbody) {
            let matrixHtml = '';
            const permKeys = Object.keys(allPermissions);

            permKeys.forEach(permKey => {
                const permName = allPermissions[permKey];
                let cells = `<td><strong>${escapeHtml(permName)}</strong><br><small style="color: var(--b24-text-muted); font-family: var(--b24-font-mono);">${permKey}</small></td>`;

                rolesData.forEach(role => {
                    const hasPerm = (role.permissions_array || []).includes(permKey);
                    const disabledAttr = !can('manage_roles') || role.code === 'admin' ? 'disabled' : '';
                    cells += `
                        <td style="text-align: center;">
                            <input type="checkbox" class="perm-checkbox" data-role="${role.code}" data-perm="${permKey}" ${hasPerm ? 'checked' : ''} ${disabledAttr} style="transform: scale(1.2); cursor: pointer;">
                        </td>
                    `;
                });

                matrixHtml += `<tr>${cells}</tr>`;
            });
            matrixTbody.innerHTML = matrixHtml;
        }
    }

    /**
     * Load Top KPI Counters
     */
    async function loadKpis() {
        try {
            const [pRes, sRes, rRes] = await Promise.all([
                fetch('api/products.php').then(r => r.json()),
                fetch('api/shipments.php').then(r => r.json()),
                fetch('api/reservations.php').then(r => r.json()),
            ]);

            if (pRes.success) {
                let totalFreeStock = 0;
                let totalStock = 0;
                let totalReserved = 0;
                pRes.products.forEach(p => {
                    totalFreeStock += parseFloat(p.free_stock || 0);
                    totalStock += parseFloat(p.current_stock || 0);
                    totalReserved += parseFloat(p.reserved_stock || 0);
                });

                if (document.getElementById('kpiFreeStock')) {
                    document.getElementById('kpiFreeStock').textContent = totalFreeStock.toLocaleString('hy-AM') + ' հատ';
                }
                if (document.getElementById('kpiTopVal1')) {
                    document.getElementById('kpiTopVal1').textContent = totalStock.toLocaleString('ru-RU');
                }
                if (document.getElementById('kpiTopVal2')) {
                    document.getElementById('kpiTopVal2').textContent = totalFreeStock.toLocaleString('ru-RU');
                }
                if (document.getElementById('kpiTopVal3')) {
                    document.getElementById('kpiTopVal3').textContent = totalReserved.toLocaleString('ru-RU');
                }
            }

            if (sRes.success) {
                const activeShipments = (sRes.shipments || []).filter(s => ['CONFIRMED', 'IN_TRANSIT'].includes(s.status));
                let totalIncomingQty = 0;
                activeShipments.forEach(s => totalIncomingQty += parseFloat(s.quantity || 0));
                if (document.getElementById('kpiIncomingShipments')) {
                    document.getElementById('kpiIncomingShipments').textContent = `${activeShipments.length} (${totalIncomingQty} հատ)`;
                }
            }

            if (rRes.success) {
                const activeRes = (rRes.reservations || []).filter(r => ['RESERVED', 'CONFIRMED'].includes(r.status));
                let totalResQty = 0;
                activeRes.forEach(r => totalResQty += parseFloat(r.quantity || 0));
                if (document.getElementById('kpiActiveReservations')) {
                    document.getElementById('kpiActiveReservations').textContent = `${activeRes.length} (${totalResQty} հատ)`;
                }
            }
        } catch (e) {
            console.error('KPI error', e);
        }
    }

    // Products Catalog State with Pagination
    const productsTableState = {
        page: 1,
        pageSize: '50',
        filterStock: 'ALL',
        searchTerm: '',
        sortCol: null,
        sortDir: 'asc',
        initialized: false
    };

    /**
     * Render Products Catalog Table with Pagination and Filters
     */
    function renderProductsTable() {
        const tbody = document.getElementById('productsTableBody');
        const badgeTotal = document.getElementById('productsTotalBadge');
        const paginationInfo = document.getElementById('productsPaginationInfo');
        const paginationControls = document.getElementById('productsPaginationControls');
        if (!tbody) return;

        // 1. Filter products
        let list = [...(cachedProducts || [])];
        const term = (productsTableState.searchTerm || '').toLowerCase();
        const filter = productsTableState.filterStock;

        if (term) {
            list = list.filter(p => 
                (p.name && p.name.toLowerCase().includes(term)) ||
                (p.sku && p.sku.toLowerCase().includes(term)) ||
                String(p.bitrix_product_id).includes(term)
            );
        }

        if (filter === 'IN_STOCK') {
            list = list.filter(p => parseFloat(p.current_stock || 0) > 0);
        } else if (filter === 'RESERVED') {
            list = list.filter(p => parseFloat(p.reserved_stock || 0) > 0);
        } else if (filter === 'LOW_STOCK') {
            list = list.filter(p => parseFloat(p.free_stock || 0) <= parseFloat(p.min_stock || 0));
        } else if (filter === 'ZERO_STOCK') {
            list = list.filter(p => parseFloat(p.free_stock || 0) <= 0);
        }

        // 2. Sort products if sort column is active
        if (productsTableState.sortCol) {
            const col = productsTableState.sortCol;
            const isDesc = productsTableState.sortDir === 'desc';
            list.sort((a, b) => {
                let valA = a[col];
                let valB = b[col];

                if (col === 'id') { valA = parseInt(a.bitrix_product_id, 10); valB = parseInt(b.bitrix_product_id, 10); }
                else if (col === 'name') { return isDesc ? (b.name || '').localeCompare(a.name || '') : (a.name || '').localeCompare(b.name || ''); }
                else if (col === 'sku') { return isDesc ? (b.sku || '').localeCompare(a.sku || '') : (a.sku || '').localeCompare(b.sku || ''); }
                else if (col === 'current_stock') { valA = parseFloat(a.current_stock || 0); valB = parseFloat(b.current_stock || 0); }
                else if (col === 'reserved_stock') { valA = parseFloat(a.reserved_stock || 0); valB = parseFloat(b.reserved_stock || 0); }
                else if (col === 'free_stock') { valA = parseFloat(a.free_stock || 0); valB = parseFloat(b.free_stock || 0); }
                else if (col === 'incoming') { valA = parseFloat(a.total_incoming_confirmed || 0); valB = parseFloat(b.total_incoming_confirmed || 0); }
                else if (col === 'min_stock') { valA = parseFloat(a.min_stock || 0); valB = parseFloat(b.min_stock || 0); }
                else if (col === 'delivery_days') { valA = parseInt(a.delivery_days || 0, 10); valB = parseInt(b.delivery_days || 0, 10); }
                else if (col === 'price') { valA = parseFloat(a.price || 0); valB = parseFloat(b.price || 0); }

                if (valA < valB) return isDesc ? 1 : -1;
                if (valA > valB) return isDesc ? -1 : 1;
                return 0;
            });
        }

        const totalCount = list.length;
        if (badgeTotal) {
            badgeTotal.textContent = `${totalCount.toLocaleString('hy-AM')} ապրանք`;
        }

        // 3. Pagination calculation
        const isAll = productsTableState.pageSize === 'ALL';
        const size = isAll ? Math.max(1, totalCount) : parseInt(productsTableState.pageSize, 10);
        const totalPages = Math.max(1, Math.ceil(totalCount / size));

        if (productsTableState.page > totalPages) productsTableState.page = totalPages;
        if (productsTableState.page < 1) productsTableState.page = 1;

        const startIdx = (productsTableState.page - 1) * size;
        const endIdx = isAll ? totalCount : Math.min(startIdx + size, totalCount);
        const pageItems = isAll ? list : list.slice(startIdx, endIdx);

        // 4. Render HTML rows
        if (pageItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align: center; color: var(--ga-text-muted); padding: 30px;">Ապրանքներ չեն գտնվել տրված ֆիլտրերով</td></tr>`;
        } else {
            let html = '';
            pageItems.forEach(p => {
                const freeStock = parseFloat(p.free_stock);
                const freeClass = freeStock > 0 ? 'color: var(--b24-success-dark); font-weight: 700;' : 'color: var(--b24-danger); font-weight: 700;';
                const unit = p.unit || 'հատ';
                const minStock = parseFloat(p.min_stock || 0);
                const delDays = parseInt(p.delivery_days || 7, 10);
                const isAlert = freeStock <= minStock;

                html += `
                    <tr>
                        <td><span class="badge badge-muted">#${p.bitrix_product_id}</span></td>
                        <td><strong>${escapeHtml(p.name)}</strong></td>
                        <td><code>${escapeHtml(p.sku || '-')}</code></td>
                        <td class="text-right">${p.current_stock} ${unit}</td>
                        <td class="text-right" style="color: var(--b24-warning-dark);">${p.reserved_stock} ${unit}</td>
                        <td class="text-right" style="${freeClass}">${p.free_stock} ${unit}</td>
                        <td class="text-right" style="color: var(--b24-info);">+${p.total_incoming_confirmed} ${unit}</td>
                        <td class="text-right">
                            <span class="${isAlert ? 'badge badge-warning' : ''}" style="${isAlert ? 'font-weight:700;' : ''}">
                                ${minStock} ${unit}
                            </span>
                        </td>
                        <td class="text-right"><strong>${delDays} օր</strong></td>
                        <td class="text-right"><strong>${parseFloat(p.price).toLocaleString('hy-AM')} ֏</strong></td>
                        <td style="text-align: center;">
                            <button class="btn btn-secondary btn-sm edit-threshold-btn" data-id="${p.bitrix_product_id}" data-name="${escapeHtml(p.name)}" data-min="${minStock}" data-max="${p.max_stock || 0}" data-days="${delDays}" title="Կարգավորել նվազագույն շեմն ու մատակարարման օրերը">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                Փոխել
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
            if (window.applyTablePreferences) window.applyTablePreferences('productsTable');

            // Bind threshold edit modal trigger
            tbody.querySelectorAll('.edit-threshold-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    const name = btn.getAttribute('data-name');
                    const min = btn.getAttribute('data-min');
                    const max = btn.getAttribute('data-max');
                    const days = btn.getAttribute('data-days');

                    document.getElementById('editProductBitrixId').value = id;
                    document.getElementById('editProductName').value = name;
                    document.getElementById('editProductMinStock').value = min;
                    document.getElementById('editProductMaxStock').value = max;
                    document.getElementById('editProductDeliveryDays').value = days;

                    document.getElementById('editProductModal').classList.add('active');
                });
            });
        }

        // 5. Update Pagination Info
        if (paginationInfo) {
            if (totalCount === 0) {
                paginationInfo.textContent = 'Արդյունքներ չկան';
            } else {
                paginationInfo.textContent = `Ցուցադրված է ${startIdx + 1} - ${endIdx} (ընդհանուր՝ ${totalCount.toLocaleString('hy-AM')} ապրանք)`;
            }
        }

        // 6. Render Pagination Controls
        if (paginationControls) {
            if (isAll || totalPages <= 1) {
                paginationControls.innerHTML = '';
                return;
            }

            let btnHtml = '';
            // Prev button
            btnHtml += `<button class="btn btn-secondary btn-sm" id="btnProdPagePrev" ${productsTableState.page <= 1 ? 'disabled' : ''} style="padding: 2px 8px; height: 24px;">«</button>`;

            // Page numbers sliding window
            const maxButtons = 5;
            let startPage = Math.max(1, productsTableState.page - 2);
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);
            if (endPage - startPage < maxButtons - 1) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            if (startPage > 1) {
                btnHtml += `<button class="btn btn-secondary btn-sm prod-page-num-btn" data-p="1" style="padding: 2px 8px; height: 24px;">1</button>`;
                if (startPage > 2) btnHtml += `<span style="color: #999; padding: 0 2px;">...</span>`;
            }

            for (let pNum = startPage; pNum <= endPage; pNum++) {
                const activeStyle = pNum === productsTableState.page ? 'background-color: var(--ga-blue); color: #fff; border-color: var(--ga-blue); font-weight: 700;' : '';
                btnHtml += `<button class="btn btn-secondary btn-sm prod-page-num-btn" data-p="${pNum}" style="padding: 2px 8px; height: 24px; ${activeStyle}">${pNum}</button>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) btnHtml += `<span style="color: #999; padding: 0 2px;">...</span>`;
                btnHtml += `<button class="btn btn-secondary btn-sm prod-page-num-btn" data-p="${totalPages}" style="padding: 2px 8px; height: 24px;">${totalPages}</button>`;
            }

            // Next button
            btnHtml += `<button class="btn btn-secondary btn-sm" id="btnProdPageNext" ${productsTableState.page >= totalPages ? 'disabled' : ''} style="padding: 2px 8px; height: 24px;">»</button>`;

            paginationControls.innerHTML = btnHtml;

            // Bind Pagination Button Clicks
            const btnPrev = document.getElementById('btnProdPagePrev');
            if (btnPrev) {
                btnPrev.addEventListener('click', () => {
                    if (productsTableState.page > 1) {
                        productsTableState.page--;
                        renderProductsTable();
                    }
                });
            }

            const btnNext = document.getElementById('btnProdPageNext');
            if (btnNext) {
                btnNext.addEventListener('click', () => {
                    if (productsTableState.page < totalPages) {
                        productsTableState.page++;
                        renderProductsTable();
                    }
                });
            }

            paginationControls.querySelectorAll('.prod-page-num-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetPage = parseInt(btn.getAttribute('data-p'), 10);
                    if (targetPage && targetPage !== productsTableState.page) {
                        productsTableState.page = targetPage;
                        renderProductsTable();
                    }
                });
            });
        }
    }

    /**
     * Initialize Event Listeners for Products Table Controls
     */
    function initProductsTableControls() {
        if (productsTableState.initialized) return;
        productsTableState.initialized = true;

        const pageSizeSelect = document.getElementById('productsPageSize');
        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', (e) => {
                productsTableState.pageSize = e.target.value;
                productsTableState.page = 1;
                renderProductsTable();
            });
        }

        const filterStockSelect = document.getElementById('productsFilterStock');
        if (filterStockSelect) {
            filterStockSelect.addEventListener('change', (e) => {
                productsTableState.filterStock = e.target.value;
                productsTableState.page = 1;
                renderProductsTable();
            });
        }

        const searchInput = document.getElementById('productsSearchInput');
        if (searchInput) {
            let searchTimeout = null;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    productsTableState.searchTerm = e.target.value.trim().toLowerCase();
                    productsTableState.page = 1;
                    renderProductsTable();
                }, 200);
            });
        }

        // Enhance with universal sorting / drag & drop / column visibility
        if (window.enhanceTable) {
            window.enhanceTable('productsTable', {
                onSort: (colKey, direction) => {
                    productsTableState.sortCol = direction === 'neutral' ? null : colKey;
                    productsTableState.sortDir = direction;
                    renderProductsTable();
                }
            });
        }
    }

    /**
     * Load Products Catalog
     */
    async function loadProducts() {
        const selectSim = document.getElementById('simProductSelect');
        const selectShipment = document.getElementById('newShipmentProduct');
        const selectRes = document.getElementById('newResProduct');

        try {
            const res = await fetch('api/products.php');
            const data = await res.json();

            if (!data.success) return;
            cachedProducts = data.products;

            initProductsTableControls();
            renderProductsTable();

            // Populate Dropdowns
            const populateSelect = (selectEl) => {
                if (!selectEl) return;
                const currentVal = selectEl.value;
                selectEl.innerHTML = cachedProducts.map(p => `
                    <option value="${p.bitrix_product_id}">[Համար՝ ${p.bitrix_product_id}] ${escapeHtml(p.name)} (Առկա է՝ ${p.current_stock} ${p.unit || 'հատ'})</option>
                `).join('');
                if (currentVal) selectEl.value = currentVal;
            };

            populateSelect(selectSim);
            populateSelect(selectShipment);
            populateSelect(selectRes);

        } catch (e) {
            console.error('Products load error', e);
        }
    }

    /**
     * Stock Sync Button
     */
    const syncBitrixBtn = document.getElementById('syncBitrixBtn');
    if (syncBitrixBtn) {
        syncBitrixBtn.addEventListener('click', async () => {
            if (!can('sync_bitrix')) {
                showToast('Մուտքն արգելված է: Չունեք մնացորդները թարմացնելու իրավունք:', 'error');
                return;
            }

            syncBitrixBtn.disabled = true;
            syncBitrixBtn.innerHTML = `${ICONS.loader} Թարմացվում է...`;

            try {
                const res = await fetch('api/bitrix_sync.php', { method: 'POST' });
                const json = await res.json();
                if (json.success) {
                    showToast(json.message, 'success');
                    loadDashboard();
                    loadProducts();
                    loadKpis();
                } else {
                    showToast(json.error || 'Թարմացման սխալ', 'error');
                }
            } catch (e) {
                showToast('Կապի խափանում համակարգի հետ', 'error');
            } finally {
                syncBitrixBtn.disabled = false;
                syncBitrixBtn.innerHTML = `${ICONS.refresh} Թարմացնել մնացորդները`;
            }
        });
    }

    /**
     * Simulator Logic
     */
    function initSimulator() {
        const runBtn = document.getElementById('runSimBtn');
        const productSelect = document.getElementById('simProductSelect');
        const dateInput = document.getElementById('simTargetDate');
        const qtyInput = document.getElementById('simQuantity');
        const resultContainer = document.getElementById('simResultContainer');

        if (!runBtn) return;

        runBtn.addEventListener('click', async () => {
            const productId = productSelect.value;
            const date = dateInput.value;
            const qty = parseFloat(qtyInput.value) || 1;

            if (!productId || !date) {
                showToast('Ընտրեք ապրանքը և առաքման օրը', 'error');
                return;
            }

            runBtn.disabled = true;
            runBtn.innerHTML = `${ICONS.loader} Ստուգվում է...`;

            try {
                const res = await fetch(`api/availability.php?product_id=${productId}&date=${date}&quantity=${qty}`);
                const data = await res.json();

                if (!data.success) {
                    showToast(data.error || 'Հաշվարկի սխալ', 'error');
                    return;
                }

                renderSimulatorResult(data, resultContainer);
            } catch (e) {
                showToast('Հաշվարկի սխալ', 'error');
            } finally {
                runBtn.disabled = false;
                runBtn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> Ստուգել հասանելիությունը`;
            }
        });
    }

    function renderSimulatorResult(data, container) {
        const p = data.product;
        const req = data.request;
        const sb = data.stock_breakdown;
        const verdict = data.verdict;
        const timeline = data.timeline || [];
        const unit = p.unit || 'հատ';

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

        let timelineRowsHtml = '';
        timeline.forEach(event => {
            const changeClass = event.change >= 0 ? 'color: var(--b24-success-dark); font-weight: 700;' : 'color: var(--b24-danger); font-weight: 700;';
            const changeSign = event.change > 0 ? '+' : '';
            timelineRowsHtml += `
                <tr>
                    <td><code>${ICONS.calendar} ${event.date}</code></td>
                    <td><strong>${escapeHtml(event.title)}</strong><br><small style="color: var(--b24-text-muted);">${escapeHtml(event.details)}</small></td>
                    <td style="${changeClass}">${changeSign}${event.change} ${unit}</td>
                    <td style="color: #2066b0; font-weight: 700;">${event.balance_after} ${unit}</td>
                </tr>
            `;
        });

        container.style.display = 'block';
        container.innerHTML = `
            <div class="atp-product-card ${borderClass}">
                <div class="atp-product-top">
                    <div>
                        <div class="atp-product-name">${escapeHtml(p.name)}</div>
                        <div class="atp-product-sku">Արտիկուլ՝ <code>${escapeHtml(p.sku || '-')}</code> | Պահանջվում է՝ <strong>${req.requested_quantity} ${unit}</strong> մինչև <strong>${req.target_date_formatted}</strong></div>
                    </div>
                    <div>
                        <span class="badge ${badgeClass}" style="font-size: 13px; padding: 4px 12px;">${statusIcon} ${verdict.status_text}</span>
                    </div>
                </div>

                <div class="stock-pills-grid">
                    <div class="stock-pill">
                        <span class="stock-pill-label">Առկա է պահեստում</span>
                        <span class="stock-pill-val">${sb.physical_stock} <small style="font-size: 11px;">${unit}</small></span>
                    </div>
                    <div class="stock-pill">
                        <span class="stock-pill-label">Ամրագրված է</span>
                        <span class="stock-pill-val">${sb.physical_reserved} <small style="font-size: 11px;">${unit}</small></span>
                    </div>
                    <div class="stock-pill highlight">
                        <span class="stock-pill-label">Ազատ է հիմա</span>
                        <span class="stock-pill-val">${sb.free_now} <small style="font-size: 11px;">${unit}</small></span>
                    </div>
                    <div class="stock-pill">
                        <span class="stock-pill-label">Մուտքեր մինչև ${req.target_date_formatted}</span>
                        <span class="stock-pill-val" style="color: var(--b24-success-dark);">+${sb.incoming_confirmed} <small style="font-size: 11px;">${unit}</small></span>
                    </div>
                    <div class="stock-pill ${verdict.status === 'AVAILABLE' ? 'success' : (verdict.status === 'PARTIAL' ? 'warning' : '')}">
                        <span class="stock-pill-label">Հասանելի կլինի</span>
                        <span class="stock-pill-val">${sb.atp_available} <small style="font-size: 11px;">${unit}</small></span>
                    </div>
                </div>

                <div class="atp-verdict-box ${verdict.status_class}">
                    ${verdict.message.replace(/\n/g, '<br>')}
                    ${verdict.earliest_full_date_formatted ? `<br><strong>Ամբողջական քանակը հասանելի կլինի՝</strong> ${verdict.earliest_full_date_formatted}` : ''}
                </div>

                <h4 style="margin: 18px 0 10px 0; font-size: 14px; font-weight: 700;">${ICONS.clock} Ապրանքի շարժի քայլ առ քայլ ժամանակացույց.</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ամսաթիվ</th>
                                <th>Իրադարձություն</th>
                                <th>Փոփոխություն (+/-)</th>
                                <th>Մնացորդ քայլից հետո</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${timelineRowsHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    /**
     * Load Incoming Shipments Table
     */
    async function loadShipments() {
        const tbody = document.getElementById('shipmentsTableBody');
        const filterStatus = document.getElementById('filterShipmentStatus')?.value || '';
        const searchVal = document.getElementById('searchShipmentInput')?.value.toLowerCase() || '';

        if (!tbody) return;

        try {
            let url = 'api/shipments.php';
            if (filterStatus) url += `?status=${encodeURIComponent(filterStatus)}`;

            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; color: var(--b24-danger); padding: 30px;">${data.error || 'Մուտքն արգելված է'}</td></tr>`;
                return;
            }

            let filtered = data.shipments || [];
            if (searchVal) {
                filtered = filtered.filter(s => 
                    (s.supplier_name && s.supplier_name.toLowerCase().includes(searchVal)) ||
                    (s.product_name && s.product_name.toLowerCase().includes(searchVal)) ||
                    (s.product_sku && s.product_sku.toLowerCase().includes(searchVal))
                );
            }
            cachedShipments = filtered;

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; color: var(--b24-text-muted); padding: 30px;">Մատակարարումներ չեն գտնվել</td></tr>`;
                return;
            }

            let html = '';
            filtered.forEach(s => {
                let statusBadge = '';
                if (s.status === 'CONFIRMED') statusBadge = `<span class="badge badge-success">${ICONS.check} Հաստատված</span>`;
                else if (s.status === 'IN_TRANSIT') statusBadge = `<span class="badge badge-info">${ICONS.truck} Ճանապարհին է</span>`;
                else if (s.status === 'PLANNED') statusBadge = `<span class="badge badge-warning">${ICONS.alert} Պլանային</span>`;
                else if (s.status === 'RECEIVED') statusBadge = `<span class="badge badge-muted">${ICONS.check} Ընդունված է</span>`;
                else if (s.status === 'CANCELLED') statusBadge = `<span class="badge badge-danger">${ICONS.x} Չեղարկված</span>`;

                const canReceive = ['CONFIRMED', 'IN_TRANSIT', 'PLANNED'].includes(s.status) && can('receive_shipments');
                const canCancel = ['CONFIRMED', 'IN_TRANSIT', 'PLANNED'].includes(s.status) && can('manage_shipments');
                const unit = s.product_unit || 'հատ';

                html += `
                    <tr>
                        <td><strong>#${s.id}</strong></td>
                        <td>
                            <strong>${escapeHtml(s.product_name || 'Ապրանք #' + s.bitrix_product_id)}</strong>
                            <br><small style="color: var(--b24-text-muted); font-family: var(--b24-font-mono);">${escapeHtml(s.product_sku || '')}</small>
                        </td>
                        <td>${escapeHtml(s.supplier_name)}</td>
                        <td><strong style="color: var(--b24-success-dark);">+${s.quantity} ${unit}</strong></td>
                        <td><code>${ICONS.calendar} ${s.expected_date}</code></td>
                        <td>${escapeHtml(s.warehouse_title || 'Պահեստ 1')}</td>
                        <td>${statusBadge}</td>
                        <td>
                            ${s.bitrix_doc_id ? `<span class="badge badge-info">Փաստաթուղթ #${s.bitrix_doc_id}</span>` : '-'}
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                ${canReceive ? `
                                    <button class="btn btn-success btn-sm receive-shipment-btn" data-id="${s.id}" title="Ընդունել պահեստ և ավտոմատ կազմել մուտքի փաստաթուղթ">
                                        ${ICONS.download} Ընդունել պահեստ
                                    </button>
                                ` : ''}
                                ${canCancel ? `
                                    <button class="btn btn-secondary btn-sm cancel-shipment-btn" data-id="${s.id}" title="Չեղարկել">
                                        ${ICONS.x}
                                    </button>
                                ` : ''}
                                ${!canReceive && !canCancel ? '-' : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            if (window.applyTablePreferences) window.applyTablePreferences('shipmentsTable');
            bindShipmentActions();

        } catch (e) {
            console.error('Shipments error', e);
        }
    }

    function bindShipmentActions() {
        document.querySelectorAll('.receive-shipment-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!confirm(`Ընդունե՞լ մատակարարում #${id}-ը պահեստ: Ավտոմատ կստեղծվի և կանցկացվի մուտքի փաստաթուղթ:`)) return;

                btn.disabled = true;
                btn.innerHTML = `${ICONS.loader} Մուտքագրում...`;

                try {
                    const res = await fetch(`api/shipments.php?action=receive&id=${id}`, { method: 'POST' });
                    const json = await res.json();
                    if (json.success) {
                        showToast(json.message, 'success');
                        loadDashboard();
                        loadShipments();
                        loadProducts();
                        loadKpis();
                    } else {
                        showToast(json.error || 'Ընդունման սխալ', 'error');
                        btn.disabled = false;
                        btn.innerHTML = `${ICONS.download} Ընդունել պահեստ`;
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                    btn.disabled = false;
                }
            });
        });

        document.querySelectorAll('.cancel-shipment-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!confirm(`Չեղարկե՞լ մատակարարում #${id}-ը:`)) return;

                try {
                    const res = await fetch(`api/shipments.php?action=cancel&id=${id}`, { method: 'POST' });
                    const json = await res.json();
                    if (json.success) {
                        showToast('Մատակարարումը չեղարկվեց', 'info');
                        loadDashboard();
                        loadShipments();
                        loadKpis();
                    } else {
                        showToast(json.error || 'Չեղարկման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                }
            });
        });
    }

    /**
     * Load Product Reservations Table
     */
    async function loadReservations() {
        const tbody = document.getElementById('reservationsTableBody');
        const filterStatus = document.getElementById('filterReservationStatus')?.value || '';
        const searchVal = document.getElementById('searchReservationInput')?.value.toLowerCase() || '';

        if (!tbody) return;

        try {
            let url = 'api/reservations.php';
            if (filterStatus) url += `?status=${encodeURIComponent(filterStatus)}`;

            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; color: var(--b24-danger); padding: 30px;">${data.error || 'Մուտքն արգելված է'}</td></tr>`;
                return;
            }

            let filtered = data.reservations || [];
            if (searchVal) {
                filtered = filtered.filter(r => 
                    String(r.deal_id).includes(searchVal) ||
                    (r.customer_name && r.customer_name.toLowerCase().includes(searchVal)) ||
                    (r.manager_name && r.manager_name.toLowerCase().includes(searchVal)) ||
                    (r.product_name && r.product_name.toLowerCase().includes(searchVal))
                );
            }
            cachedReservations = filtered;

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; color: var(--b24-text-muted); padding: 30px;">Ամրագրումներ չեն գտնվել</td></tr>`;
                return;
            }

            let html = '';
            filtered.forEach(r => {
                let statusBadge = '';
                if (r.status === 'RESERVED') statusBadge = `<span class="badge badge-warning">${ICONS.lock} Ամրագրված է</span>`;
                else if (r.status === 'CONFIRMED') statusBadge = `<span class="badge badge-success">${ICONS.check} Հաստատված է</span>`;
                else if (r.status === 'SHIPPED') statusBadge = `<span class="badge badge-muted">${ICONS.box} Առաքված է</span>`;
                else if (r.status === 'CANCELLED') statusBadge = `<span class="badge badge-danger">${ICONS.x} Չեղարկված է</span>`;

                const canConfirm = ['RESERVED'].includes(r.status) && (can('confirm_reservations') || can('manage_reservations'));
                const canShip = ['RESERVED', 'CONFIRMED'].includes(r.status) && (can('ship_reservations') || can('manage_reservations'));
                const canCancel = ['RESERVED', 'CONFIRMED'].includes(r.status) && can('manage_reservations');
                const unit = r.product_unit || 'հատ';

                html += `
                    <tr>
                        <td><strong>#${r.id}</strong></td>
                        <td>
                            <a href="deal_card.php?deal_id=${r.deal_id}" target="_blank" style="color: #2066b0; text-decoration: none; font-weight: 700;">
                                Գործարք #${r.deal_id}
                            </a>
                        </td>
                        <td>
                            <strong>${escapeHtml(r.product_name || 'Ապրանք #' + r.bitrix_product_id)}</strong>
                            <br><small style="color: var(--b24-text-muted); font-family: var(--b24-font-mono);">${escapeHtml(r.product_sku || '')}</small>
                        </td>
                        <td><strong style="color: var(--b24-warning-dark);">${r.quantity} ${unit}</strong></td>
                        <td><code>${ICONS.calendar} ${r.delivery_date}</code></td>
                        <td>
                            <strong>${escapeHtml(r.customer_name || 'Հաճախորդ')}</strong>
                            <br><small style="color: var(--b24-text-muted);">${escapeHtml(r.manager_name || 'Մենեջեր')}</small>
                        </td>
                        <td>${statusBadge}</td>
                        <td><small style="color: var(--b24-text-muted);">${r.created_at.split(' ')[0]}</small></td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                ${canConfirm ? `<button class="btn btn-secondary btn-sm confirm-res-btn" data-id="${r.id}" title="Հաստատել ամրագրումը">${ICONS.check} Հաստատել</button>` : ''}
                                ${canShip ? `<button class="btn btn-secondary btn-sm ship-res-btn" data-id="${r.id}" title="Առաքել հաճախորդին">${ICONS.send} Առաքել</button>` : ''}
                                ${canCancel ? `<button class="btn btn-secondary btn-sm cancel-res-btn" data-id="${r.id}" title="Չեղարկել ամրագրումը">${ICONS.x}</button>` : ''}
                                ${!canConfirm && !canShip && !canCancel ? '-' : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            if (window.applyTablePreferences) window.applyTablePreferences('reservationsTable');
            bindReservationActions();

        } catch (e) {
            console.error('Reservations error', e);
        }
    }

    function bindReservationActions() {
        document.querySelectorAll('.cancel-res-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!confirm(`Չեղարկե՞լ ամրագրում #${id}-ը:`)) return;

                try {
                    const res = await fetch(`api/reservations.php?action=cancel&id=${id}`, { method: 'POST' });
                    const json = await res.json();
                    if (json.success) {
                        showToast('Ամրագրումը չեղարկվեց', 'info');
                        loadDashboard();
                        loadReservations();
                        loadProducts();
                        loadKpis();
                    } else {
                        showToast(json.error || 'Չեղարկման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                }
            });
        });

        document.querySelectorAll('.confirm-res-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                try {
                    const res = await fetch(`api/reservations.php?action=confirm&id=${id}`, { method: 'POST' });
                    const json = await res.json();
                    if (json.success) {
                        showToast('Ամրագրումը հաստատվեց', 'success');
                        loadDashboard();
                        loadReservations();
                    } else {
                        showToast(json.error || 'Հաստատման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                }
            });
        });

        document.querySelectorAll('.ship-res-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!confirm(`Առաքե՞լ ապրանքը հաճախորդին ամրագրում #${id}-ով:`)) return;
                try {
                    const res = await fetch(`api/reservations.php?action=ship&id=${id}`, { method: 'POST' });
                    const json = await res.json();
                    if (json.success) {
                        showToast('Ապրանքը նշվեց որպես առաքված', 'success');
                        loadDashboard();
                        loadReservations();
                    } else {
                        showToast(json.error || 'Առաքման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                }
            });
        });
    }

    /**
     * Modal Handlers (Add Shipment / Add Reservation)
     */
    function initModals() {
        const addShipmentModal = document.getElementById('addShipmentModal');
        const openAddShipmentBtn = document.getElementById('openAddShipmentModalBtn');
        const saveShipmentBtn = document.getElementById('saveShipmentBtn');

        const addReservationModal = document.getElementById('addReservationModal');
        const openAddReservationBtn = document.getElementById('openAddReservationModalBtn');
        const saveReservationBtn = document.getElementById('saveReservationBtn');

        // Set default dates to +7 days
        const defaultDate = new Date();
        defaultDate.setDate(defaultDate.getDate() + 7);
        const defaultDateStr = defaultDate.toISOString().split('T')[0];

        const shipmentDateEl = document.getElementById('newShipmentDate');
        if (shipmentDateEl) shipmentDateEl.value = defaultDateStr;

        const resDateEl = document.getElementById('newResDate');
        if (resDateEl) resDateEl.value = defaultDateStr;

        // Open Shipment Modal
        if (openAddShipmentBtn) {
            openAddShipmentBtn.addEventListener('click', () => {
                addShipmentModal.classList.add('active');
            });
        }

        // Save Shipment
        if (saveShipmentBtn) {
            saveShipmentBtn.addEventListener('click', async () => {
                const productId = document.getElementById('newShipmentProduct').value;
                const qty = document.getElementById('newShipmentQty').value;
                const date = document.getElementById('newShipmentDate').value;
                const supplier = document.getElementById('newShipmentSupplier').value;
                const status = document.getElementById('newShipmentStatus').value;
                const notes = document.getElementById('newShipmentNotes').value;

                if (!productId || !qty || !date || !supplier) {
                    showToast('Լրացրեք բոլոր պարտադիր դաշտերը', 'error');
                    return;
                }

                saveShipmentBtn.disabled = true;
                try {
                    const res = await fetch('api/shipments.php?action=create', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: qty,
                            expected_date: date,
                            supplier_name: supplier,
                            status: status,
                            notes: notes
                        })
                    });
                    const json = await res.json();
                    if (json.success) {
                        showToast('Մատակարարումը հաջողությամբ գրանցվեց:', 'success');
                        addShipmentModal.classList.remove('active');
                        loadDashboard();
                        loadShipments();
                        loadProducts();
                        loadKpis();
                    } else {
                        showToast(json.error || 'Գրանցման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                } finally {
                    saveShipmentBtn.disabled = false;
                }
            });
        }

        // ==========================================
        // CRM Entities (Managers & Customers) Handler
        // ==========================================
        const selectManager = document.getElementById('newResManager');
        const selectCustomer = document.getElementById('newResCustomer');
        const refreshManagersBtn = document.getElementById('refreshManagersBtn');
        const refreshCustomersBtn = document.getElementById('refreshCustomersBtn');
        const openAddCrmCustomerBtn = document.getElementById('openAddCrmCustomerModalBtn');
        const addCrmCustomerModal = document.getElementById('addCrmCustomerModal');
        const saveCrmCustomerBtn = document.getElementById('saveCrmCustomerBtn');
        const openAddManagerBtn = document.getElementById('openAddManagerModalBtn');
        const addCrmManagerModal = document.getElementById('addCrmManagerModal');
        const saveCrmManagerBtn = document.getElementById('saveCrmManagerBtn');
        const dealIdInput = document.getElementById('newResDealId');
        const fetchDealInfoBtn = document.getElementById('fetchDealInfoBtn');
        const dealFetchStatus = document.getElementById('dealFetchStatus');

        let cachedManagersList = [];
        let cachedCompaniesList = [];
        let cachedContactsList = [];
        let cachedPastList = [];

        async function loadCrmEntities(forceRefresh = false, selectCustomerName = null, selectManagerName = null) {
            try {
                if (refreshManagersBtn && forceRefresh) refreshManagersBtn.innerHTML = ICONS.loader;
                if (refreshCustomersBtn && forceRefresh) refreshCustomersBtn.innerHTML = ICONS.loader;

                const res = await fetch(`api/crm_entities.php?refresh=${forceRefresh ? 1 : 0}`);
                const data = await res.json();

                if (data.success) {
                    cachedManagersList = data.managers || [];
                    cachedCompaniesList = data.companies || [];
                    cachedContactsList = data.contacts || [];
                    cachedPastList = data.past_customers || [];

                    renderManagerSelect(selectManagerName);
                    renderCustomerSelect(selectCustomerName);

                    if (forceRefresh) {
                        showToast('Bitrix24 CRM տվյալները թարմացվեցին', 'success');
                    }
                }
            } catch (e) {
                console.error('Failed to load CRM entities', e);
            } finally {
                if (refreshManagersBtn) refreshManagersBtn.innerHTML = ICONS.refresh;
                if (refreshCustomersBtn) refreshCustomersBtn.innerHTML = ICONS.refresh;
            }
        }

        function renderManagerSelect(selectedName = null) {
            if (!selectManager) return;
            const currentVal = selectedName || selectManager.value || (currentUser ? currentUser.name : '');
            
            let html = '<option value="">-- Ընտրեք մենեջերին --</option>';
            cachedManagersList.forEach(m => {
                const isSel = (currentVal && (m.name === currentVal || m.name.toLowerCase() === currentVal.toLowerCase())) ? 'selected' : '';
                const roleDesc = m.role_name ? ` (${m.role_name})` : '';
                html += `<option value="${escapeHtml(m.name)}" ${isSel}>${escapeHtml(m.name)}${escapeHtml(roleDesc)}</option>`;
            });

            // If selected name not in list, add as custom option
            if (currentVal && !cachedManagersList.some(m => m.name === currentVal)) {
                html += `<option value="${escapeHtml(currentVal)}" selected>${escapeHtml(currentVal)} (Ընտրված)</option>`;
            }

            selectManager.innerHTML = html;
        }

        function renderCustomerSelect(selectedName = null) {
            if (!selectCustomer) return;
            const currentVal = selectedName || selectCustomer.value;
            let html = '<option value="">-- Ընտրեք հաճախորդին / ընկերությանը --</option>';

            if (cachedCompaniesList.length > 0) {
                html += '<optgroup label="🏢 Bitrix24 CRM Ընկերություններ">';
                cachedCompaniesList.forEach(c => {
                    const isSel = (currentVal && (c.name === currentVal || c.name.toLowerCase() === currentVal.toLowerCase())) ? 'selected' : '';
                    html += `<option value="${escapeHtml(c.name)}" ${isSel}>${escapeHtml(c.name)}</option>`;
                });
                html += '</optgroup>';
            }

            if (cachedContactsList.length > 0) {
                html += '<optgroup label="👤 Bitrix24 CRM Կոնտակտներ">';
                cachedContactsList.forEach(ct => {
                    const isSel = (currentVal && (ct.name === currentVal || ct.name.toLowerCase() === currentVal.toLowerCase())) ? 'selected' : '';
                    html += `<option value="${escapeHtml(ct.name)}" ${isSel}>${escapeHtml(ct.name)}</option>`;
                });
                html += '</optgroup>';
            }

            if (cachedPastList.length > 0) {
                html += '<optgroup label="📋 Այլ / Նախորդ ամրագրումներ">';
                cachedPastList.forEach(p => {
                    const isSel = (currentVal && (p.name === currentVal || p.name.toLowerCase() === currentVal.toLowerCase())) ? 'selected' : '';
                    html += `<option value="${escapeHtml(p.name)}" ${isSel}>${escapeHtml(p.name)}</option>`;
                });
                html += '</optgroup>';
            }

            // If selected custom customer not found in list, add as option
            if (currentVal && !cachedCompaniesList.some(c => c.name === currentVal) && !cachedContactsList.some(ct => ct.name === currentVal) && !cachedPastList.some(p => p.name === currentVal)) {
                html += `<option value="${escapeHtml(currentVal)}" selected>💼 ${escapeHtml(currentVal)} (Ընտրված)</option>`;
            }

            selectCustomer.innerHTML = html;
        }

        // Deal info live lookup
        async function fetchDealInfo(dealId) {
            if (!dealId || dealId <= 0) return;
            if (dealFetchStatus) {
                dealFetchStatus.innerHTML = `<span style="color: #2066b0;">${ICONS.loader} Ստուգվում է Bitrix24 գործարք #${dealId}-ը...</span>`;
            }
            try {
                const res = await fetch(`api/crm_entities.php?deal_id=${dealId}`);
                const data = await res.json();
                if (data.success && (data.manager || data.customer)) {
                    if (data.manager) renderManagerSelect(data.manager);
                    if (data.customer) renderCustomerSelect(data.customer);
                    if (data.delivery_date) {
                        const dateEl = document.getElementById('newResDate');
                        if (dateEl) dateEl.value = data.delivery_date;
                    }
                    if (dealFetchStatus) {
                        dealFetchStatus.innerHTML = `<span style="color: var(--b24-success-dark); font-weight: 600;">${ICONS.check} Գործարք #${dealId}՝ ${escapeHtml(data.customer || '')} (${escapeHtml(data.manager || '')})</span>`;
                    }
                } else {
                    if (dealFetchStatus) {
                        dealFetchStatus.innerHTML = `<span style="color: var(--b24-text-muted);">Գործարք #${dealId}-ը ստուգվեց:</span>`;
                    }
                }
            } catch (e) {
                if (dealFetchStatus) {
                    dealFetchStatus.innerHTML = `<span style="color: var(--b24-danger);">Չհաջողվեց կապ հաստատել:</span>`;
                }
            }
        }

        if (fetchDealInfoBtn && dealIdInput) {
            fetchDealInfoBtn.addEventListener('click', () => {
                fetchDealInfo(dealIdInput.value);
            });
        }
        if (dealIdInput) {
            dealIdInput.addEventListener('change', () => {
                fetchDealInfo(dealIdInput.value);
            });
        }

        // Refresh triggers
        if (refreshManagersBtn) {
            refreshManagersBtn.addEventListener('click', () => loadCrmEntities(true));
        }
        if (refreshCustomersBtn) {
            refreshCustomersBtn.addEventListener('click', () => loadCrmEntities(true));
        }

        // Sub-Modal: Add Customer in Bitrix24
        if (openAddCrmCustomerBtn && addCrmCustomerModal) {
            openAddCrmCustomerBtn.addEventListener('click', () => {
                addCrmCustomerModal.classList.add('active');
            });
        }

        // Toggle Company / Contact fields
        const typeCompanyRadio = document.getElementById('typeCompanyRadio');
        const typeContactRadio = document.getElementById('typeContactRadio');
        const companyFieldsGroup = document.getElementById('companyFieldsGroup');
        const contactFieldsGroup = document.getElementById('contactFieldsGroup');

        if (typeCompanyRadio && typeContactRadio) {
            typeCompanyRadio.addEventListener('change', () => {
                if (companyFieldsGroup) companyFieldsGroup.style.display = 'block';
                if (contactFieldsGroup) contactFieldsGroup.style.display = 'none';
            });
            typeContactRadio.addEventListener('change', () => {
                if (companyFieldsGroup) companyFieldsGroup.style.display = 'none';
                if (contactFieldsGroup) contactFieldsGroup.style.display = 'block';
            });
        }

        if (saveCrmCustomerBtn) {
            saveCrmCustomerBtn.addEventListener('click', async () => {
                const isCompany = typeCompanyRadio ? typeCompanyRadio.checked : true;
                const phone = document.getElementById('newCrmPhone').value;
                const email = document.getElementById('newCrmEmail').value;

                let payload = {};
                if (isCompany) {
                    const title = document.getElementById('newCrmCompanyTitle').value.trim();
                    if (!title) {
                        showToast('Մուտքագրեք ընկերության անվանումը', 'error');
                        return;
                    }
                    payload = {
                        action: 'add_company',
                        title: title,
                        phone: phone,
                        email: email
                    };
                } else {
                    const name = document.getElementById('newCrmContactName').value.trim();
                    const lastName = document.getElementById('newCrmContactLastName').value.trim();
                    if (!name) {
                        showToast('Մուտքագրեք կոնտակտի անունը', 'error');
                        return;
                    }
                    payload = {
                        action: 'add_contact',
                        name: name,
                        last_name: lastName,
                        phone: phone,
                        email: email
                    };
                }

                saveCrmCustomerBtn.disabled = true;
                saveCrmCustomerBtn.innerHTML = `${ICONS.loader} Գրանցվում է Bitrix24-ում...`;

                try {
                    const res = await fetch('api/crm_entities.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (json.success) {
                        showToast(json.message, 'success');
                        if (addCrmCustomerModal) addCrmCustomerModal.classList.remove('active');
                        // Reload and select newly created customer
                        await loadCrmEntities(false, json.entity.name);
                        // Clear inputs
                        if (document.getElementById('newCrmCompanyTitle')) document.getElementById('newCrmCompanyTitle').value = '';
                        if (document.getElementById('newCrmContactName')) document.getElementById('newCrmContactName').value = '';
                        if (document.getElementById('newCrmContactLastName')) document.getElementById('newCrmContactLastName').value = '';
                        if (document.getElementById('newCrmPhone')) document.getElementById('newCrmPhone').value = '';
                        if (document.getElementById('newCrmEmail')) document.getElementById('newCrmEmail').value = '';
                    } else {
                        showToast(json.error || 'Գրանցման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                } finally {
                    saveCrmCustomerBtn.disabled = false;
                    saveCrmCustomerBtn.innerHTML = `${ICONS.check} Ստեղծել Bitrix24-ում և ընտրել`;
                }
            });
        }

        // Sub-Modal: Add Manager
        if (openAddManagerBtn && addCrmManagerModal) {
            openAddManagerBtn.addEventListener('click', () => {
                addCrmManagerModal.classList.add('active');
            });
        }

        if (saveCrmManagerBtn) {
            saveCrmManagerBtn.addEventListener('click', async () => {
                const name = document.getElementById('newManagerName').value.trim();
                const email = document.getElementById('newManagerEmail').value.trim();
                const role = document.getElementById('newManagerRole').value;

                if (!name) {
                    showToast('Մուտքագրեք մենեջերի անունը', 'error');
                    return;
                }

                saveCrmManagerBtn.disabled = true;
                try {
                    const res = await fetch('api/crm_entities.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'add_manager',
                            name: name,
                            email: email,
                            role_code: role
                        })
                    });
                    const json = await res.json();
                    if (json.success) {
                        showToast(json.message, 'success');
                        if (addCrmManagerModal) addCrmManagerModal.classList.remove('active');
                        // Reload and select newly created manager
                        await loadCrmEntities(false, null, json.entity.name);
                        document.getElementById('newManagerName').value = '';
                        document.getElementById('newManagerEmail').value = '';
                    } else {
                        showToast(json.error || 'Ավելացման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                } finally {
                    saveCrmManagerBtn.disabled = false;
                }
            });
        }

        // Open Reservation Modal
        if (openAddReservationBtn) {
            openAddReservationBtn.addEventListener('click', () => {
                addReservationModal.classList.add('active');
                loadCrmEntities(false);
                if (dealIdInput && dealIdInput.value) {
                    fetchDealInfo(dealIdInput.value);
                }
            });
        }

        // Initial load of CRM entities
        loadCrmEntities(false);

        // Save Reservation
        if (saveReservationBtn) {
            saveReservationBtn.addEventListener('click', async () => {
                const dealId = document.getElementById('newResDealId').value;
                const productId = document.getElementById('newResProduct').value;
                const qty = document.getElementById('newResQty').value;
                const date = document.getElementById('newResDate').value;
                const manager = document.getElementById('newResManager').value;
                const customer = document.getElementById('newResCustomer').value;
                const notes = document.getElementById('newResNotes').value;

                if (!dealId || !productId || !qty || !date || !manager || !customer) {
                    showToast('Լրացրեք բոլոր պարտադիր դաշտերը (գործարք, ապրանք, քանակ, մենեջեր, հաճախորդ)', 'error');
                    return;
                }

                saveReservationBtn.disabled = true;
                try {
                    const res = await fetch('api/reserve.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            deal_id: dealId,
                            product_id: productId,
                            quantity: qty,
                            delivery_date: date,
                            manager_name: manager,
                            customer_name: customer,
                            notes: notes
                        })
                    });
                    const json = await res.json();
                    if (json.success) {
                        showToast('Ապրանքը հաջողությամբ ամրագրվեց:', 'success');
                        addReservationModal.classList.remove('active');
                        loadDashboard();
                        loadReservations();
                        loadProducts();
                        loadKpis();
                    } else {
                        showToast(json.error || 'Ամրագրման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                } finally {
                    saveReservationBtn.disabled = false;
                }
            });
        }

        // Save Product Thresholds & Delivery Lead Time Days
        const saveThresholdsBtn = document.getElementById('saveProductThresholdsBtn');
        const editProductModal = document.getElementById('editProductModal');
        if (saveThresholdsBtn) {
            saveThresholdsBtn.addEventListener('click', async () => {
                const bitrixProductId = document.getElementById('editProductBitrixId').value;
                const minStock = document.getElementById('editProductMinStock').value;
                const maxStock = document.getElementById('editProductMaxStock').value;
                const deliveryDays = document.getElementById('editProductDeliveryDays').value;

                if (!bitrixProductId) return;

                saveThresholdsBtn.disabled = true;
                saveThresholdsBtn.innerHTML = `${ICONS.loader} Պահպանվում է...`;

                try {
                    const res = await fetch('api/products.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'update_thresholds',
                            bitrix_product_id: bitrixProductId,
                            min_stock: minStock,
                            max_stock: maxStock,
                            delivery_days: deliveryDays
                        })
                    });
                    const json = await res.json();
                    if (json.success) {
                        showToast(json.message, 'success');
                        if (editProductModal) editProductModal.classList.remove('active');
                        loadProducts();
                        loadDashboard();
                    } else {
                        showToast(json.error || 'Պահպանման սխալ', 'error');
                    }
                } catch (e) {
                    showToast('Կապի խափանում', 'error');
                } finally {
                    saveThresholdsBtn.disabled = false;
                    saveThresholdsBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg> Պահպանել`;
                }
            });
        }

        // Search & Filter Listeners
        document.getElementById('searchShipmentInput')?.addEventListener('input', () => loadShipments());
        document.getElementById('filterShipmentStatus')?.addEventListener('change', () => loadShipments());
        const bindRefresh = (btnId, loadFn) => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.addEventListener('click', async () => {
                    const orig = btn.innerHTML;
                    btn.innerHTML = `<svg class="spin-icon" style="animation: spin 1s linear infinite; margin-right: 4px;" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg> Թարմացվում է...`;
                    btn.disabled = true;
                    try { await loadFn(); } catch (e) {}
                    btn.innerHTML = orig;
                    btn.disabled = false;
                });
            }
        };

        bindRefresh('refreshShipmentsBtn', loadShipments);
        
        document.getElementById('searchReservationInput')?.addEventListener('input', () => loadReservations());
        document.getElementById('filterReservationStatus')?.addEventListener('change', () => loadReservations());
        
        bindRefresh('refreshReservationsBtn', loadReservations);
        bindRefresh('refreshProductsBtn', loadProducts);
    }

    /**
     * Settings Form Handler & REST Connection Tester
     */
    async function initSettingsForm() {
        const form = document.getElementById('settingsForm');
        const testBtn = document.getElementById('testWebhookBtn');
        const statusBox = document.getElementById('connectionStatusBox');
        if (!form) return;

        // Load existing settings
        try {
            const res = await fetch('api/settings.php');
            const data = await res.json();
            if (data.success && data.settings) {
                const s = data.settings;
                const webhookInput = document.getElementById('settingWebhookUrl');
                const storeSelect = document.getElementById('settingDefaultStore');
                const ttlInput = document.getElementById('settingTtlDays');

                if (webhookInput && s.bitrix_webhook_url) webhookInput.value = s.bitrix_webhook_url;
                if (storeSelect && s.default_store_id) storeSelect.value = s.default_store_id;
                if (ttlInput && s.reservation_ttl_days) ttlInput.value = s.reservation_ttl_days;
            }
        } catch (e) {
            console.error('Settings load error', e);
        }

        // Test Webhook Button
        if (testBtn) {
            testBtn.addEventListener('click', async () => {
                const webhook = document.getElementById('settingWebhookUrl')?.value.trim() || '';
                if (!webhook) {
                    showToast('Մուտքագրեք վեբհուկի URL-ը', 'error');
                    return;
                }

                testBtn.disabled = true;
                testBtn.innerHTML = `${ICONS.loader} Ստուգվում է...`;

                try {
                    const res = await fetch(`api/settings.php?action=test_connection&webhook_url=${encodeURIComponent(webhook)}`);
                    const json = await res.json();

                    if (statusBox) {
                        statusBox.style.display = 'block';
                        if (json.success && json.connected) {
                            statusBox.style.background = 'var(--b24-success-bg)';
                            statusBox.style.border = '1px solid var(--b24-success-border)';
                            statusBox.style.color = 'var(--b24-success-dark)';
                            statusBox.innerHTML = `${ICONS.check} ${json.message}`;
                            showToast('REST API կապը հաջողությամբ հաստատվեց:', 'success');
                        } else {
                            statusBox.style.background = 'var(--b24-danger-bg)';
                            statusBox.style.border = '1px solid var(--b24-danger-border)';
                            statusBox.style.color = 'var(--b24-danger-dark)';
                            statusBox.innerHTML = `${ICONS.x} ${json.error || 'Կապի սխալ'}`;
                            showToast(json.error || 'Կապի սխալ', 'error');
                        }
                    }
                } catch (err) {
                    showToast('Ցանցային հարցման սխալ', 'error');
                } finally {
                    testBtn.disabled = false;
                    testBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> Ստուգել REST կապը`;
                }
            });
        }

        // Save Settings
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const webhook = document.getElementById('settingWebhookUrl')?.value.trim() || '';
            const store = document.getElementById('settingDefaultStore')?.value || '1';
            const ttl = document.getElementById('settingTtlDays')?.value || '7';

            try {
                const res = await fetch('api/settings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ webhook_url: webhook, default_store: store, ttl_days: ttl })
                });
                const json = await res.json();
                if (json.success) {
                    showToast(json.message, 'success');
                } else {
                    showToast(json.error || 'Պահպանման սխալ', 'error');
                }
            } catch (err) {
                showToast('Հարցման սխալ', 'error');
            }
        });

        // Purge Mock Data Button
        const purgeBtn = document.getElementById('purgeTestDataBtn');
        if (purgeBtn) {
            purgeBtn.addEventListener('click', async () => {
                const confirmed = confirm('ՈՒՇԱԴՐՈՒԹՅՈՒՆ։ Այս գործողությունը անդառնալիորեն կջնջի բոլոր թեստային ապրանքները, մնացորդները, մատակարարումները և ամրագրումները: Ցանկանո՞ւմ եք շարունակել:');
                if (!confirmed) return;

                purgeBtn.disabled = true;
                purgeBtn.innerHTML = `${ICONS.loader} Մաքրվում է...`;

                try {
                    const res = await fetch('api/settings.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'purge_test_data' })
                    });
                    const json = await res.json();
                    if (json.success) {
                        showToast(json.message, 'success');
                        await loadAuthData();
                        loadDashboard();
                        loadShipments();
                        loadReservations();
                        loadProducts();
                    } else {
                        showToast(json.error || 'Մաքրման սխալ', 'error');
                    }
                } catch (err) {
                    showToast('Հարցման սխալ', 'error');
                } finally {
                    purgeBtn.disabled = false;
                    purgeBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"></path></svg> Մաքրել թեստային տվյալները`;
                }
            });
        }
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

    /**
     * Helper to render SVG Pie / Donut Chart
     */
    function renderSvgPie(svgEl, segments, isDonut = false) {
        if (!svgEl) return;
        const total = segments.reduce((sum, s) => sum + (s.value || 0), 0);
        if (total <= 0) {
            svgEl.innerHTML = `<circle cx="100" cy="100" r="70" fill="#f0f2f5" /><text x="100" y="105" text-anchor="middle" font-size="12" fill="#8899aa">Տվյալներ չկան</text>`;
            return;
        }

        const cx = 100, cy = 100, r = 70;
        let cumulativeAngle = -Math.PI / 2;
        let pathsHtml = '';

        segments.forEach(seg => {
            if (seg.value <= 0) return;
            const sliceAngle = (seg.value / total) * 2 * Math.PI;
            const x1 = cx + r * Math.cos(cumulativeAngle);
            const y1 = cy + r * Math.sin(cumulativeAngle);
            const x2 = cx + r * Math.cos(cumulativeAngle + sliceAngle);
            const y2 = cy + r * Math.sin(cumulativeAngle + sliceAngle);
            const largeArc = sliceAngle > Math.PI ? 1 : 0;

            if (segments.filter(s => s.value > 0).length === 1) {
                pathsHtml += `<circle cx="${cx}" cy="${cy}" r="${r}" fill="${seg.color}" />`;
            } else {
                const d = `M ${cx} ${cy} L ${x1} ${y1} A ${r} ${r} 0 ${largeArc} 1 ${x2} ${y2} Z`;
                pathsHtml += `<path d="${d}" fill="${seg.color}" stroke="#ffffff" stroke-width="1.5"><title>${escapeHtml(seg.label)}: ${seg.value} (${Math.round(seg.value / total * 100)}%)</title></path>`;
            }

            cumulativeAngle += sliceAngle;
        });

        if (isDonut) {
            pathsHtml += `<circle cx="${cx}" cy="${cy}" r="${r * 0.5}" fill="#ffffff" />`;
            pathsHtml += `<text x="${cx}" y="${cy + 5}" text-anchor="middle" font-size="13" font-weight="700" fill="#334455">${Math.round(segments[0]?.value / total * 100 || 0)}%</text>`;
        }

        svgEl.innerHTML = pathsHtml;
    }

    async function loadDashboard() {
        const dashLastUpdate = document.getElementById('dashLastUpdate');
        const dashRiskSection = document.getElementById('dashRiskSection');
        const dashCriticalBlock = document.getElementById('dashCriticalBlock');
        const dashCriticalTitle = document.getElementById('dashCriticalTitle');
        const dashCriticalList = document.getElementById('dashCriticalList');
        const dashShortageBlock = document.getElementById('dashShortageBlock');
        const dashShortageTitle = document.getElementById('dashShortageTitle');
        const dashShortageList = document.getElementById('dashShortageList');
        const dashProductTableBody = document.getElementById('dashProductTableBody');
        const dashTableSearch = document.getElementById('dashTableSearch');

        const metricsTableBody = document.getElementById('metricsTableBody');
        const revenueTableBody = document.getElementById('revenueTableBody');
        const topTurnoverTableBody = document.getElementById('topTurnoverTableBody');
        const donutSvg = document.getElementById('donutSvg');
        const pieSvg = document.getElementById('pieSvg');

        try {
            const res = await fetch('api/dashboard.php');
            const data = await res.json();
            if (!data.success) {
                showToast(data.error || 'Վահանակի տվյալների բեռնման սխալ', 'error');
                return;
            }

            const s = data.summary;
            const prods = data.products || [];

            if (dashLastUpdate) {
                const now = new Date();
                dashLastUpdate.textContent = `Թարմացված է՝ ${now.toLocaleDateString('hy-AM')} ${now.toLocaleTimeString('hy-AM')}`;
            }

            // 1. Metrika Top Cards (Real Live Data)
            const totalQty = s.total_quantity || 0;
            const availQty = s.total_available || 0;
            const resQty = s.total_reserved || 0;
            const incomingQty = s.incoming_confirmed_qty || 0;

            const elTop1 = document.getElementById('kpiTopVal1');
            if (elTop1) elTop1.textContent = totalQty.toLocaleString('hy-AM');
            
            const elTop2 = document.getElementById('kpiTopVal2');
            if (elTop2) elTop2.textContent = availQty.toLocaleString('hy-AM');

            const elTop3 = document.getElementById('kpiTopVal3');
            if (elTop3) elTop3.textContent = resQty.toLocaleString('hy-AM');

            // 2. Metrika Sales / Stock Funnel (Real Live Data)
            const maxVal = Math.max(totalQty, 1);
            const funnelVal1 = document.getElementById('funnelVal1');
            const funnelBar1 = document.getElementById('funnelBar1');
            if (funnelVal1) funnelVal1.textContent = totalQty.toLocaleString('hy-AM') + ' հատ';
            if (funnelBar1) funnelBar1.style.width = '100%';

            const funnelVal2 = document.getElementById('funnelVal2');
            const funnelBar2 = document.getElementById('funnelBar2');
            if (funnelVal2) funnelVal2.textContent = availQty.toLocaleString('hy-AM') + ' հատ';
            if (funnelBar2) funnelBar2.style.width = Math.min(100, Math.max(10, Math.round((availQty / maxVal) * 100))) + '%';

            const funnelVal3 = document.getElementById('funnelVal3');
            const funnelBar3 = document.getElementById('funnelBar3');
            if (funnelVal3) funnelVal3.textContent = resQty.toLocaleString('hy-AM') + ' հատ';
            if (funnelBar3) funnelBar3.style.width = Math.min(100, Math.max(8, Math.round((resQty / maxVal) * 100))) + '%';

            const funnelVal4 = document.getElementById('funnelVal4');
            const funnelBar4 = document.getElementById('funnelBar4');
            if (funnelVal4) funnelVal4.textContent = incomingQty.toLocaleString('hy-AM') + ' հատ';
            if (funnelBar4) funnelBar4.style.width = Math.min(100, Math.max(8, Math.round((incomingQty / maxVal) * 100))) + '%';

            const shortageCount = (data.risk_shortage || []).length;
            const funnelVal5 = document.getElementById('funnelVal5');
            const funnelBar5 = document.getElementById('funnelBar5');
            if (funnelVal5) funnelVal5.textContent = shortageCount + ' ապրանք';
            if (funnelBar5) funnelBar5.style.width = Math.min(100, Math.max(8, Math.round((shortageCount / Math.max(prods.length, 1)) * 100))) + '%';

            const criticalCount = (data.risk_critical || []).length;
            const funnelVal6 = document.getElementById('funnelVal6');
            const funnelBar6 = document.getElementById('funnelBar6');
            if (funnelVal6) funnelVal6.textContent = criticalCount + ' ապրանք';
            if (funnelBar6) funnelBar6.style.width = Math.min(100, Math.max(6, Math.round((criticalCount / Math.max(prods.length, 1)) * 100))) + '%';

            // 3. Metrika Circular Gauge Mini Table (Real Live Data)
            const gaugeValMoney = document.getElementById('gaugeValMoney');
            if (gaugeValMoney) gaugeValMoney.textContent = s.warehouse_value_fmt + ' ֏';

            const gaugeValTotal = document.getElementById('gaugeValTotal');
            if (gaugeValTotal) gaugeValTotal.textContent = totalQty.toLocaleString('hy-AM') + ' հատ';

            const gaugeValFree = document.getElementById('gaugeValFree');
            if (gaugeValFree) gaugeValFree.textContent = availQty.toLocaleString('hy-AM') + ' հատ';

            const gaugeValReserved = document.getElementById('gaugeValReserved');
            if (gaugeValReserved) gaugeValReserved.textContent = resQty.toLocaleString('hy-AM') + ' հատ';

            const gaugeValIncoming = document.getElementById('gaugeValIncoming');
            if (gaugeValIncoming) gaugeValIncoming.textContent = incomingQty.toLocaleString('hy-AM') + ' հատ';



            // 5. Multi-slice Pie Chart (Status Distribution)
            const countNormal = prods.filter(p => p.risk_status === 'normal').length;
            const countCritical = (data.risk_critical || []).length;
            const countShortage = (data.risk_shortage || []).length;
            const countWarning = (data.risk_warning || []).length;
            const countExcess = (data.risk_excess || []).length;

            renderSvgPie(pieSvg, [
                { label: 'Նորմալ', value: countNormal, color: '#44bb44' },
                { label: 'Ավելցուկ', value: countExcess, color: '#0088cc' },
                { label: 'Նախազգուշացում', value: countWarning, color: '#f1c40f' },
                { label: 'Պակասորդ', value: countShortage, color: '#e65c00' },
                { label: 'Կրիտիկական', value: countCritical, color: '#d93025' }
            ], false);

            // 6. Top Turnover Products Table
            if (topTurnoverTableBody) {
                const sorted = [...prods].sort((a, b) => (b.current_stock || 0) - (a.current_stock || 0)).slice(0, 5);
                topTurnoverTableBody.innerHTML = sorted.map(p => `
                    <tr>
                        <td><strong>${escapeHtml(p.name)}</strong></td>
                        <td class="text-right">${p.available_stock} ${p.unit || 'հատ'}</td>
                        <td class="text-right">${p.coverage_days === 999 ? 'Պլան' : p.coverage_days + ' օր'}</td>
                        <td class="text-right"><span class="badge ${p.risk_status === 'critical' ? 'badge-danger' : (p.risk_status === 'warning' ? 'badge-warning' : 'badge-success')}">${p.risk_status === 'critical' ? 'Ռիսկ' : 'Նորմալ'}</span></td>
                    </tr>
                `).join('');
            }

            // 7. Risk Alerts Strip
            let hasRisks = false;
            if (data.risk_critical && data.risk_critical.length > 0) {
                hasRisks = true;
                if (dashCriticalBlock) dashCriticalBlock.style.display = 'block';
                if (dashCriticalTitle) dashCriticalTitle.textContent = `Կրիտիկական ռիսկ (սպառման վտանգ 0-${data.settings.critical_coverage_days} օր)`;
                if (dashCriticalList) {
                    dashCriticalList.innerHTML = data.risk_critical.map(p => `
                        <div>• <strong>${escapeHtml(p.name)}</strong> — Ազատ մնացորդ՝ <strong>${p.available_stock} հատ</strong>, Ծածկույթ՝ <strong style="color: #d93025;">${p.coverage_days} օր</strong></div>
                    `).join('');
                }
            } else {
                if (dashCriticalBlock) dashCriticalBlock.style.display = 'none';
            }

            if (data.risk_shortage && data.risk_shortage.length > 0) {
                hasRisks = true;
                if (dashShortageBlock) dashShortageBlock.style.display = 'block';
                if (dashShortageTitle) dashShortageTitle.textContent = "Պակասորդի ռիսկ (նվազագույն շեմից ցածր)";
                if (dashShortageList) {
                    dashShortageList.innerHTML = data.risk_shortage.map(p => `
                        <div>• <strong>${escapeHtml(p.name)}</strong> — Ազատ մնացորդ՝ <strong>${p.available_stock} հատ</strong> (Նվազագույնը՝ ${p.min_stock} հատ)</div>
                    `).join('');
                }
            } else {
                if (dashShortageBlock) dashShortageBlock.style.display = 'none';
            }

            if (dashRiskSection) {
                dashRiskSection.style.display = hasRisks ? 'block' : 'none';
            }

            // 8. Advanced Full Product Matrix Table (Sorting, Filtering, Pagination, Export)
            let tableState = {
                sortCol: 'current_stock',
                sortDir: 'desc',
                statusFilter: 'ALL',
                search: '',
                page: 1,
                pageSize: 50
            };

            const dashFilterStatus = document.getElementById('dashFilterStatus');
            const dashPageSize = document.getElementById('dashPageSize');
            const dashExportCsvBtn = document.getElementById('dashExportCsvBtn');
            const dashPaginationInfo = document.getElementById('dashPaginationInfo');
            const dashPaginationControls = document.getElementById('dashPaginationControls');
            const dashTotalBadge = document.getElementById('dashTotalBadge');

            if (dashTotalBadge) {
                dashTotalBadge.textContent = `${prods.length.toLocaleString('hy-AM')} ապրանք`;
            }

            function getFilteredAndSortedProducts() {
                let list = [...prods];

                // 1. Filter by Status
                if (tableState.statusFilter === 'IN_STOCK') {
                    list = list.filter(p => parseFloat(p.current_stock || 0) > 0);
                } else if (tableState.statusFilter === 'RESERVED') {
                    list = list.filter(p => parseFloat(p.reserved_stock || 0) > 0);
                } else if (tableState.statusFilter !== 'ALL') {
                    list = list.filter(p => p.risk_status === tableState.statusFilter);
                }

                // 2. Filter by Search Query
                if (tableState.search) {
                    const q = tableState.search.toLowerCase().trim();
                    list = list.filter(p => 
                        (p.name && p.name.toLowerCase().includes(q)) || 
                        (p.sku && p.sku.toLowerCase().includes(q)) ||
                        (p.bitrix_product_id && p.bitrix_product_id.toString().includes(q))
                    );
                }

                // 3. Sort
                list.sort((a, b) => {
                    let valA = a[tableState.sortCol];
                    let valB = b[tableState.sortCol];

                    // Numeric fields
                    const numFields = ['current_stock', 'reserved_stock', 'available_stock', 'min_stock', 'max_stock', 'coverage_days', 'free_stock_value', 'price', 'cost_price'];
                    if (numFields.includes(tableState.sortCol)) {
                        valA = parseFloat(valA || 0);
                        valB = parseFloat(valB || 0);
                    } else {
                        valA = (valA || '').toString().toLowerCase();
                        valB = (valB || '').toString().toLowerCase();
                    }

                    if (valA < valB) return tableState.sortDir === 'asc' ? -1 : 1;
                    if (valA > valB) return tableState.sortDir === 'asc' ? 1 : -1;
                    return 0;
                });

                return list;
            }

            function renderTable() {
                if (!dashProductTableBody) return;

                const filtered = getFilteredAndSortedProducts();
                const totalCount = filtered.length;

                // Pagination slice
                const isAll = tableState.pageSize === 'ALL';
                const size = isAll ? totalCount : parseInt(tableState.pageSize, 10);
                const totalPages = Math.max(1, Math.ceil(totalCount / size));

                if (tableState.page > totalPages) tableState.page = totalPages;
                if (tableState.page < 1) tableState.page = 1;

                const startIdx = (tableState.page - 1) * size;
                const endIdx = isAll ? totalCount : Math.min(startIdx + size, totalCount);
                const pageItems = isAll ? filtered : filtered.slice(startIdx, endIdx);

                // Update Table Header Sort Icons
                document.querySelectorAll('#dashProductTable th.sortable-th').forEach(th => {
                    const col = th.getAttribute('data-sort');
                    const iconSpan = th.querySelector('.sort-icon');
                    if (iconSpan) {
                        if (col === tableState.sortCol) {
                            iconSpan.textContent = tableState.sortDir === 'asc' ? ' ▲' : ' ▼';
                            th.style.color = 'var(--ga-blue)';
                        } else {
                            iconSpan.textContent = ' ⇅';
                            th.style.color = '';
                        }
                    }
                });

                // Render Rows
                if (pageItems.length === 0) {
                    dashProductTableBody.innerHTML = `<tr><td colspan="11" style="text-align: center; color: var(--ga-text-muted); padding: 30px;">Ապրանքներ չեն գտնվել տրված ֆիլտրերով</td></tr>`;
                } else {
                    dashProductTableBody.innerHTML = pageItems.map(p => {
                        const unit = p.unit || 'հատ';
                        let badge = '<span class="badge badge-success">Նորմալ</span>';
                        if (p.risk_status === 'critical') badge = '<span class="badge badge-danger">Կրիտիկական</span>';
                        else if (p.risk_status === 'shortage') badge = '<span class="badge badge-warning">Պակասորդ</span>';
                        else if (p.risk_status === 'warning') badge = '<span class="badge badge-warning">Վտանգավոր</span>';
                        else if (p.risk_status === 'excess') badge = '<span class="badge badge-info">Ավելցուկ</span>';

                        const coverageText = p.coverage_days === 999 ? 'Պլանային' : `${p.coverage_days} օր`;
                        const curStock = parseFloat(p.current_stock || 0);
                        const curStockStyle = curStock > 0 ? 'font-weight: 700; color: #202124;' : 'color: #8899aa;';

                        return `
                            <tr>
                                <td>
                                    <strong>${escapeHtml(p.name)}</strong>
                                    <br><small style="color: var(--ga-text-secondary); font-size: 11px;">ID՝ #${p.bitrix_product_id}</small>
                                </td>
                                <td><code>${escapeHtml(p.sku || '-')}</code></td>
                                <td class="text-right" style="${curStockStyle}">${curStock.toLocaleString('hy-AM')} ${unit}</td>
                                <td class="text-right" style="color: var(--ga-orange-dark);">${parseFloat(p.reserved_stock || 0).toLocaleString('hy-AM')} ${unit}</td>
                                <td class="text-right" style="font-weight: 700; color: var(--ga-blue);">${parseFloat(p.available_stock || 0).toLocaleString('hy-AM')} ${unit}</td>
                                <td class="text-right">${parseFloat(p.min_stock || 0).toLocaleString('hy-AM')} ${unit}</td>
                                <td class="text-right">${parseFloat(p.max_stock || 0).toLocaleString('hy-AM')} ${unit}</td>
                                <td class="text-right"><strong>${coverageText}</strong></td>
                                <td class="text-right">${p.turnover}</td>
                                <td class="text-right"><strong>${parseFloat(p.free_stock_value || 0).toLocaleString('hy-AM')} ֏</strong></td>
                                <td>${badge}</td>
                            </tr>
                        `;
                    }).join('');
                    if (window.applyTablePreferences) window.applyTablePreferences('dashProductTable');
                }

                // Update Pagination Info
                if (dashPaginationInfo) {
                    if (totalCount === 0) {
                        dashPaginationInfo.textContent = 'Արդյունքներ չկան';
                    } else {
                        dashPaginationInfo.textContent = `Ցուցադրված է ${startIdx + 1} - ${endIdx} (ընդհանուր՝ ${totalCount.toLocaleString('hy-AM')} ապրանք)`;
                    }
                }

                // Render Pagination Controls
                if (dashPaginationControls) {
                    if (isAll || totalPages <= 1) {
                        dashPaginationControls.innerHTML = '';
                        return;
                    }

                    let btnHtml = '';
                    // Prev button
                    btnHtml += `<button class="btn btn-secondary btn-sm" id="btnPagePrev" ${tableState.page <= 1 ? 'disabled' : ''} style="padding: 2px 8px; height: 24px;">«</button>`;

                    // Page number buttons (sliding window)
                    const maxButtons = 5;
                    let startPage = Math.max(1, tableState.page - 2);
                    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
                    if (endPage - startPage < maxButtons - 1) {
                        startPage = Math.max(1, endPage - maxButtons + 1);
                    }

                    if (startPage > 1) {
                        btnHtml += `<button class="btn btn-secondary btn-sm page-num-btn" data-p="1" style="padding: 2px 8px; height: 24px;">1</button>`;
                        if (startPage > 2) btnHtml += `<span style="color: #999;">...</span>`;
                    }

                    for (let pNum = startPage; pNum <= endPage; pNum++) {
                        const activeStyle = pNum === tableState.page ? 'background-color: var(--ga-blue); color: #fff; border-color: var(--ga-blue); font-weight: 700;' : '';
                        btnHtml += `<button class="btn btn-secondary btn-sm page-num-btn" data-p="${pNum}" style="padding: 2px 8px; height: 24px; ${activeStyle}">${pNum}</button>`;
                    }

                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) btnHtml += `<span style="color: #999;">...</span>`;
                        btnHtml += `<button class="btn btn-secondary btn-sm page-num-btn" data-p="${totalPages}" style="padding: 2px 8px; height: 24px;">${totalPages}</button>`;
                    }

                    // Next button
                    btnHtml += `<button class="btn btn-secondary btn-sm" id="btnPageNext" ${tableState.page >= totalPages ? 'disabled' : ''} style="padding: 2px 8px; height: 24px;">»</button>`;

                    dashPaginationControls.innerHTML = btnHtml;

                    // Bind page clicks
                    dashPaginationControls.querySelectorAll('.page-num-btn').forEach(b => {
                        b.onclick = () => {
                            tableState.page = parseInt(b.getAttribute('data-p'), 10);
                            renderTable();
                        };
                    });

                    const prevBtn = document.getElementById('btnPagePrev');
                    if (prevBtn) {
                        prevBtn.onclick = () => {
                            if (tableState.page > 1) {
                                tableState.page--;
                                renderTable();
                            }
                        };
                    }

                    const nextBtn = document.getElementById('btnPageNext');
                    if (nextBtn) {
                        nextBtn.onclick = () => {
                            if (tableState.page < totalPages) {
                                tableState.page++;
                                renderTable();
                            }
                        };
                    }
                }
            }

            // Bind Table Header Sorting
            document.querySelectorAll('#dashProductTable th.sortable-th').forEach(th => {
                th.onclick = () => {
                    const col = th.getAttribute('data-sort');
                    if (tableState.sortCol === col) {
                        tableState.sortDir = tableState.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        tableState.sortCol = col;
                        tableState.sortDir = col === 'name' || col === 'sku' ? 'asc' : 'desc';
                    }
                    tableState.page = 1;
                    renderTable();
                };
            });

            // Bind Status Filter
            if (dashFilterStatus) {
                dashFilterStatus.onchange = (e) => {
                    tableState.statusFilter = e.target.value;
                    tableState.page = 1;
                    renderTable();
                };
            }

            // Bind Page Size
            if (dashPageSize) {
                dashPageSize.onchange = (e) => {
                    tableState.pageSize = e.target.value;
                    tableState.page = 1;
                    renderTable();
                };
            }

            // Bind Live Search
            if (dashTableSearch) {
                dashTableSearch.oninput = (e) => {
                    tableState.search = e.target.value;
                    tableState.page = 1;
                    renderTable();
                };
            }

            // Bind CSV Export
            if (dashExportCsvBtn) {
                dashExportCsvBtn.onclick = () => {
                    const exportItems = getFilteredAndSortedProducts();
                    const dateStr = new Date().toISOString().slice(0, 10);
                    const headers = ['ID', 'Ապրանքի անվանում', 'Արտիկուլ/Կոդ', 'Փաստացի մնացորդ', 'Ամրագրված', 'Հասանելի', 'Մին շեմ', 'Մաքս շեմ', 'Ծածկույթ (օր)', 'Շրջանառություն', 'Արժեք (AMD)', 'Ինքնարժեք (AMD)', 'Կարգավիճակ'];
                    const rows = exportItems.map(p => [
                        p.bitrix_product_id,
                        p.name,
                        p.sku || '-',
                        p.current_stock,
                        p.reserved_stock,
                        p.available_stock,
                        p.min_stock,
                        p.max_stock,
                        p.coverage_days === 999 ? 'Պլանային' : p.coverage_days,
                        p.turnover,
                        p.free_stock_value,
                        p.cost_price,
                        p.risk_status
                    ]);
                    downloadCsvFile(`warehouse_stock_matrix_${dateStr}.csv`, headers, rows);
                };
            }

            // Initial Render
            renderTable();

        } catch (e) {
            console.error('Dashboard load error', e);
            showToast('Վահանակի տվյալների հարցման սխալ', 'error');
        }
    }

    // Export Buttons Event Listeners for Products, Shipments, Reservations tabs
    const exportProductsCsvBtn = document.getElementById('exportProductsCsvBtn');
    if (exportProductsCsvBtn) {
        exportProductsCsvBtn.addEventListener('click', () => {
            const dateStr = new Date().toISOString().slice(0, 10);
            const headers = ['ID', 'Ապրանքի անվանում', 'Արտիկուլ/Կոդ', 'Առկա է պահեստում', 'Ամրագրված', 'Ազատ է հիմա', 'Սպասվող մուտքեր', 'Ընդհանուր ամրագրումներ', 'Գին (AMD)'];
            const rows = (cachedProducts || []).map(p => [
                p.bitrix_product_id,
                p.name,
                p.sku || '-',
                p.current_stock,
                p.reserved_stock,
                p.free_stock,
                p.total_incoming_confirmed,
                p.total_active_reserved,
                p.price
            ]);
            downloadCsvFile(`warehouse_products_${dateStr}.csv`, headers, rows);
        });
    }

    const exportShipmentsCsvBtn = document.getElementById('exportShipmentsCsvBtn');
    if (exportShipmentsCsvBtn) {
        exportShipmentsCsvBtn.addEventListener('click', () => {
            const dateStr = new Date().toISOString().slice(0, 10);
            const headers = ['Համար', 'Ապրանք', 'Արտիկուլ', 'Մատակարար', 'Քանակ', 'Սպասվող օր', 'Պահեստ', 'Կարգավիճակ', 'Մուտքի փաստաթուղթ'];
            const rows = (cachedShipments || []).map(s => [
                s.id,
                s.product_name || `Ապրանք #${s.bitrix_product_id}`,
                s.product_sku || '-',
                s.supplier_name,
                s.quantity,
                s.expected_date,
                s.warehouse_title || 'Կենտրոնական պահեստ',
                s.status,
                s.bitrix_doc_id || '-'
            ]);
            downloadCsvFile(`warehouse_shipments_${dateStr}.csv`, headers, rows);
        });
    }

    const exportReservationsCsvBtn = document.getElementById('exportReservationsCsvBtn');
    if (exportReservationsCsvBtn) {
        exportReservationsCsvBtn.addEventListener('click', () => {
            const dateStr = new Date().toISOString().slice(0, 10);
            const headers = ['Համար', 'Գործարք', 'Ապրանք', 'Արտիկուլ', 'Քանակ', 'Առաքման օր', 'Հաճախորդ', 'Մենեջեր', 'Կարգավիճակ', 'Ստեղծման ամսաթիվ'];
            const rows = (cachedReservations || []).map(r => [
                r.id,
                `Գործարք #${r.deal_id}`,
                r.product_name || `Ապրանք #${r.bitrix_product_id}`,
                r.product_sku || '-',
                r.quantity,
                r.delivery_date,
                r.customer_name || '-',
                r.manager_name || '-',
                r.status,
                r.created_at
            ]);
            downloadCsvFile(`warehouse_reservations_${dateStr}.csv`, headers, rows);
        });
    }

    /**
     * Universal CSV Download Helper with UTF-8 BOM & Semicolon Delimiter for Excel
     */
    function downloadCsvFile(filename, headers, rows) {
        if (!rows || rows.length === 0) {
            showToast('Արտահանման համար տվյալներ չկան', 'error');
            return;
        }
        let csvContent = "\uFEFF"; // UTF-8 BOM for Excel Armenian/Unicode support
        csvContent += headers.join(';') + "\n";
        rows.forEach(r => {
            const line = r.map(val => {
                if (val === null || val === undefined) return '""';
                const str = String(val).replace(/"/g, '""');
                return `"${str}"`;
            }).join(';');
            csvContent += line + "\n";
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(() => URL.revokeObjectURL(url), 1000);
        showToast(`Հաջողությամբ արտահանվեց ${rows.length} տող`, 'success');
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
