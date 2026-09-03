<?php
/**
 * Warehouse Stock & Availability Management Portal
 * Pure Natural Armenian Language (Հայերեն) & Black-and-White Vector Icons
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/AuthService.php';

$auth = new AuthService();
$currentUser = $auth->getCurrentUser();
$allUsers = $auth->getUsers();
?>
<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> - Ղեկավարման վահանակ</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232066b0' stroke-width='2'><path d='M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z'></path></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2026">
</head>
<body>

    <div class="app-layout">
        
        <!-- Left Sidebar Navigation Rail (Metrika Style) -->
        <aside class="app-sidebar">
            <div class="sidebar-top">
                <div class="brand-mark" title="Պահեստ" id="brandLogoBtn">
                    <svg width="34" height="34" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="36" height="36" rx="9" fill="url(#brandLogoGrad)"/>
                        <path d="M18 7.5L28.5 13.5V23.5L18 29.5L7.5 23.5V13.5L18 7.5Z" stroke="#FFFFFF" stroke-width="2.2" stroke-linejoin="round"/>
                        <path d="M18 7.5V29.5" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>
                        <path d="M7.5 13.5L18 19.5L28.5 13.5" stroke="#FFFFFF" stroke-width="2" stroke-linejoin="round"/>
                        <circle cx="18" cy="19.5" r="2.2" fill="#FFFFFF"/>
                        <defs>
                            <linearGradient id="brandLogoGrad" x1="0" y1="0" x2="36" y2="36" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2563EB"/>
                                <stop offset="100%" stop-color="#1D4ED8"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <button class="sidebar-btn" id="sidebarSearchBtn" title="Որոնել">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
                <div class="sidebar-toggle-switch" id="sidebarThemeToggle" title="Թեմայի փոփոխում">
                    <div class="toggle-circle"></div>
                </div>
                <button class="sidebar-btn" id="sidebarAddBtn" title="Ավելացնել">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
                <button class="sidebar-btn" id="sidebarFavoritesBtn" title="Ընտրվածներ">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </button>
                <button class="sidebar-btn active" id="sidebarGridBtn" title="Վահանակի ցանց">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                </button>
                <button class="sidebar-btn" id="sidebarMenuBtn" title="Ցանկ / Մենյու">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                </button>
            </div>

            <div class="sidebar-bottom">
                <div class="user-avatar-btn" id="sidebarAvatarBtn" title="Օգտատեր">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <button class="sidebar-btn" id="sidebarHelpBtn" title="Օգնություն">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </button>
            </div>
        </aside>

        <!-- Main Workspace Container -->
        <div class="app-main-layout">
            
            <!-- Metrika Top Bar (Armenian Language & Interactive Tools) -->
            <header class="app-metrika-header">
                <div class="header-left-title">
                    <div class="date-picker-pill" id="dateRangePill" title="Ընտրել ժամանակահատվածը">
                        <span>01.09.2026 — 30.09.2026</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                </div>

                <div class="header-right-tools">
                    <button class="header-tool-btn primary-icon" id="quickAddBtn" title="Ավելացնել նոր գրանցում">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <button class="header-tool-btn" id="headerRefreshBtn" title="Թարմացնել տվյալները">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    </button>
                    <div class="header-tool-btn" title="Ընթացիկ աշխատակից">
                        <select id="currentUserSelect" style="border: none; background: transparent; font-size: 12px; font-weight: 500; color: inherit; cursor: pointer; outline: none;">
                            <?php foreach ($allUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= $u['id'] == $currentUser['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['role_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="header-tool-btn" id="headerSettingsBtn" title="Կարգավորումներ">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    </button>
                    <button class="header-tool-btn" id="headerFilterBtn" title="Ֆիլտրել աղյուսակը">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                    </button>
                    <div class="projects-select-pill">
                        Ընտրված պահեստներ՝ 4
                    </div>
                </div>
            </header>

            <!-- Navigation Tabs Bar -->
            <nav class="nav-tabs-wrapper" style="margin: 0 0 14px 0; background: transparent; padding: 0;">
                <button class="nav-tab active" data-tab="tab-dashboard" data-permission="view_dashboard">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    Վահանակ
                </button>
                <button class="nav-tab" data-tab="tab-simulator" data-permission="view_simulator">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Ստուգել հասանելիությունը
                </button>
                <button class="nav-tab" data-tab="tab-shipments" data-permission="view_shipments">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    Մատակարարումներ
                </button>
                <button class="nav-tab" data-tab="tab-reservations" data-permission="view_reservations">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Ամրագրումներ
                </button>
                <button class="nav-tab" data-tab="tab-products" data-permission="view_products">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    Պահեստի մնացորդներ
                </button>
                <button class="nav-tab" data-tab="tab-roles" data-permission="manage_roles">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Աշխատակիցներ և Իրավունքներ
                </button>
                <button class="nav-tab" data-tab="tab-settings">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Կարգավորումներ
                </button>
            </nav>

            <!-- Tab Panes -->
            <main>
                <!-- 0. Dashboard Tab -->
                <div id="tab-dashboard" class="tab-pane" style="display: block;">
                    <?php include __DIR__ . '/views/dashboard_view.php'; ?>
                </div>

                <!-- 1. Simulator Tab -->
                <div id="tab-simulator" class="tab-pane" style="display: none;">
                    <?php include __DIR__ . '/views/simulator_view.php'; ?>
                </div>

                <!-- 2. Shipments Tab -->
                <div id="tab-shipments" class="tab-pane" style="display: none;">
                    <?php include __DIR__ . '/views/shipments_view.php'; ?>
                </div>

                <!-- 3. Reservations Tab -->
                <div id="tab-reservations" class="tab-pane" style="display: none;">
                    <?php include __DIR__ . '/views/reservations_view.php'; ?>
                </div>

                <!-- 4. Products Tab -->
                <div id="tab-products" class="tab-pane" style="display: none;">
                    <?php include __DIR__ . '/views/products_view.php'; ?>
                </div>

                <!-- 5. Roles & Permissions Tab -->
                <div id="tab-roles" class="tab-pane" style="display: none;">
                    <?php include __DIR__ . '/views/roles_view.php'; ?>
                </div>

                <!-- 6. Settings Tab -->
                <div id="tab-settings" class="tab-pane" style="display: none;">
                    <?php include __DIR__ . '/views/settings_view.php'; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
