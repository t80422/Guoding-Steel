<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
    <meta charset="UTF-8">
    <title>出貨單列印</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* A4 直式紙張大小 */
        body {
            /* 優先使用繁中無襯線字體，貼近範例視覺 */
            font-family: "Microsoft JhengHei", "Noto Sans TC", "PingFang TC", "Heiti TC", "Source Han Sans TC", "PMingLiU", "MingLiU", Arial, sans-serif;
            font-size: 14px;
        }

        .a4 {
            width: 210mm;
            height: 297mm;
            padding: 5mm;
            margin: auto;
            border: 1px solid #000;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        table {
            width: 100% !important;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
        }

        .borderless th,
        .borderless td {
            border: none !important;
        }

        .checkbox-group td {
            border: none;
            padding: 0 4px;
        }

        .dashed {
            border-bottom: 1px dashed #000 !important;
        }

        .underlined-text {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            text-align: center;
            padding-bottom: 2px;
        }

        .signature {
            height: 60px;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .dynamic-details {
            flex-grow: 1;
            min-height: 90px;
        }

        .bottom-fixed {
            margin-top: auto;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }

            .a4 {
                border: none;
                padding: 5mm;
            }

        }
    </style>
</head>

<body>
    <div class="a4">
        <div class="main-content">
            <!-- 表頭 -->
            <table class="borderless mb-1">
                <tr>
                    <td class="align-top" style="width:84%;">
                        <div style="text-align: left; font-weight: bold; font-size: 2.5rem; line-height: 1.2;">
                            <img src="<?= base_url('images/國鼎LOGO.png') ?>" alt="國鼎鋼鐵" 
                                 style="height: 60px; width: auto; margin-right: 12px; vertical-align: middle;">
                            <span style="letter-spacing: 0.35em; vertical-align: middle;">國鼎鋼鐵股份有限公司</span>
                        </div>
                    </td>
                    <td class="text-end align-top" style="width:16%;">
                        NO.&nbsp;<?= esc($order['o_number']) ?><br>
                        大溪&nbsp;03-3802339<br>
                        苗栗&nbsp;037-990009<br>
                    </td>
                </tr>
            </table>
            <!-- 進倉庫/出倉庫選項 - 置中顯示 -->
            <div class="text-center fs-4 mb-2">
                請詳細打√&nbsp;&nbsp;
                進倉庫<input type="checkbox" <?= isset($order['o_type']) && (int)$order['o_type'] === 0 ? 'checked' : '' ?>>&nbsp;&nbsp;
                出倉庫<input type="checkbox" <?= isset($order['o_type']) && (int)$order['o_type'] === 1 ? 'checked' : '' ?>>
            </div>
            <!-- 基本資料列 -->
            <table class="borderless mb-1">
                <tr>
                    <td class="text-center pt-2">
                        名稱地址：<span class="underlined-text fs-5"><?= esc($order['from_location_name'] ?? '') ?></span>
                        <span>至</span>
                        <span class="underlined-text fs-5"><?= esc($order['to_location_name'] ?? '') ?></span>
                    </td>
                    <td class="text-center pt-2">
                        <span class="fs-5">日期：<?= esc($order['o_date'] ?? '') ?></span>
                    </td>
                </tr>
            </table>
            <!-- 材料規格大清單（三組，動態） -->
            <table class="mb-1">
                <thead>
                    <tr>
                        <th>材料規格</th>
                        <th>單位</th>
                        <th>數量</th>
                        <th>材料規格</th>
                        <th>單位</th>
                        <th>數量</th>
                        <th>材料規格</th>
                        <th>單位</th>
                        <th>數量</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($itemsGrid)): ?>
                        <?php foreach ($itemsGrid as $row): ?>
                            <tr>
                                <?php for ($i = 0; $i < 3; $i++): ?>
                                    <?php $cell = $row[$i] ?? null; ?>
                                    <?php if ($cell): ?>
                                        <td><?= esc($cell['name'] ?? '') ?></td>
                                        <td><?= esc($cell['unit'] ?? '') ?></td>
                                        <td class="fw-bold" style="color:red;"><?= esc($cell['qty'] ?? '') ?></td>
                                    <?php else: ?>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <!-- 明細列（序號 + 內容） -->
            <div class="dynamic-details">
                <table class="borderless mb-1">
                    <?php
                    $detailRows = $details ?? [];
                    foreach ($detailRows as $index => $d):
                        $label = ($index + 1) . '.'; // 動態產生序號
                    ?>
                        <tr>
                            <td style="width:5%; text-align:left;color:red;"><?= $label ?></td>
                            <td class="dashed text-break" style="width:95%; text-align:left;color:red;">
                                <?= esc($d['spec'] ?? '') ?><br>
                            <?= esc($d['detail'] ?? '') ?>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- 底部資訊 -->
        <div class="bottom-fixed">
            <!-- 配件／氧氣／乙炔 & GPS & 時間 -->
            <table class="borderless mb-1">
                <tr>
                    <td class="fw-bold fs-6" colspan="3" style="text-align:left;">備註：<span style="color:red;"><?= esc($order['o_remark'] ?? '') ?></span></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="width:33%; text-align:left;">氧氣：<?= esc($order['o_oxygen'] ?? '') ?></td>
                    <td style="width:34%; text-align:left;">乙炔：<?= esc($order['o_acetylene'] ?? '') ?></td>
                    <td style="width:33%; text-align:right;">
                        <span style="font-weight:bold; font-size:1.5em;">總噸數:<?= $totalWeight ?></span>
                    </td>
                </tr>
                <tr>
                    <td style="width:33%; text-align:left;">GPS：<?= esc($order['gps_name'] ?? '') ?></td>
                    <td style="width:33%; text-align:left;">上料時間：<?= esc($order['o_loading_time'] ?? '') ?></td>
                    <td style="width:34%; text-align:left;">下料時間：<?= esc($order['o_unloading_time'] ?? '') ?></td>
                </tr>
            </table>

            <!-- 簽收欄 -->
            <table>
                <tr>
                    <td style="text-align:left; width:25%;">
                        車種:<br>
                        <span class="fw-bold fs-6">車號：<?= esc($order['o_car_number'] ?? '') ?></span><br>
                        司機電話：<?= esc($order['o_driver_phone'] ?? '') ?>
                    </td>
                    <td class="text-center" style="width:25%; vertical-align:top;">
                        <div style="font-weight:bold; margin-bottom:5px;">司機簽收</div>
                        <div class="signature" style="height:50px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">
                            <?php if (!empty($order['o_driver_signature'])): ?>
                                <img src="<?= url_to('OrderController::serveSignature', $order['o_driver_signature']) ?>"
                                    alt="司機簽名" style="max-width:100%; max-height:100%; object-fit:contain;">
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-center" style="width:25%; vertical-align:top;">
                        <div style="font-weight:bold; margin-bottom:5px;">倉庫簽收</div>
                        <div class="signature" style="height:50px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">
                            <?php if (!empty($order['o_from_signature'])): ?>
                                <img src="<?= url_to('OrderController::serveSignature', $order['o_from_signature']) ?>"
                                    alt="倉庫簽名" style="max-width:100%; max-height:100%; object-fit:contain;">
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-center" style="width:25%; vertical-align:top;">
                        <div style="font-weight:bold; margin-bottom:5px;">工地簽收</div>
                        <div class="signature" style="height:50px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">
                            <?php if (!empty($order['o_to_signature'])): ?>
                                <img src="<?= url_to('OrderController::serveSignature', $order['o_to_signature']) ?>"
                                    alt="工地簽名" style="max-width:100%; max-height:100%; object-fit:contain;">
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        // 若帶參數 ?auto=1 則自動開啟列印對話框
        window.addEventListener('load', function() {
            const url = new URL(window.location.href);
            if (url.searchParams.get('auto') === '1') {
                window.print();
            }
        });
    </script>
</body>

</html>