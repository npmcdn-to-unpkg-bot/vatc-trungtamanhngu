// Agency Theme JavaScript
var loadmore = 1;
(function ($) {
    "use strict"; // Start of use strict

    // jQuery for page scrolling feature - requires jQuery Easing plugin
    $('a.page-scroll').bind('click', function (event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top )
        }, 1250, 'easeInOutExpo');
        event.preventDefault();
    });

    // Highlight the top nav as scrolling occurs
    $('body').scrollspy({
        target: '.navbar-fixed-top',
        offset: 51
    });

    // Closes the Responsive Menu on Menu Item Click
    $('.navbar-collapse ul li a:not(.dropdown-toggle)').click(function () {
        $('.navbar-toggle:visible').click();
    });

    // Offset for Main Navigation
    $('#mainNav').affix({
        offset: {
            top: 100
        }
    })
    $('.carousel').carousel({
        interval: 5000 //changes the speed
    });
    $('select').select2();
    $("#slider1").revolution({
        sliderType: "standard",
        sliderLayout: "fullscreen",
        delay: 9000,
        onHoverStop: 'on',
        navigation: {
            onHoverStop: 'on',
            touch: {
                touchenabled: "on",
                swipe_treshold: 75,
                swipe_min_touches: 1,
                drag_block_vertical: false,
                swipe_direction: "horizontal"
            },
            arrows: {
                style: "",
                enable: true,
                rtl: false,
                hide_onmobile: false,
                hide_onleave: true,
                hide_delay: 200,
                hide_delay_mobile: 1200,
                hide_under: 0,
                hide_over: 9999,
                tmp: '',
                left: {
                    container: "slider",
                    h_align: "left",
                    v_align: "center",
                    h_offset: 20,
                    v_offset: 0
                },
                right: {
                    container: "slider",
                    h_align: "right",
                    v_align: "center",
                    h_offset: 20,
                    v_offset: 0
                }
            }
        }
    });
})(jQuery); // End of use strict
$(document).ready(function () {
    $("body").on("click", ".navbar-toggle", function () {
        $("#menu-mobile").toggleClass("is-visible");
    })
    $("body").on("click", "#menu-mobile ul li a", function () {
        var id = $(this).attr("data-rel");
        $("html, body").animate({scrollTop: $(id).offset().top}, 3000);
        $("#menu-mobile").removeClass("is-visible");
    })
    $('.modal').on('hidden.bs.modal', function (e) {
        $("label[for='message_error']").text('');
    });
    $(".btn-playgame").click(function () {
        var hlv_id = $(this).attr("data-rel");
        $("#popupPlay").modal();
        $("#popupPlay").find("input[name='hlv_id']").val(hlv_id);
    });
    $("#kol-pagination").jPages({
        containerID: "gallery-posts",
        perPage: 6,
        first: false,
        previous: "←",
        next: "→",
        last: false
    });
    $("#datepicker").datepicker();
    $("body").on("click", ".btn-search-kol", function () {
        showLoading();
        var data = $("#filter-kol").serialize();
        $.ajax({
            type: "POST",
            url: rootUrl + 'search',
            data: data,
            success: function (response) {
                hideLoading();
                $("#gallery-posts").html(response);
                $("#kol-pagination").jPages({
                    containerID: "gallery-posts",
                    perPage: 6,
                    first: false,
                    previous: "←",
                    next: "→",
                    last: false
                });
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    })
    $("body").on("click", ".btn-loadmore-blogs", function () {
        showLoading();
        $.ajax({
            type: "POST",
            url: rootUrl + 'blogs/loadmore',
            data: {page: loadmore},
            success: function (response) {
                hideLoading();
                loadmore++;
                $(".blogs-list-area").append(response);
                if (!response) {
                    $(this).remove();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    })
});
function userPosts() {

    var forgotvalidator = $("#postForm").validate();
    if (forgotvalidator.form()) {
        //showLoading();
        var data = $("#postForm").serialize();
        sharePicture=$(".image-share").attr("src");
        shareDescription=$("textarea[name='p_description']").val();
        callFBShare();
        $.ajax({
            type: "POST",
            url: rootUrl + 'posts',
            data: data,
            success: function (response) {
                hideLoading();
                var result = $.parseJSON(response);
                if (result.status == 1) {
                    //shareDescription = result.description;
                    //sharePicture = result.image;
                    //callFBShare();
                } else {
                    var error = result.message;
                    showMessageWhenWrong(error);
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    }
}
function showLoading(){
    $(".loadding-area").show();
}
function hideLoading(){
    $(".loadding-area").hide();
}