/* lafka-child/js/order-method.js
 * Method-switch modal — DOM construction via createElement to avoid innerHTML.
 *
 * @since lafka-child 5.8.0
 */
(function () {
  'use strict';

  var COOKIE = 'lafka_order_method';
  var VALID = ['delivery', 'pickup'];

  function setMethod(method) {
    if (VALID.indexOf(method) === -1) return;
    var exp = new Date();
    exp.setFullYear(exp.getFullYear() + 1);
    document.cookie = COOKIE + '=' + method +
      '; path=/; expires=' + exp.toUTCString() +
      '; SameSite=Lax' +
      (location.protocol === 'https:' ? '; Secure' : '');
    location.reload();
  }

  function el(tag, attrs, text) {
    var node = document.createElement(tag);
    if (attrs) {
      Object.keys(attrs).forEach(function (k) {
        if (k === 'class') { node.className = attrs[k]; }
        else { node.setAttribute(k, attrs[k]); }
      });
    }
    if (text != null) { node.textContent = text; }
    return node;
  }

  function buildModal() {
    var overlay = el('div', { 'class': 'lafka-method-modal__overlay' });
    var modal   = el('div', { 'class': 'lafka-method-modal', role: 'dialog', 'aria-modal': 'true', 'aria-labelledby': 'lafka-method-modal-title' });
    modal.appendChild(el('h3', { id: 'lafka-method-modal-title' }, 'How are you getting your order?'));

    // Operator-specific labels come from wp_localize_script (resolver-backed).
    // Never hardcode literals — lafka-child is public OSS. Falls back to a
    // generic label if the localized data is missing.
    var localized   = (typeof window.lafkaOrderMethodLabels === 'object') ? window.lafkaOrderMethodLabels : {};
    var pickupAddr  = (localized.pickupLabel || '').trim();
    var pickupText  = pickupAddr ? ('🏪  Pickup at ' + pickupAddr) : '🏪  Pickup';
    var btnDelivery = el('button', { type: 'button', 'data-method': 'delivery' }, '🚚  Delivery');
    var btnPickup   = el('button', { type: 'button', 'data-method': 'pickup' },   pickupText);
    var btnClose    = el('button', { type: 'button', 'class': 'lafka-method-modal__close', 'aria-label': 'Close' }, '×');

    modal.appendChild(btnDelivery);
    modal.appendChild(btnPickup);
    modal.appendChild(btnClose);
    overlay.appendChild(modal);
    return overlay;
  }

  function openModal() {
    var overlay = buildModal();
    document.body.appendChild(overlay);

    overlay.addEventListener('click', function (e) {
      var m = e.target.getAttribute && e.target.getAttribute('data-method');
      if (m) { setMethod(m); return; }
      if (e.target === overlay || (e.target.classList && e.target.classList.contains('lafka-method-modal__close'))) {
        overlay.remove();
      }
    });

    function onEsc(ev) {
      if (ev.key === 'Escape') {
        overlay.remove();
        document.removeEventListener('keydown', onEsc);
      }
    }
    document.addEventListener('keydown', onEsc);
  }

  document.addEventListener('click', function (e) {
    var t = e.target.closest && e.target.closest('[data-lafka-method-toggle]');
    if (!t) return;
    e.preventDefault();
    openModal();
  });
})();
