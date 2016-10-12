function registerController(name, controller) {
    angular.module('pineapple').controllerProvider.register(name, controller);
}

function resizeModuleContent() {
    var offset = 50;
    var height = ((window.innerHeight > 0) ? window.innerHeight : screen.height) - 1;
    height = height - offset;
    if (height < 1) height = 1;
    if (height > offset) {
        $(".module-content").css("min-height", (height) + "px");
    }
}

function collapseNavBar() {
    width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    if (width < 768) {
        $('div.navbar-collapse').removeClass('in');
    } else {
        $('div.navbar-collapse').addClass('in');
    }
}

function selectElement(elem) {
    var selectRange = document.createRange();
    selectRange.selectNodeContents(elem);
    var selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(selectRange);
}

$('html').click(function(e){
    var elem = e.toElement;
    if (elem !== undefined && elem.classList.contains('autoselect')) {
        selectElement(elem);
    }
});

$(window).resize(function() {
    resizeModuleContent();
});