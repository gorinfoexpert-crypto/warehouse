<div class="metrika-card" style="margin-bottom: 14px;">
    <div class="card-head-tools" style="padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
        <span class="metrika-card-title" style="font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            Համակարգի և REST API ինտեգրման կարգավորումներ
        </span>
    </div>
    <div class="metrika-card-body" style="padding: 12px 0 0 0;">
        <form id="settingsForm">
            <h3 style="margin-bottom: 8px; font-size: 13.5px; font-weight: 700; color: var(--ga-text-title); display: flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                REST API միացում (Inbound Webhook)
            </h3>
            <p style="color: var(--ga-text-muted); font-size: 11.5px; margin-bottom: 10px;">
                Համակարգն աշխատում է ուղիղ REST API վեբհուկով՝ առանց հավելված տեղադրելու բարդությունների: Բավական է տեղադրել ստացված մուտքային վեբհուկի հասցեն:
            </p>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" for="settingWebhookUrl" style="font-weight: 600; margin-bottom: 4px; display: block;">Մուտքային վեբհուկի հասցեն (Inbound Webhook URL)՝</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <input type="text" id="settingWebhookUrl" class="form-control" placeholder="https://yourportal.bitrix24.ru/rest/1/xxxxxxxxxxxx/" style="flex: 1; min-width: 260px; height: 30px;">
                    <button type="button" class="btn btn-secondary btn-sm" id="testWebhookBtn" style="white-space: nowrap;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        Ստուգել REST կապը
                    </button>
                </div>
                <small style="color: var(--ga-text-muted); font-size: 10.5px; margin-top: 4px; display: block;">
                    Վեբհուկի անհրաժեշտ իրավունքները՝ <strong>crm</strong> (Գործարքներ) և <strong>catalog</strong> (Ապրանքներ, մնացորդներ, պահեստի փաստաթղթեր):
                </small>
            </div>

            <!-- Connection Status Banner -->
            <div id="connectionStatusBox" style="display: none; margin-top: 10px; padding: 8px 12px; border-radius: var(--ga-radius-sm); font-size: 12px; font-weight: 600;"></div>

            <div class="form-row" style="display: flex; gap: 14px; margin-top: 14px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 240px;">
                    <h3 style="margin-bottom: 6px; font-size: 13px; font-weight: 700; color: var(--ga-text-title); display: flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        Հիմնական պահեստ
                    </h3>
                    <select id="settingDefaultStore" class="form-control" style="width: 100%; height: 30px;">
                        <option value="1" selected>Կենտրոնական պահեստ (Երևան)</option>
                        <option value="2">Տարանցիկ պահեստ (Գյումրի)</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1; min-width: 240px;">
                    <h3 style="margin-bottom: 6px; font-size: 13px; font-weight: 700; color: var(--ga-text-title); display: flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Ամրագրման ժամկետներ
                    </h3>
                    <input type="number" id="settingTtlDays" class="form-control" value="7" min="1" max="60" style="width: 100%; height: 30px;">
                </div>
            </div>

            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; align-items: center;">
                <button type="submit" class="btn btn-primary btn-sm" id="saveSettingsBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Պահպանել կարգավորումները
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Clear Test Data Card -->
<div class="metrika-card" style="margin-top: 14px; border-top: 3px solid #ef4444;">
    <div class="card-head-tools">
        <span class="metrika-card-title" style="font-size: 14px; font-weight: 700; color: #dc2626; display: flex; align-items: center; gap: 6px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            Թեստային տվյալների մաքրում (Reset / Purge Mock Data)
        </span>
    </div>
    <div class="metrika-card-body" style="padding: 10px 0 0 0;">
        <p style="color: var(--ga-text-secondary); font-size: 12px; margin-bottom: 12px;">
            Եթե պատրաստվում եք համակարգն աշխատեցնել Bitrix24 պորտալի ձեր իրական տվյալներով, սեղմեք ստորև գտնվող կոճակը: Այն ամբողջությամբ կջնջի համակարգի բոլոր թեստային ապրանքները, մնացորդները, մատակարարումները, ամրագրումները և թեստային աշխատակիցներին՝ բացառությամբ ձեր ընթացիկ օգտատիրոջ:
        </p>
        <div style="display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-sm" id="purgeTestDataBtn" style="background-color: #dc2626; color: #ffffff; border: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"></path></svg>
                Մաքրել թեստային տվյալները
            </button>
        </div>
    </div>
</div>
