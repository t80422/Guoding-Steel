function search(url) {
    const keyword = document.getElementById('keyword').value.trim();
    const type = document.getElementById('type').value;

    let queryString = '?keyword=' + encodeURIComponent(keyword);
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