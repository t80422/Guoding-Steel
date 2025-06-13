function search(url) {
    const keyword = document.getElementById('keyword').value.trim();
    location.href = url + '?keyword=' + encodeURIComponent(keyword);
}

function confirmDelete(url) {
    if (confirm('確定要刪除這筆資料嗎？')) {
        location.href = url;
    }
}