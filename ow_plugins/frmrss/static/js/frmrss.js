var rssFormComponent;

function showRssFormComponent(){
    rssFormComponent = OW.ajaxFloatBox('FRMRSS_CMP_RssFloatBox', {}, {width:700, iconClass: 'ow_ic_add', title: OW.getLanguageText('frmrss', 'rss_float_box_title')});
}

function closeRssFormComponent(){
    if(rssFormComponent){
        rssFormComponent.close();
    }
}

function createTagRss(homeUrl){
    if(document.getElementsByName('tag')[0].selectedOptions[0].value!="" && document.getElementsByName('tag')[0].selectedOptions[0].value!=null) {
        $(".no_rss_tag").removeClass("no_rss_tag");
        document.getElementById("rssWithTagLink").style.display = "block";
        var url = homeUrl + "news/rss/" + encodeURI(document.getElementsByName('tag')[0].selectedOptions[0].text);
        document.getElementById("rssWithTagLink").innerHTML = "<a id=\"aRssWithTagLink\" target=\"_blank\" href=\"" + url + " \">" + url + "</a>";
    }
}