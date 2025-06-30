//todo: 拿掉,改成在各自的index.php裡面寫
function search(url) {
    const keyword = document.getElementById('keyword').value.trim();
    const order_date_start = document.getElementById('order_date_start').value;
    const order_date_end = document.getElementById('order_date_end').value;
    const type = document.getElementById('type').value;

    let queryString = '?keyword=' + encodeURIComponent(keyword);

    if (order_date_start) {
        queryString += '&order_date_start=' + encodeURIComponent(order_date_start);
    }
    if (order_date_end) {
        queryString += '&order_date_end=' + encodeURIComponent(order_date_end);
    }
    if (type !== '') {
        queryString += '&type=' + encodeURIComponent(type);
    }
    location.href = url + queryString;
}

function confirmDelete(url) {
    if (confirm('確定要刪除這筆資料嗎？')) {
        location.href = url;
    }
}