var slidesToShow = 3;

$(window).on('resize', function(){
  var d_w = $(document).width();

  var ratio = window.devicePixelRatio || 1;
  var screenwXr = screen.width * ratio;
  if(d_w>screenwXr)
    d_w = screenwXr;
  //console.log("w="+d_w.toString());

  slidesToShow = 3;
  if(d_w<1200)
    slidesToShow = 2;
  if(d_w<600)
    slidesToShow = 1;
  $(".frmslideshow_regular").slick('slickSetOption',"slidesToShow",slidesToShow,true);
});

$(document).on('ready', function() {
  var d_w = $(document).width();

  var ratio = window.devicePixelRatio || 1;
  var screenwXr = screen.width * ratio;
  if(d_w>screenwXr)
    d_w = screenwXr;
  //console.log("w="+d_w.toString());

  slidesToShow = 3;
  if(d_w<1200)
    slidesToShow = 2;
  if(d_w<600)
    slidesToShow = 1;

  $(".frmslideshow_regular").slick({
    dots: true,
    arrows: false,
    infinite: true,
    centerMode: true,
    slidesToShow: slidesToShow,
    slidesToScroll: 1,
    autoplay: false,
    autoplaySpeed: 3000,
    //variableWidth: true,
    //adaptiveHeight: true,
  });
});