<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta charset="UTF-8">
    <title>倉庫單列印</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* A4 直式紙張大小 */
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 14px;
        }
        .a4 {
            width: 210mm;
            height: 297mm;
            padding: 10mm;
            margin: auto;
            border: 1px solid #000;
            box-sizing: border-box;
        }
        table {
            width: 100% !important;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
        }
        .borderless th, .borderless td {
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
            .no-print { display: none !important; }
            body { margin: 0; }
            .a4 { border: none; }
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
                    進倉庫<input type="checkbox" <?= isset($type) && $type === 'in' ? 'checked' : '' ?>>&nbsp;&nbsp;
                    出倉庫<input type="checkbox" <?= isset($type) && $type === 'out' ? 'checked' : '' ?>>
                </div>
            </td>
            <td class="text-end align-top" style="width:20%;">
                NO.&nbsp;<?= esc($serialNo ?? '') ?><br>
                大溪&nbsp;03-3802339<br>
                苗栗&nbsp;037-990009
            </td>
        </tr>
    </table>

    <!-- 基本資料列 -->
    <table class="borderless mb-1">
        <tr>
            <td style="width:33%; text-align:left;">名稱地址：<?= esc($from ?? '') ?></td>
            <td style="width:33%; text-align:left;">至<?= esc($to ?? '') ?></td>
            <td style="width:34%; text-align:left;">日期：<?= esc($date ?? '') ?></td>
        </tr>
    </table>

    <!-- C 區：材料規格大清單（三組） -->
    <table class="mb-1">
        <thead>
            <tr>
                <th>材料規格</th><th>單位</th><th>數量</th>
                <th>材料規格</th><th>單位</th><th>數量</th>
                <th>材料規格</th><th>單位</th><th>數量</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $staticItems = [
                ['中間樁','支','壓頂板','塊','龍鱗鋼板','片'],
                ['支撐料','支','橋台角鐵','支','不繡鋼板','片'],
                ['國旗','面','精合鋼','支','彎頭板','片'],
                ['短料','支','千斤頂','個','雨棚板','片'],
                ['三角架','支','千斤頂保護蓋','片','組合鋼梯','座'],
                ['大U','支','長模數','組','平台踏板','片'],
                ['小U','支','短模數','組','帆布','捲'],
                ['大小U角鐵','支','鋼管橫桿','支','竹片','排'],
                ['PC連接板','片','安全索','條','支撐','組'],
                ['錨釘','支','母支撐支','根','木板','片'],
                ['手指頭(大)','個','GIP立桿','支','PGB釘＋土壤針','套'],
                ['手指頭(小)','個','経踩鐵蓋','個','油壓頭','台'],
                ['加勁肋','個','CJ叉焊','個','手動加壓機','台'],
                ['龍骨','座','導向活扣','個','操作油','桶'],
                ['',' ','活扣保護蓋','個','鑽鑽','個'],
            ];

            $quantities = $quantities ?? []; // Controller 可傳入對應數量陣列

            foreach ($staticItems as $index => $row):
                $q1 = $quantities[$index][0] ?? '';
                $q2 = $quantities[$index][1] ?? '';
                $q3 = $quantities[$index][2] ?? '';
        ?>
            <tr>
                <td><?= $row[0] ?></td><td><?= $row[1] ?></td><td><?= esc($q1) ?></td>
                <td><?= $row[2] ?></td><td><?= $row[3] ?></td><td><?= esc($q2) ?></td>
                <td><?= $row[4] ?></td><td><?= $row[5] ?></td><td><?= esc($q3) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- D 區：規格 H300~H458 勾選列 -->
    <table class="checkbox-group mb-1">
        <tr>
            <td style="width:10%;">規格<br>請打√</td>
            <?php $specOptions = ['H300','H350','H400','H414','H458'];
            foreach ($specOptions as $s): ?>
                <td style="width:18%;"><input type="checkbox" <?= isset($spec) && $spec === $s ? 'checked' : '' ?>><?= $s ?></td>
            <?php endforeach; ?>
        </tr>
    </table>

    <!-- E 區：(一)(二)(三)(四) 明細列 -->
    <table class="mb-1">
        <?php
            $detailRows = $details ?? [[],[],[],[]];
            $labels = ['(一)','(二)','(三)','(四)'];
            for ($i = 0; $i < 4; $i++):
                $d = $detailRows[$i] ?? ['spec' => '', 'unit' => '', 'qty' => ''];
        ?>
            <tr>
                <td style="width:5%; border:none; text-align:left;"><?= $labels[$i] ?></td>
                <td class="dashed" style="width:55%; text-align:left;"><?= esc($d['spec'] ?? '') ?></td>
                <td class="dashed" style="width:15%;"><?= esc($d['unit'] ?? '') ?></td>
                <td class="dashed" style="width:15%;"><?= esc($d['qty'] ?? '') ?></td>
                <td style="width:10%; border:none;">計&nbsp;支&nbsp;M</td>
            </tr>
        <?php endfor; ?>
    </table>

    <!-- F 區：配件／氧氣／乙炔 -->
    <table class="borderless mb-1">
        <tr>
            <td style="width:20%; text-align:left;">配件：</td>
            <td class="dashed"></td>
        </tr>
        <tr>
            <td style="text-align:left;">氧氣：</td>
            <td class="dashed"></td>
        </tr>
        <tr>
            <td style="text-align:left;">乙炔：</td>
            <td class="dashed"></td>
        </tr>
    </table>

    <!-- G 區：GPS & 時間 -->
    <table class="mb-1">
        <tr>
            <td style="width:33%; text-align:left;">GPS：<?= esc($gps ?? '') ?></td>
            <td style="width:33%; text-align:left;">上料時間：<?= esc($load_time ?? '') ?></td>
            <td style="width:34%; text-align:left;">下料時間：<?= esc($unload_time ?? '') ?></td>
        </tr>
    </table>

    <!-- H 區：車號 -->
    <table class="mb-1">
        <tr><td style="text-align:left;">車號：<?= esc($car_no ?? '') ?></td></tr>
    </table>

    <!-- I 區：簽收欄 -->
    <table>
        <tr>
            <td class="text-center signature">司機簽收</td>
            <td class="text-center signature">倉庫簽收</td>
            <td class="text-center signature">工地簽收</td>
        </tr>
    </table>
</div>

<script>
    // 若帶參數 ?auto=1 則自動開啟列印對話框
    window.addEventListener('load', function () {
        const url = new URL(window.location.href);
        if (url.searchParams.get('auto') === '1') {
            window.print();
        }
    });
</script>
</body>
</html> 