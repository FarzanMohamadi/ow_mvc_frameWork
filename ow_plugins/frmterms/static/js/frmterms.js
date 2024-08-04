var termsComponent;

function showTermsFormComponent(sectionId){
    termsComponent = OW.ajaxFloatBox('FRMTERMS_CMP_TermsFloatBox',  { params :{sectionId: sectionId}}, {width:700, iconClass: 'ow_ic_add'});
}

function showMobileTermsFormComponent(sectionId){
    termsComponent = OWM.ajaxFloatBox('FRMTERMS_MCMP_TermsFloatBox',  { params :{sectionId: sectionId}}, {width:700, iconClass: 'ow_ic_add'});
}

function closeTermsFormComponent(){
    if(termsComponent){
        termsComponent.close();
    }
}

