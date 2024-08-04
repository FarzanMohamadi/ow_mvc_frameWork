function searchSubGroup(url) {
    var searchT = $('#searchTitle')[0].value;
    url = url + "?searchTitle="+searchT;
    window.location = url;
}
