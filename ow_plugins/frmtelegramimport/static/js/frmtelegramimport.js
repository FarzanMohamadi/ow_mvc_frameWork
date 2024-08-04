var importComponent;
var uploadComponent;
var helpComponent;

function showImportForm($groupId){
    importComponent = OW.ajaxFloatBox('FRMTELEGRAMIMPORT_CMP_DataImportFloatBox', {iconClass: 'ow_ic_add',groupId: $groupId})
}
function showUploadForm($groupId) {
    uploadComponent = OW.ajaxFloatBox('FRMTELEGRAMIMPORT_CMP_FileUploadFloatBox', {iconClass: 'ow_ic_add',groupId: $groupId})
}
function showHelpWindow(){
    helpComponent=OW.ajaxFloatBox('FRMTELEGRAMIMPORT_CMP_HelpFloatBox', {iconClass: 'ow_ic_add'})
}
function closeImportForm(){
    if(importComponent){
        importComponent.close();
    }
}
function closeUploadForm(){
    if(uploadComponent){
        uploadComponent.close();
    }
}
function closeHelpWindow() {
    if(helpComponent){
        helpComponent.close();
    }
}

