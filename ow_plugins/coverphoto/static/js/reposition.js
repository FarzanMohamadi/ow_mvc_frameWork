let finalPosition = 0;
let firstStyle = 0;
let enabled = true;

$(document).ready(function () {
    window.addEventListener("resize", function () {
        let coverResizeWrapper = $('.cover-resize-wrapper');
        if(!coverResizeWrapper.hidden){
            coverResizeWrapper.height($('.cover-wrapper').height());
        }
    })
});
function repositionCover() {
    let coverWrapper = $('.cover-wrapper');
    let coverResizeWrapper = $('.cover-resize-wrapper');
    coverWrapper.hide();
    coverResizeWrapper.show();
    coverResizeWrapper.height(coverWrapper.height());
    let dragItem = $('.cover-resize-wrapper img');
    let image = dragItem[0];
    $('.cover-resize-buttons').show();
    $('.drag-div').show();
    $('.default-buttons').hide();
    $('.screen-width').val(coverResizeWrapper.width());

    let active = false;
    let initialY;
    firstStyle = image.style;
    enabled = true;
    let yOffset = 0;

    dragItem.on('touchstart', dragStart);
    $(document).on('touchend', dragEnd);
    $(document).on('touchmove', drag);

    dragItem.on('mousedown',dragStart);
    $(document).on('mouseup', dragEnd);
    $(document).on('mousemove', drag);

    function dragStart(e) {
        if (e.type === "touchstart") {
            initialY = e.touches[0].clientY - yOffset;
        } else {
            initialY = e.clientY - yOffset;
        }
        if (e.target === image) {
            active = true;
        }
    }

    function dragEnd(e) {
        initialY = yOffset;
        active = false;
        finalPosition = yOffset;
    }

    function drag(e) {
        if (active && enabled) {
            let currentY;
            e.preventDefault();
            if (e.type === "touchmove") {
                currentY = e.touches[0].clientY - initialY;
            } else {
                currentY = e.clientY - initialY;
            }
            if(currentY <= 0 && coverResizeWrapper.height() - currentY <= image.height){
                yOffset = currentY;
                setTranslate(currentY, image);
            }
        }
    }
}

function setTranslate(yPos, el) {
    el.style.transform = "translate3d(" + 0 + "px, " + yPos + "px, 0)";
}

function saveReposition() {
    if ($('input.cover-position').length == 1) {
        $('input.cover-position').val(finalPosition);
        $('input.cover-photo-height').val($('.cover-container').height());
        $('form.cover-position-form').submit();
    }
}

function cancelReposition() {
    $('.cover-wrapper').show();
    $('.cover-resize-wrapper').hide();
    $('.cover-resize-buttons').hide();
    $('.default-buttons').show();
    $('input.cover-position').val(0);
    $('.cover-resize-wrapper img')[0].style = firstStyle;
    enabled = false;
}