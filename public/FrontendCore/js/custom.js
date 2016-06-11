$(document).ready(function () {
    $(".cl-item").hover(function () {
        $(this).addClass('cl-item-unfold');
    }, function () {
        $(this).removeClass('cl-item-unfold');
    });
    $(".nav-user-account").hover(function () {
        $(this).addClass('user-account-unfold');
    }, function () {
        $(this).removeClass('user-account-unfold');
    });
    window.onscroll = function () {
        if (document.body.scrollTop > $(document).height() / 100 * 10 || document.documentElement.scrollTop > $(document).height() / 100 * 10) {
            $("#header").addClass("menu-position-fix");
            $("#j-floor-fixed-panel").css({"visibility": "visible", "left": "5%"});
            $(".ui-fixed-panel-go-top").css({"visibility": "visible"});
        } else {
            $("#header").removeClass("menu-position-fix");
            $("#j-floor-fixed-panel").css({"visibility": "hidden", "left": "5%"});
            $(".ui-fixed-panel-go-top").css({"visibility": "hidden"});
        }
    };


    $("body").on("click", ".floor-nav-list li a", function () {
        $(".floor-nav-list li a").removeClass("current");
        $(this).addClass("current");

        var $container = $("html,body");
        var $scrollTo = $(this).attr("href");
        $container.animate({scrollTop: $($scrollTo).offset().top - 100, scrollLeft: 0}, 300);
    });
    $("body").on("click", ".btn-scroll-to-top", function () {
        var $container = $("html,body");
        $container.animate({scrollTop: 0, scrollLeft: 0}, 300);
    });
    $('.ui-banner-slider-container').flexslider({
        animation: "slide",
        prevText: "",
        nextText: ""
    });
    $('.recommend-slider-box').flexslider({
        animation: "slide",
        prevText: "",
        nextText: ""
    });

    // CLick mobile
    $("body").on("click", ".btn-menu-mobile", function () {
       $(".ms-drawer-mask").show();
       $(".ms-drawer").addClass("open");
    });
    $("body").on("click", ".ms-drawer-mask", function () {
       $(".ms-drawer-mask").fadeOut();
       $(".ms-drawer").removeClass("open");
    });
});