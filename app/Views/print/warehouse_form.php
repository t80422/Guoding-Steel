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

        .signature {
            height: 60px;
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
        <!-- 表頭 -->
        <table class="borderless mb-1">
            <tr>
                <td class="text-end align-top" style="width:80%;">
                    <div class="fw-bold fs-2 me-1">國 鼎 鋼 鐵 股 份 有 限 公 司</div>
                    <div class="fs-4 me-5">
                        請詳細打√&nbsp;&nbsp;
                        進倉庫<input type="checkbox" <?= isset($order['o_type']) && (int)$order['o_type'] === 0 ? 'checked' : '' ?>>&nbsp;&nbsp;
                        出倉庫<input type="checkbox" <?= isset($order['o_type']) && (int)$order['o_type'] === 1 ? 'checked' : '' ?>>
                    </div>
                </td>
                <td class="text-end align-top" style="width:20%;">
                    NO.&nbsp;<?= esc( '') ?><br>
                    大溪&nbsp;03-3802339<br>
                    苗栗&nbsp;037-990009
                </td>
            </tr>
        </table>
        <!-- 基本資料列 -->
        <table class="borderless mb-1">
            <tr>
                <td style="width:33%; text-align:left;">名稱地址：<?= esc($order['from_location_name'] ?? '') ?></td>
                <td style="width:33%; text-align:left;">至 <?= esc($order['to_location_name'] ?? '') ?></td>
                <td style="width:34%; text-align:left;">日期：<?= esc($order['o_date'] ?? '') ?></td>
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
                                    <td><?= esc($cell['qty'] ?? '') ?></td>
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
        <!-- 明細列 -->
        <table class="borderless mb-1">
            <?php
            $detailRows = $details ?? [];
            foreach ($detailRows as $index => $d):
                $label = ($index + 1) . '.'; // 動態產生序號
            ?>
                <tr>
                    <td style="width:5%; text-align:left;"><?= $label ?></td>
                    <td class="dashed" style="width:55%; text-align:left;"><?= esc($d['spec'] ?? '') ?></td>
                    <td class="dashed" style="width:15%;"><?= esc($d['unit'] ?? '') ?></td>
                    <td class="dashed" style="width:15%;"><?= esc($d['qty'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- 配件／氧氣／乙炔 -->
        <table class="borderless mb-1">
            <tr>
                <td style="width:20%; text-align:left;">配件：</td>
                <td class="dashed"></td>
            </tr>
            <tr>
                <td style="text-align:left;">氧氣：<?= esc($order['o_oxygen'] ?? '') ?></td>
                <td class="dashed"></td>
            </tr>
            <tr>
                <td style="text-align:left;">乙炔：<?= esc($order['o_acetylene'] ?? '') ?></td>
                <td class="dashed"></td>
            </tr>
        </table>

        <!-- GPS & 時間 -->
        <table class="borderless mb-1">
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
                    車號：<?= esc($order['o_car_number'] ?? '') ?><br>
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