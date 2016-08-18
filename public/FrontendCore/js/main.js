/**
 * Created by haing on 8/17/2016.
 */
$(function ($) {
    "use strict"; // Start of use strict
    var trigger = $("#as-hamburger"),
        primary_nav = $(".as-nav-wrapper"),
        isClosed = true,
        primary_nav_link = $(".as-primary-nav li a");

    function burgerTime() {
        if (isClosed == true) {
            trigger.removeClass("is-open");
            trigger.addClass("is-closed");
            isClosed = false;
        } else {
            trigger.removeClass("is-closed");
            trigger.addClass("is-open");
            isClosed = true;
        }
    }

    function hide_menu() {
        burgerTime();
        primary_nav.toggleClass("is-visible");
        $("html, body").toggleClass("as-hidden");
    }

    trigger.click(function () {
        hide_menu();
    });
    primary_nav_link.click(function () {
        hide_menu();
    });

    $('a[href*="#"]:not([href="#"])').click(function () {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top
                }, 1000);
                return false;
            }
        }
    });
});
