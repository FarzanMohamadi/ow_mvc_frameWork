/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */

function frmgt_addAttributes(items){
    var i = 0;
    while(i < items.length){
        var myPlacement;
        if(items[i].hasOwnProperty('placement')){
            myPlacement = items[i].placement;
        }
        if(items[i].address){
            if(!$(items[i].address).is(":visible")){
                i++;
                continue;
            }
        }
        frmgt_tour.addStep({
            element: items[i].address,
            content: items[i].description,
            placement: myPlacement,
            title: OW.getLanguageText('frmguidedtour', 'guide_title'),
            onShown: function (tour) {
                // $("div.tour-backdrop").click(function(){
                //     frmgt_cookie_setSeen()
                //     frmgt_tour.end();
                // });
                $(document).keyup(function(e) {
                    if (e.keyCode == 27) { // escape key
                        frmgt_cookie_setSeen()
                        frmgt_tour.end();
                    }
                });
            },
        });
        i++;
    }
}

function frmgt_initializeAndSetOptions(nextPageAddr, previousPageAddr) {
    var buttonNextDef, buttonPreviousDef;
    var endButtonText = 'button_skip';

    if (nextPageAddr){
        buttonNextDef = "<button class='btn btn-warning btn-tour-next-page' data-role='end' onclick='update_seen_status_and_load_page(1, \"" + nextPageAddr + "\")'>" +
            OW.getLanguageText('frmguidedtour', 'button_nextPage') + "</button>";
    }
    else{
        buttonNextDef = '';
        endButtonText = 'end_guided_tour';
    }
    if (previousPageAddr){
        buttonPreviousDef = "<button class='btn btn-warning btn-tour-next-page' data-role='end' onclick='update_seen_status_and_load_page(1, \"" + previousPageAddr + "\")'>" +
            OW.getLanguageText('frmguidedtour', 'button_previousPage') + "</button>";
    }
    else{
        buttonPreviousDef = '';
    }
    frmgt_tour = new Tour({
        smartPlacement: true,
        keyboard: true,
        backdrop: true,
        orphan: true,
        storage: false,
        template: "<div class='popover tour'>" +
        "<div class='arrow'></div>" +
        "<button class='btn btn-warning btn-tour-not-now' data-role='end' onclick='frmgt_cookie_setSeen()'>" + "</button>" +
        "<h3 class='popover-title'></h3>" +
        "<div class='popover-content'></div>" +
        "<div class='popover-navigation'>" +
        "<button class='btn btn-info btn-tour-prev' data-role='prev'>" + OW.getLanguageText('frmguidedtour', 'button_prev') + "</button>" +
        "<button class='btn btn-info btn-tour-next' data-role='next'>" + OW.getLanguageText('frmguidedtour', 'button_next') + "</button>" +
        buttonNextDef +
        buttonPreviousDef +
        "<button class='btn btn-warning btn-tour-end' data-role='end' onclick='frmgt_onEndTour()'>" + OW.getLanguageText('frmguidedtour', endButtonText) + "</button>" +
        "</div>" +
        "</div>",

    });
}

function frmgt_createOneStepGuideForIntroduction() {
    window.oneStepTour = new Tour({
        backdrop: true,
        storage: false,
        template: "<div class='popover tour'>" +
        "<div class='arrow'></div>" +
        "<h3 class='popover-title'></h3>" +
        "<div class='popover-content'></div>" +
        "<div class='popover-navigation'>" +
        "<button class='btn btn-warning btn-tour-next-page' onclick= 'afterIntroductionStep()'>" + OW.getLanguageText('frmguidedtour', 'button_activateGuideline') + "</button>" +
        "<button class='btn btn-warning btn-tour-end' data-role='end' onclick= 'frmgt_onEndTour()'>" + OW.getLanguageText('frmguidedtour', 'button_skip') + "</button>" +
        "</div>" +
        "</div>",
        steps: [
            {
                element: 'a.ow_ic_guidedtour.console_item_guidedtour',
                content: OW.getLanguageText('frmguidedtour', 'index_guideLink'),
                placement: 'right',
            },
        ],
    });

    window.oneStepTour.init();
    window.oneStepTour.start();
}

function frmgt_onEndTour(){
    frmgt_tour = undefined;
    frmgt_setSeen();
}

function frmgt_setSeen() {
    $.ajax({
        url: frmgt_ajax_seen_url,
        type: 'post',
        dataType: 'json',
    });
}

function frmgt_isJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function afterIntroductionStep(){
    window.oneStepTour.end();
    if(typeof window.frmgt_data !== 'undefined') {
        frmgt_applyGuide(frmgt_data.jsonString, frmgt_data.nextPageaddr, frmgt_data.previousPageAddr, 1);
    }
}

function frmgt_applyGuide(jsonString, nextPageAddr, previousPageAddr, seenStatus){

    if (frmgt_isJsonString(jsonString)) {
        window.frmgt_data = {};
        frmgt_data["jsonString"] = jsonString;
        frmgt_data["nextPageaddr"] = nextPageAddr;
        frmgt_data["previousPageAddr"] = previousPageAddr;
        if(typeof frmgt_tour !== 'undefined')
        {
            frmgt_tour.end();
            frmgt_tour = undefined;
        }
        if (seenStatus === 0)
            frmgt_createOneStepGuideForIntroduction();
        else {
            frmgt_initializeAndSetOptions(nextPageAddr, previousPageAddr);
            frmgt_addAttributes(JSON.parse(jsonString));
            frmgt_tour.init();
            frmgt_tour.start();
        }
    }else {
        console.log("Error in parsing JSON.");
    }
}

function update_seen_status_and_load_page(status, url) {
    $.ajax({
        url: frmgt_ajax_update_status_url,
        type: 'post',
        dataType: 'json',
        data: {
            status: status,
        },
        success: function()
        {
            location.href = url;
        }
    });
}