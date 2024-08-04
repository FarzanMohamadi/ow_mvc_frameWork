$(document).on("ready", function () {
    TableExport(  $(".export_table")  , {
        formats: ['xlsx'],
        exportButtons: true,
        trimWhitespace: true,
        filename: '_graphy_result'
    });
    $(".export_to_excel").on('click', function (e) {
        e.preventDefault();
        $($("caption.bottom.tableexport-caption button.button-default.xlsx")[0]).click();
        return false;
    });
});