/* lafka-child/js/cart-drawer.js
 * Cart drawer slide-in + focus trap + WC fragment listener.
 *
 * Listens to WC's added_to_cart jQuery event to auto-open the drawer
 * after AJAX add-to-cart. Also handles [data-lafka-cart-open] triggers
 * (e.g., header cart icon).
 *
 * @since lafka-child 5.8.0
 */
(function ($) {
  'use strict';
  if (!$) return;

  var drawer = document.querySelector('.lafka-cart-drawer');
  if (!drawer) return;

  var lastFocus = null;

  function open() {
    lastFocus = document.activeElement;
    drawer.dataset.open = 'true';
    drawer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    var f = drawer.querySelector('button, a, [tabindex="0"]');
    if (f && f.focus) f.focus();
  }

  function close() {
    drawer.dataset.open = 'false';
    drawer.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest && e.target.closest('[data-lafka-cart-close]')) close();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer.dataset.open === 'true') close();
    if (e.key === 'Tab' && drawer.dataset.open === 'true') {
      var focusables = Array.prototype.slice.call(drawer.querySelectorAll('button, a, [tabindex]:not([tabindex="-1"])'));
      var first = focusables[0];
      var last  = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  $(document.body).on('added_to_cart', open);

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest && e.target.closest('[data-lafka-cart-open]');
    if (trigger) { e.preventDefault(); open(); }
  });
})(window.jQuery);
