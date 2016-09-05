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
        sliderLayout: "fullwidth",
        autoHeight: "on",
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
                enable: false,
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
            },
            bullets: {
                enable: true,
                hide_onmobile: true,
                hide_under: 778,
                style: "hermes",
                hide_onleave: false,
                direction: "horizontal",
                h_align: "center",
                v_align: "bottom",
                h_offset: 0,
                v_offset: 20,
                space: 5,
                tmp: ''
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
    //$(".btn-playgame").click(function () {
    //    var hlv_id = $(this).attr("data-rel");
    //    $("#popupPlay-" + hlv_id).modal();
    //    $("#popupPlay-" + hlv_id).find("input[name='hlv_id']").val(hlv_id);
    //});
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
    });
    $('.text-thele-area').slimScroll({
        height: '400px',
        alwaysVisible: true,
        color: '#b51218',
        size: '5px',
        distance: '10px',
    });
    $("body").on("click", ".user-posts", function () {
        var idPost = $(this).attr("data-rel");
        showLoading();
        $.ajax({
            type: "POST",
            url: rootUrl + 'index/search-post',
            data: {id: idPost},
            success: function (response) {
                hideLoading();
                var result = $.parseJSON(response);
                if (result.status == 1) {
                    $('.gallery-post').html(nl2br(result.data.p_description));
                    $(".gallery-post").css({
                        "background": "url('" + rootUrl + "public/FrontendCore/images/play_" + result.data.hlv_id + ".png') no-repeat top left",
                        "background-size": "auto 100%"
                    });
                    $(".btn-share-post").attr("data-rel", idPost);
                    $("#popupGallery").modal();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            }
        });
    });
    var $elems = $('body');
    var active = true;
    $(window).scroll(function () {
        wintop = $(window).scrollTop(); // calculate distance from top of window
        if (wintop > 500 && active == true) {
            $("#as-hamburger").fadeIn();
            active = false;
        }

        if (wintop < 500) {
            $("#as-hamburger").hide();
            active = true;
        }
    });
    $("body").on("click", ".btn-share-post", function () {
        var idPost = $(this).attr("data-rel");
        shareDescription = $(".gallery-post").text();
        sharePicture = rootUrl + "public/uploads/images/sharePosts/" + idPost + ".jpg";
        callFBShare();
    });

    $('.hq-hlv-post').keypress(function() {
        var length = $(this).val().length;
        var length = maxLength-length;
        $('.chars-count').text(length);
    });
    $('.popupPlaygame').on('hidden.bs.modal', function (e) {
        $(".chars-count").text(maxLength);
    })
});
function userPosts(idFrom) {
    var forgotvalidator = $(idFrom).validate();
    if (forgotvalidator.form()) {
        showLoading();
        var data = $(idFrom).serialize();
        //shareDescription = $(idFrom + " textarea[name='p_description']").val();
        //sharePicture = $(".image-share").attr("src");
        //callFBShare();
        $.ajax({
            type: "POST",
            url: rootUrl + 'posts',
            data: data,
            success: function (response) {
                hideLoading();
                var result = $.parseJSON(response);
                if (result.status == 1) {
                    shareDescription = result.description;
                    sharePicture = rootUrl + result.image;
                    callFBShare();
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
function showLoading() {
    $(".loadding-area").show();
}
function hideLoading() {
    $(".loadding-area").hide();
}
function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}