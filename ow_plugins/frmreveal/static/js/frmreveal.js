var html = '<div class="curtain" id="curtain"><div class="curtain__wrapper"><input type="checkbox" id="check1" checked  onclick="disableCheckbox(this, 1);"><div class="curtain__panel curtain__panel--left"></div> <!-- curtain__panel --><div class="curtain__content"><div class="curtain2" id="curtain2"><div class="curtain__wrapper2"><input type="checkbox"  id="check2" checked  onclick="disableCheckbox(this, 2);"><div class="curtain__panel2 curtain__panel--left2"></div> <!-- curtain__panel --><div class="curtain__content2"></div><div class="curtain__panel2 curtain__panel--right2"></div> <!-- curtain__panel --></div></div></div><div class="curtain__panel curtain__panel--right"></div> <!-- curtain__panel --></div></div>';
$('body').append(html);

function disableCheckbox(node, number){
    if(node.getAttribute('justCreated'))
    {
        if(number==1){
            $('.curtain__panel.curtain__panel--left').css('transform','')
            $('.curtain__panel.curtain__panel--right').css('transform','')
            $('.curtain__panel.curtain__panel--left').css('-webkit-transform','');
            $('.curtain__panel.curtain__panel--right').css('-webkit-transform','');
        }
        node.remove();
        if(number==2){
            $('.curtain').fadeOut(2000);
        }
    }
    else {
        node.parentNode.removeChild(node);
        /*    if(number==1){
         $('.curtain').fadeOut(2000);
         }*/
        if (number == 2) {
            $('.curtain').fadeOut(2000);
        }
    }
}

function reveal(){
    var check1 = document.getElementById('check1');
    var check2 = document.getElementById('check2');
    if(!check1 || !check1.checked){
        var justcreated1 = true;
        if(!check1) {
            $('.curtain__wrapper').prepend('<input type="checkbox" id="check1" onclick="disableCheckbox(this, 1);" checked/>')
            $('.curtain').fadeIn(2000);
            check1 = document.getElementById('check1');
            check1.setAttribute('justCreated',true);
            $('.curtain__panel.curtain__panel--left').css('transform','inherit');
            $('.curtain__panel.curtain__panel--right').css('transform','inherit');
        }
    }
    if(!check2 || !check2.checked){
        if(!check2) {
            $('.curtain__wrapper2').prepend('<input type="checkbox" id="check2" onclick="disableCheckbox(this, 2);" checked/>')
            $('.curtain2').fadeIn(2000);
            check2 = document.getElementById('check2');
            check2.setAttribute('justCreated',true);
            $('.curtain__panel.curtain__panel--left2').css('transform','inherit');
            $('.curtain__panel.curtain__panel--right2').css('transform','inherit');
        }
    }
}

document.addEventListener("mousedown", function (e){
    if(e.which==2){
        e.preventDefault();
        reveal();
    }
})

document.addEventListener("keydown", function(event) {
    if (event.keyCode == 27) { // Esc key maps to keycode `19`
        reveal();
    }
})
/*$(document).keyup(function(e) {
 if (e.keyCode == 27) { // escape key maps to keycode `27`
 if($('#curtain').isFading()){
 $(curtain).fadeIn(2000);
 }
 if($('#curtain2').isFading()){
 $(curtain2).fadeIn(2000);
 }
 }
 });*/
function sendparams() {
    document.body.style.overflow = "hidden";
}

function checkCurtainVisibility(){
    return this.css('opacity') < 1;
};
$('#curtain2').isFading = function(){
    return this.css('opacity') < 1;
};