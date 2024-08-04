$(document).on('ready', function () {
    $(document).on("click", ".faq_question", function (e) {
        var that =  $(e.target);
        if (!$(that).hasClass("active")) {
            $(".faq_question.active").removeClass("active").siblings(".faq_answer").hide(100);
            $(that).addClass("active").siblings(".faq_answer").show(100);
            $(".faq_question_toggle").text("+");
            $(that).find(".faq_question_toggle").text("");
        }
    });
    $($(".faq_answer")[0]).first().show();
});