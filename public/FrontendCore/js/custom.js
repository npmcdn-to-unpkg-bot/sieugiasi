var loadmore_product_mobile = 1;
$(document).ready(function () {
    if ($('#carousel').length != 0) {
        $('#carousel').flexslider({
            animation: "slide",
            controlNav: false,
            directionNav: false,
            animationLoop: false,
            slideshow: false,
            itemWidth: 210,
            // itemMargin: 5,
            asNavFor: '#slider',
            direction: 'vertical'
        });
    }
    if ($('#slider').length != 0) {
        $('#slider').flexslider({
            animation: "fade",
            animationLoop: false,
            slideshow: false,
            controlNav: false,
            touch: true,
            sync: "#carousel"
        });
    }
    if ($('#datepicker').length != 0) {
        $("#datepicker").datepicker({
            // changeMonth: true,
            // changeYear: true,
            // yearRange: '1990:2016',
            // dateFormat: 'dd/mm/yy'
        });
    }
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
    $(".bc-cate-has-child").hover(function () {
        $(this).addClass('bc-cate-child-active');
    }, function () {
        $(this).removeClass('bc-cate-child-active');
    });
    window.onscroll = function () {
        if (document.body.scrollTop > $(document).height() / 100 * 10 || document.documentElement.scrollTop > $(document).height() / 100 * 10) {
            $("#header").addClass("menu-position-fix");
            $("#j-floor-fixed-panel").css({"visibility": "visible", "left": "5%"});
            $(".ui-fixed-panel-go-top").css({"visibility": "visible"});
            if ($(".broad-category-fixed-nav").lenght != 0) {
                $(".broad-category-fixed-nav").show();
                $(".broad-category-fixed-nav").css("top", "85px");
            }
        } else {
            $("#header").removeClass("menu-position-fix");
            $("#j-floor-fixed-panel").css({"visibility": "hidden", "left": "5%"});
            $(".ui-fixed-panel-go-top").css({"visibility": "hidden"});
            if ($(".broad-category-fixed-nav").lenght != 0) {
                $(".broad-category-fixed-nav").hide();
                $(".broad-category-fixed-nav").css("top", "0");
            }
        }
    };


    $("body").on("click", ".floor-nav-list li a", function () {
        $(".floor-nav-list li a").removeClass("current");
        $(this).addClass("current");

        var $container = $("html,body");
        var $scrollTo = $(this).attr("href");
        $container.animate({scrollTop: $($scrollTo).offset().top - 100, scrollLeft: 0}, 300);
    });
    $("body").on("click", ".bc-fixed-nav-available ", function () {
        $(".bc-fixed-nav-available ").removeClass("bc-fixed-nav-active");
        $(this).addClass("bc-fixed-nav-active");

        var $container = $("html,body");
        var $scrollTo = $(this).attr("data-rel");
        $container.animate({scrollTop: $($scrollTo).offset().top - 125, scrollLeft: 0}, 300);
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
    $("body").on("change", ".province_user", function () {
        var id = $(this).val();
        changeProvince(id, "districtOfUser");
    });
    $(".label-checkout").click(function () {

        $("#accordian-checkout li").removeClass("active");
        $(".sub-checkout").hide();
        $(this).next().slideDown();
        $(this).parent("li").addClass("active");
    });
    $("body").on("click", "#load-more-product-mobile", function () {
        $(".md-loading").show();
        $.ajax({
            type: "POST",
            url: rootUrl + 'index/loadmore-product',
            data: {pagination: loadmore_product_mobile},
            success: function (response) {
                $(".md-loading").hide();
                $(".list-product-mobile").append(response);
                if (response == '') {
                    $("#load-more-product-mobile").remove();
                }
                loadmore_product_mobile++;
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    });
    $("body").on("click", ".refine-title .filter,.refine-filtered-pwa ", function () {
        $("#j-refine-wrapper").show();
        $(".ms-rc-ripple").removeClass("refine-active");
        $(".refine-title .filter").addClass("refine-active");
        $(".refine-main-tab").removeClass("animated fadeInRight").hide();
        $(".filter-filed").addClass("animated fadeInRight").show();
    });
    $("body").on("click", ".refine-title .attribute,.search-attr", function () {
        $("#j-refine-wrapper").show();
        $(".ms-rc-ripple").removeClass("refine-active");
        $(".refine-title .attribute").addClass("refine-active");
        $(".filter-filed").removeClass("animated fadeInRight").hide();
        $(".refine-main-tab").addClass("animated fadeInRight").show();
    });
    $("body").on("click", ".search-remind .fa-close", function () {
        $("#j-refine-wrapper").hide();
    });
    $("body").on("change", ".m-filter-category", function () {
        var url = $(".m-filter-category option:selected").attr("data-url");
        window.location.href = url;
    });
    $("body").on("change", ".m-filter-size", function () {
        var size = $(".m-filter-size option:selected").val();
        filterCategoryProduct('size',size,$(this));
    });
    $("body").on("change", ".m-filter-color", function () {
        var color = $(".m-filter-color option:selected").val();
        filterCategoryProduct('color',color,$(this));
    });
    $("body").on("click", ".m-search-mobile", function () {
        $(".search-field").slideToggle();
    })


});
function filterTodaySale($cate, $value, $this) {
    $(".filter-todaysale").removeClass('current');
    $this.addClass("current");
    $.ajax({
        type: "POST",
        url: rootUrl + 'today-sale/search',
        data: {cate: $cate, value: $value},
        success: function (response) {
            $("#filter-today-sale").html(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });
}