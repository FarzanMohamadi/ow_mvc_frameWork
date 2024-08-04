function searchTechnology(url) {
    var technologyS = $('#technologyStatus')[0].value;
    var filter = "?technologyStatus="+technologyS;
    url = url + filter;
    window.location = url;
}