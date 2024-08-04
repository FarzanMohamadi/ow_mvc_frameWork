var uploadFileFormComponent;
var customizeCategoryFormComponent;
var manageCompany;
var categoryForm;
var accessFileComponent;
var accessCategoryComponent;
var categoryFilter;

$(document).ready(function() {
    $('form[name="manage_file"]').submit(function(e) {
        if($('input[name="file"]').val()) {
            e.preventDefault();
            $('#progress-div').show();
            $(this).ajaxSubmit({
                forceSync: true,
                target:   '#targetLayer',
                beforeSubmit: function() {
                    $("#progress-bar").width('0%');
                },
                uploadProgress: function (event, position, total, percentComplete){
                    $("#progress-bar").width(percentComplete + '%');
                    $("#progress-bar").html('<div id="progress-status">' + percentComplete +' %</div>')
                },
                success:function (){
                    closeUploadFileForm();
                    $('#loader-icon').hide();
                    setTimeout(function() {
                        window.location = window.location;
                    }, 1000);
                },
                resetForm: false
            });
            return false;
        }
    });
});

function handleFilterForm(url){
    $('form[name="files_filter"]').submit(function(e) {
        e.preventDefault();
        if ($('select[name=category]').length > 0) {
            categoryId = $('select[name=category]').val();
        } else if($('.special_category_wrapper.active').length > 0) {
            categoryId = $('.special_category_wrapper.active').attr('id');
            categoryId = categoryId.substr(17, categoryId.length);
        }
        holding = $('select[name=holding]').val();
        fromMonth = $('select[name=month_start_date]').val();
        fromYear = $('select[name=year_start_date]').val();
        toMonth = $('select[name=month_end_date]').val();
        toYear = $('select[name=year_end_date]').val();
        subCompany = $('input[name=sub_company]')[0].checked;
        window.location = url + "?categoryId=" + categoryId + "&holding=" + holding + "&fromMonth=" + fromMonth + "&fromYear=" + fromYear + "&toMonth=" + toMonth + "&toYear=" + toYear + "&sub_company=" + subCompany;
    });
}

function showCategoryForm($categoryId,title){
    categoryForm = OW.ajaxFloatBox('FRMSHASTA_CMP_CategoryFloatBox',{categoryId:$categoryId},{
        width: '400px',
        title: title
    });
}
function showUploadFileForm($fileId){
    uploadFileFormComponent = OW.ajaxFloatBox('FRMSHASTA_CMP_FileUploadFloatBox',{fileId:$fileId});
}

function showCompanyForm(compId){
    manageCompany = OW.ajaxFloatBox('FRMSHASTA_CMP_ManageCompanyFloatBox', {id: compId});
}

function closeCompanyForm(){
    if(manageCompany){
        manageCompany.close();
    }
}

function showCustomizeCategoryForm(title){
    customizeCategoryFormComponent = OW.ajaxFloatBox('FRMSHASTA_CMP_CustomizeCategoriesFloatbox', [], {
        width: '400px',
        title: title
    } );
}

function closeUploadFileForm(){
    if(uploadFileFormComponent){
        uploadFileFormComponent.close();
    }
}

function showManageSpecialCategoryForm(title){
    manageSpecialCategory = OW.ajaxFloatBox('FRMSHASTA_CMP_CustomizeSpecialFloatbox', [], {
        width: '400px',
        title: title
    });
}

function closeManageSpecialCategoryForm(){
    if(manageSpecialCategory){
        manageSpecialCategory.close();
    }
}

function closeCustomizeCategoryForm(){
    if(customizeCategoryFormComponent){
        customizeCategoryFormComponent.close();
        window.location = window.location;
    }
}

function hasCategoryMonthFilter(catId) {
    if (categoryFilter) {
        for( var prop in categoryFilter ){
            if (prop == catId) {
                if (categoryFilter[prop].month != "1") {
                    return false;
                }
                return true;
            }
        }
    }
    return true;
}

function hasCategoryYearFilter(catId) {
    if (categoryFilter) {
        for( var prop in categoryFilter ){
            if (prop == catId) {
                if (categoryFilter[prop].year != "1") {
                    return false;
                }
                return true;
            }
        }
    }
    return true;
}

$(window).on('load', function () {
    $('.special_category_wrapper').click(function () {
        $(this).addClass('active');
    });
});

function categoryChanger($id){
    $('.special_category_wrapper').removeClass('active');
    $('#category_wrapper_'+$id).addClass('active');

    if(hasCategoryYearFilter($id)){
        $('.overall_report_container .add_file_header.filter_form select[name="year_start_date"]').css('display','inline-block');
        $('.overall_report_container .add_file_header.filter_form select[name="year_end_date"]').css('display','inline-block');
    }else{
        $('.overall_report_container .add_file_header.filter_form select[name="year_start_date"]').css('display','none');
        $('.overall_report_container .add_file_header.filter_form select[name="year_end_date"]').css('display','none');
    }

    if(hasCategoryMonthFilter($id)){
        $('.overall_report_container .add_file_header.filter_form select[name="month_start_date"]').css('display','inline-block');
        $('.overall_report_container .add_file_header.filter_form select[name="month_end_date"]').css('display','inline-block');
    }else{
        $('.overall_report_container .add_file_header.filter_form select[name="month_start_date"]').css('display','none');
        $('.overall_report_container .add_file_header.filter_form select[name="month_end_date"]').css('display','none');
    }

    $('.special_category_data_wrapper').css('display','none');
    $('#category_data_'+$id).css('display','block');
}

function showManageAccessFileForm($fileId,title){
    accessFileComponent = OW.ajaxFloatBox('FRMSHASTA_CMP_ManageAccessFileFloatBox', {fileId: $fileId},{
        width: '400px',
        title: title
    });
}
var manageAccessSelect = function( list, fileId,respondUrl){
    this.list = list;
    this.fileId = fileId;
    this.respondUrl = respondUrl;
    this.resultList = [];
}

manageAccessSelect.prototype = {
    init: function(){
        var self = this;
        $.each( this.list,
            function(index, data){
                var ei = self.findIndex(data.entityId);
                if(data.selected) {
                    if (ei == null) {
                        self.resultList.push(data.entityId);
                        $(this).addClass('ow_mild_green');
                    }
                }
                $('#'+data.linkId).click(
                    function(){
                        var ei = self.findIndex(data.entityId);
                        if( ei == null ){
                            self.resultList.push(data.entityId);
                            $(this).addClass('ow_mild_green');
                        }else{
                            self.resultList.splice(ei, 1);
                            $(this).removeClass('ow_mild_green');
                        }
                    }
                );
            }
        );

        $('input.submit',this.$context).click(function(){
            self.submit();
        });
    },

    findIndex: function( value ){

        for( var i = 0; i < this.resultList.length; i++){
            if( value == this.resultList[i] ){
                return i;
            }
        }
        return null;
    },

    reset: function(){
        $('a.selected', this.$context).removeClass('selected');
        this.resultList = [];
    },

    submit: function(){
        var underSelf=this;

        selectedCount=underSelf.resultList.length;
        accessFileComponent.close();

        $.ajax({
            type: 'POST',
            url: underSelf.respondUrl,
            data: {"sendIdList": JSON.stringify(underSelf.resultList),"fileId":underSelf.fileId},
            dataType: 'json',
            complete: function (data) {
                if( data.status==200 )
                {
                    OW.info(OW.getLanguageText('frmshasta', 'manage_success_message'));
                }
                else
                {
                    OW.error(OW.getLanguageText('frmshasta', 'error_in_manage'));
                }
            }
        });
    }
}



function showManageAccessCategoryForm($categoryId,title){
    accessCategoryComponent = OW.ajaxFloatBox('FRMSHASTA_CMP_ManageAccessCategoryFloatBox', {categoryId: $categoryId},{
        width: '400px',
        title: title
    });
}


var manageCategoryAccessSelect = function( list, categoryId,respondUrl){
    this.list = list;
    this.categoryId = categoryId;
    this.respondUrl = respondUrl;
    this.resultList = [];
}

manageCategoryAccessSelect.prototype = {
    init: function(){
        var self = this;
        $.each( this.list,
            function(index, data){
                var ei = self.findIndex(data.entityId);
                if(data.selected) {
                    if (ei == null) {
                        self.resultList.push(data.entityId);
                        $(this).addClass('ow_mild_green');
                    }
                }
                $('#'+data.linkId).click(
                    function(){
                        var ei = self.findIndex(data.entityId);
                        if( ei == null ){
                            self.resultList.push(data.entityId);
                            $(this).addClass('ow_mild_green');
                        }else{
                            self.resultList.splice(ei, 1);
                            $(this).removeClass('ow_mild_green');
                        }
                    }
                );
            }
        );

        $('input.submit',this.$context).click(function(){
            self.submit();
        });
    },

    findIndex: function( value ){

        for( var i = 0; i < this.resultList.length; i++){
            if( value == this.resultList[i] ){
                return i;
            }
        }
        return null;
    },

    reset: function(){
        $('a.selected', this.$context).removeClass('selected');
        this.resultList = [];
    },

    submit: function(){
        var underSelf=this;

        selectedCount=underSelf.resultList.length;
        accessCategoryComponent.close();

        $.ajax({
            type: 'POST',
            url: underSelf.respondUrl,
            data: {"sendIdList": JSON.stringify(underSelf.resultList),"categoryId":underSelf.categoryId},
            dataType: 'json',
            complete: function (data) {
                if( data.status==200 )
                {
                    OW.info(OW.getLanguageText('frmshasta', 'manage_success_message'));
                }
                else
                {
                    OW.error(OW.getLanguageText('frmshasta', 'error_in_manage'));
                }
            }
        });
    }
}