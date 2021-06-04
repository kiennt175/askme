$(document).ready(function () {
    $('#tag-search').on('keyup', function (e) {
        e.preventDefault()
        if ((e.key === 'Enter' || e.keyCode === 13)) {
            if ($('#tag-search').val().trim()) {
                window.location.href = 'http://localhost:8000/tags/search/' + $('#tag-search').val().trim() + '/popular#tab-top';
            } else {
                window.location.href = 'http://localhost:8000/tags/view/popular'
            } 
        }
    });
});
