
OW.bind('frmclamav.verified.file.view', function(data) {
    for (i = 0; i < $('.ow_file_attachment_preview .ow_file_attachment_info').length; i++) {
        if($($($('.ow_file_attachment_preview .ow_file_attachment_info')[i]).find('.virus_file_condition')).size()==0){
            $($('.ow_file_attachment_preview .ow_file_attachment_info ')[i]).append('<span class="virus_file_condition verified">' + OW.getLanguageText('frmclamav', 'file_is_clean') + '</span>');
        }
    }
});



OW.bind('frmclamav.virus.file.view', function(data) {
    if(!data)
    {
        return;
    }
    var VirusNames = data.data.virusNames;
    if ($(VirusNames).size() == 1) {
        var VirusNameParent = $("span:contains("+VirusNames+")").parent().parent().parent();
        VirusNameParent.css('background-color','#ffe1e1');
        $(VirusNameParent.find('.ow_file_attachment_preload')).css('display','none');
        $(VirusNameParent.find('.virus_file_condition')).remove();
        $(VirusNameParent.find('.ow_file_attachment_info')).append('<span  class="virus_file_condition virus">'+OW.getLanguageText('frmclamav', 'virus_is_detected')+'</span>');
    }
    if ($(VirusNames).size() > 1) {
        for (i = 0; i < $(VirusNames).size(); i++) {
            var VirusNameParent = $("span:contains("+VirusNames[i]+")").parent().parent().parent();
            VirusNameParent.css('background-color','#ffe1e1');
            $(VirusNameParent.find('.ow_file_attachment_preload')).css('display','none');
            $(VirusNameParent.find('.virus_file_condition')).remove();
            $(VirusNameParent.find('.ow_file_attachment_info')).append('<span  class="virus_file_condition virus">'+OW.getLanguageText('frmclamav', 'virus_is_detected')+'</span>');
        }
    }
});