(function ($) {
    "use strict";

    /**
     * Use this file to override JavaScript functions defined in the
     * parent theme's lafka-front.js.
     *
     * The parent theme exposes several functions on `window` that you
     * can redefine here. Because this script loads after the parent
     * (via the 'lafka-front' dependency), your version wins.
     *
     * Available overridable functions:
     *   window.lafkaStickyHeaderInit   — sticky header behavior
     *   window.lafkaInitSmallCountdowns — product countdown badges
     *   window.lafkaOrderHoursCountdown — order-hours countdown
     */

    /* -----------------------------------------------------------------------
     * Example 1: Custom sticky header
     * Replace the parent's sticky header with a simpler version that just
     * adds/removes a CSS class. Style it in style.css.
     * -------------------------------------------------------------------- */
    // window.lafkaStickyHeaderInit = function () {
    //     var $header = $('#header');
    //     var threshold = $header.outerHeight();
    //     $(window).on('scroll', function () {
    //         if ($(this).scrollTop() > threshold) {
    //             $header.addClass('child-sticky-active');
    //         } else {
    //             $header.removeClass('child-sticky-active');
    //         }
    //     });
    // };

    /* -----------------------------------------------------------------------
     * Example 2: Disable product countdown badges entirely
     * -------------------------------------------------------------------- */
    // window.lafkaInitSmallCountdowns = function () {
    //     // intentionally empty — countdowns won't initialize
    // };

    /* -----------------------------------------------------------------------
     * Example 3: Custom mobile menu behavior
     * Adds a class to the body when the mobile menu is toggled.
     * -------------------------------------------------------------------- */
    // $(document).ready(function () {
    //     $('a.mob-menu-toggle').on('click', function () {
    //         $('body').toggleClass('child-mobile-menu-open');
    //     });
    // });

})(window.jQuery);
