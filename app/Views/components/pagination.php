<?php
/**
 * 通用分頁組件
 * 
 * 使用方式：
 * echo view('components/pagination', [
 *     'pager' => $pager,
 *     'baseUrl' => 'user',
 *     'params' => $_GET  // 保留所有搜尋參數
 * ]);
 */

// 建立 URL 參數的函數
function buildPagingUrl($baseUrl, $page, $params = []) {
    // 移除原有的 page 參數，避免重複
    unset($params['page']);
    
    // 加入新的 page 參數
    $params['page'] = $page;
    
    // 過濾空值參數
    $params = array_filter($params, function($value) {
        return $value !== '' && $value !== null;
    });
    
    return $baseUrl . '?' . http_build_query($params);
}

// 取得當前的 GET 參數
$currentParams = $params ?? $_GET ?? [];
?>

<?php if ($pager['totalPages'] > 1): ?>
    <nav aria-label="分頁導航">
        <ul class="pagination justify-content-center">
            <!-- 上一頁 -->
            <li class="page-item <?= $pager['currentPage'] <= 1 ? 'disabled' : '' ?>">
                <?php if ($pager['currentPage'] > 1): ?>
                    <a class="page-link" href="<?= buildPagingUrl($baseUrl, $pager['currentPage'] - 1, $currentParams) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                <?php else: ?>
                    <span class="page-link" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </span>
                <?php endif; ?>
            </li>
            
            <!-- 最首頁 -->
            <?php if ($pager['currentPage'] > 3): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPagingUrl($baseUrl, 1, $currentParams) ?>">1</a>
                </li>
                <?php if ($pager['currentPage'] > 4): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- 頁碼（最多顯示10個） -->
            <?php 
            $maxButtons = 10;
            $startPage = max(1, $pager['currentPage'] - floor($maxButtons / 2));
            $endPage = min($pager['totalPages'], $startPage + $maxButtons - 1);
            
            // 如果結束頁碼小於最大按鈕數，調整開始頁碼
            if ($endPage - $startPage + 1 < $maxButtons) {
                $startPage = max(1, $endPage - $maxButtons + 1);
            }
            ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <?php if ($i == $pager['currentPage']): ?>
                    <li class="page-item active" aria-current="page">
                        <span class="page-link"><?= $i ?></span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= buildPagingUrl($baseUrl, $i, $currentParams) ?>"><?= $i ?></a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>
            
            <!-- 最末頁 -->
            <?php if ($pager['currentPage'] < $pager['totalPages'] - 2): ?>
                <?php if ($pager['currentPage'] < $pager['totalPages'] - 3): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPagingUrl($baseUrl, $pager['totalPages'], $currentParams) ?>"><?= $pager['totalPages'] ?></a>
                </li>
            <?php endif; ?>

            <!-- 下一頁 -->
            <li class="page-item <?= $pager['currentPage'] >= $pager['totalPages'] ? 'disabled' : '' ?>">
                <?php if ($pager['currentPage'] < $pager['totalPages']): ?>
                    <a class="page-link" href="<?= buildPagingUrl($baseUrl, $pager['currentPage'] + 1, $currentParams) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                <?php else: ?>
                    <span class="page-link" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
<?php endif; ?>

 