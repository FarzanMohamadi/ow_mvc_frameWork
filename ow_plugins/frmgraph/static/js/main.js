function disable_pie_chart_selection() {
    $(".chart_type_container").css('visibility', 'hidden');
    $("select[name='chartType'] option[value='column']").attr('selected','selected');
}

$(document).on('ready', function () {
    if ($('select[name="chartType"]').find("option:selected").val() == 'pie'){
        $('.highcharts-data-label').each(function(i, label) {
            $(label).find("tspan").attr('y', 0);
        });
    };

    if  ($('select[name="chartField"] option:selected').val() === "users_status_log" ||
        $('select[name="chartField"] option:selected').val().match("^admin_"))
        disable_pie_chart_selection();

    $(document).on('change', 'select[name="chartField"]', function (e) {
        if ($(e.target).find("option:selected").val() == 'users_status_log'){
            disable_pie_chart_selection();
        }
        else if($(e.target).find("option:selected").val().match("^admin_")){
            $(".chart_type_container").css('visibility', 'hidden');
        }
        else{
            $(".chart_type_container").css('visibility', 'visible');
            $("select[name='chartType'] option[value='pie']").show();
        }
    });
});