<?php
/**
 * Deal Card Integration Assistant
 * Pure Natural Armenian Language Support (Հայերեն)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/BitrixRestClient.php';

$bitrix = new BitrixRestClient();
$message = null;
$error = null;
$errorDetails = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $webhook = trim($_POST['webhook_url'] ?? '');
    $handlerUrl = trim($_POST['handler_url'] ?? '');

    if (empty($handlerUrl)) {
        $error = 'Նշեք հավելվածի հասցեն (URL, օրինակ՝ https://your-domain.com/deal_card.php)';
    } else {
        if (!empty($webhook)) {
            $pdo = Database::getConnection();
            try {
                $stmt = $pdo->prepare("INSERT INTO settings (key, value, updated_at) VALUES ('bitrix_webhook_url', ?, datetime('now')) ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = datetime('now')");
                $stmt->execute([$webhook]);
            } catch (Exception $e) {
                $upd = $pdo->prepare("UPDATE settings SET value = ?, updated_at = datetime('now') WHERE key = 'bitrix_webhook_url'");
                $upd->execute([$webhook]);
            }
            $bitrix = new BitrixRestClient($webhook);
        }

        // Test basic connectivity first
        $testConn = $bitrix->call('crm.deal.get', ['id' => 1]);
        $hasGeneralAccess = !isset($testConn['error']) || $testConn['error'] !== 'INVALID_CREDENTIALS';

        // Attempt placement.bind
        $res = $bitrix->bindDealPlacement($handlerUrl, 'Ապրանքների հասանելիություն և Ամրագրում');
        if (!empty($res['result'])) {
            $message = 'Հավելվածը հաջողությամբ միացվեց գործարքների քարտին:';
        } else {
            $errDesc = $res['error_description'] ?? 'Անհայտ սխալ';
            $error = 'Միացման սխալ. ' . $errDesc;

            if (stripos($errDesc, 'higher privileges') !== false || stripos($errDesc, 'insufficient_scope') !== false) {
                $errorDetails = '
                    <strong>Ինչպե՞ս շտկել այս սխալը.</strong><br>
                    Վեբհուկը չունի «Տեղադրումներ / Встраивание приложений (placement)» իրավունքը:<br><br>
                    1. Բացեք ձեր CRM համակարգի ձախ մենյուից՝ <strong>Разработчикам → Другое → Входящий веб-хук</strong> (կամ խմբագրեք գործող վեբհուկը):<br>
                    2. «Права доступа» ցանկում անպայման նշեք հետևյալ 3 կետերը.<br>
                    &nbsp;&nbsp;• <strong>Управление CRM (crm)</strong><br>
                    &nbsp;&nbsp;• <strong>Торговый каталог (catalog)</strong><br>
                    &nbsp;&nbsp;• <strong>Встраивание приложений (placement)</strong><br>
                    3. Սեղմեք «Сохранить» և նոր ստացված հասցեն տեղադրեք այստեղ:<br><br>
                    <em>Հիշեցում.</em> Տեղական միջավայրում (sklad.loc) աշխատելիս գործարքի պատուհանը կարող եք բացել նաև ուղիղ հղումով՝ առանց ամպային տեղադրման:
                ';
            }
        }
    }
}

// Current host detector
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$detectedHandler = $protocol . ($_SERVER['HTTP_HOST'] ?? 'sklad.loc') . '/deal_card.php';
?>
<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Միացնել Գործարքներին</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container" style="max-width: 760px; margin-top: 40px;">
        <div class="card">
            <div class="card-header">
                <h2>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Միացնել Գործարքների քարտին
                </h2>
            </div>
            <div class="card-body">
                <p style="color: var(--b24-text-secondary); margin-bottom: 20px; font-size: 13px; line-height: 1.6;">
                    Այս գործիքի միջոցով Գործարքների քարտում ավելանում է «Ապրանքների հասանելիություն» բաժինը, որպեսզի վաճառքի մենեջերները կարողանան տեղում տեսնել ազատ մնացորդները և ամրագրել ապրանքները:
                </p>

                <?php if ($message): ?>
                    <div style="background: var(--b24-success-bg); border: 1px solid var(--b24-success-border); color: var(--b24-success-dark); padding: 14px 18px; border-radius: var(--b24-radius-xs); margin-bottom: 20px; font-weight: 600;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background: var(--b24-danger-bg); border: 1px solid var(--b24-danger-border); color: var(--b24-danger-dark); padding: 14px 18px; border-radius: var(--b24-radius-xs); margin-bottom: 20px;">
                        <div style="font-weight: 700; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php if ($errorDetails): ?>
                            <div style="font-size: 13px; line-height: 1.6; padding-top: 8px; border-top: 1px solid rgba(0,0,0,0.08); color: var(--b24-text-main);">
                                <?= $errorDetails ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="webhook_url">Մուտքային վեբհուկի հասցե (Inbound Webhook)՝</label>
                        <input type="text" name="webhook_url" id="webhook_url" class="form-control" placeholder="https://portal.bitrix24.ru/rest/1/xxxxxxxxxxxx/">
                        <small style="color: var(--b24-text-muted); font-size: 12px; margin-top: 4px; display: block;">
                            Վեբհուկի պարտադիր իրավունքները՝ <strong>crm</strong> (CRM), <strong>catalog</strong> (Տորգովիյ կատալոգ), <strong>placement</strong> (Վստրաիվանիե պրիլոժենիյ)
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="handler_url">Հավելվածի հասցե (Handler URL)՝</label>
                        <input type="text" name="handler_url" id="handler_url" class="form-control" value="<?= htmlspecialchars($detectedHandler) ?>" required>
                        <small style="color: var(--b24-text-muted); font-size: 12px; margin-top: 4px; display: block;">
                            Ամպային համակարգին միացնելու համար անհրաժեշտ է հանրային HTTPS հասցե (օրինակ՝ ngrok կամ սեփական դոմեն): Տեղական փորձարկման համար կարող եք բացել <a href="deal_card.php?deal_id=1521" target="_blank" style="color: #2066b0; font-weight: 600;">Գործարքի պատուհանի ուղիղ հղումը</a>:
                        </small>
                    </div>

                    <div style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center;">
                        <a href="index.php" class="btn btn-secondary">← Վերադառնալ գլխավոր էջ</a>
                        <button type="submit" class="btn btn-primary">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                            Միացնել համակարգին
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
