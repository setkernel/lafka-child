/* lafka-child/js/pdp-pickers.js
 * Variation/addon pickers — live price + required-state + per-size topping prices.
 *
 * Reads variation prices from .lafka-pdp-pickers[data-prices] (JSON map of
 * attribute-combo -> price). Reads per-size addon deltas from each topping
 * label's [data-lafka-topping-price] attr (JSON map of size -> delta).
 *
 * Uses ONLY textContent for dynamic updates; never innerHTML.
 *
 * @since lafka-child 5.8.0
 */
(function () {
  'use strict';

  var root = document.querySelector('.lafka-pdp-pickers');
  if (!root) return;

  var priceEl   = document.querySelector('[data-lafka-live-price]');
  var ctas      = document.querySelectorAll('[data-lafka-add-to-cart]');
  var ctaLabels = document.querySelectorAll('[data-lafka-cta-label]');
  var variationPrices;
  try { variationPrices = JSON.parse(root.dataset.prices || '{}'); }
  catch (e) { variationPrices = {}; }

  function getSelectedAttrs() {
    var attrs = {};
    root.querySelectorAll('input[type=radio]:checked').forEach(function (input) {
      attrs[input.name] = input.value;
    });
    return attrs;
  }

  function findVariationPrice(attrs) {
    var keys = Object.keys(variationPrices);
    for (var i = 0; i < keys.length; i++) {
      var stored;
      try { stored = JSON.parse(keys[i]); } catch (e) { continue; }
      var ok = true;
      var attrKeys = Object.keys(attrs);
      for (var j = 0; j < attrKeys.length; j++) {
        var n = attrKeys[j];
        var v = attrs[n];
        if (stored[n] !== v && stored[n] !== '') { ok = false; break; }
      }
      if (ok) return parseFloat(variationPrices[keys[i]]);
    }
    return null;
  }

  function bareAttrMap(attrs) {
    // attrs come keyed by 'attribute_pa_size'; lafka-plugin emits prices
    // keyed by bare 'pa_size'. Strip the prefix.
    var out = {};
    Object.keys(attrs).forEach(function (k) {
      var bare = k.indexOf('attribute_') === 0 ? k.substring('attribute_'.length) : k;
      out[bare] = attrs[k];
    });
    return out;
  }

  function getAddonDelta(allAttrs) {
    var attrMap = bareAttrMap(allAttrs);
    var total = 0;
    document.querySelectorAll('input[name^="addon-"]:checked').forEach(function (input) {
      var price = null;
      // Canonical: lafka-plugin's renderer puts the per-attribute price
      // matrix on each addon input as data-attribute-prices, shape
      // { "pa_size": { "small": "1.00", "medium": "1.50" }, ... }.
      var attrPricesJson = input.getAttribute('data-attribute-prices');
      if (attrPricesJson) {
        try {
          var attrPrices = JSON.parse(attrPricesJson);
          Object.keys(attrPrices).forEach(function (taxonomyName) {
            if (price !== null) return;
            var slug = attrMap[taxonomyName];
            if (slug && attrPrices[taxonomyName] && attrPrices[taxonomyName][slug] !== undefined) {
              price = parseFloat(attrPrices[taxonomyName][slug]);
            }
          });
        } catch (e) { /* fall through to flat price */ }
      }
      // Flat-price fallback (addon without per-attribute pricing).
      if (price === null) {
        var raw = input.getAttribute('data-price');
        if (raw !== null && raw !== '') price = parseFloat(raw);
      }
      if (price !== null && !isNaN(price)) total += price;
    });
    return total;
  }

  function allRequiredSet() {
    var ok = true;
    root.querySelectorAll('[data-required="true"]').forEach(function (field) {
      if (field.querySelectorAll('input:checked').length === 0) ok = false;
    });
    return ok;
  }

  function recompute() {
    var attrs = getSelectedAttrs();
    var basePrice = findVariationPrice(attrs);
    var addonDelta = getAddonDelta(attrs);
    var total = (basePrice || 0) + addonDelta;

    if (priceEl && basePrice !== null) {
      priceEl.textContent = '$' + total.toFixed(2);
    }

    // Per-topping price label updates (e.g. "+$1.50" next to each topping)
    // are handled by lafka-plugin's addons.js via the formatted_price
    // mechanism on the lafka-product-addons-update event. Trigger that
    // event so addons.js re-resolves the per-attribute prices and updates
    // the visible topping labels.
    if (window.jQuery) {
      var $form = window.jQuery(root).closest('form.cart');
      if ($form.length) $form.trigger('lafka-product-addons-update');
    }

    var ok = allRequiredSet() && basePrice !== null;
    ctas.forEach(function (cta) {
      cta.disabled = !ok;
      cta.dataset.lafkaState = ok ? 'ready' : 'incomplete';
    });
    ctaLabels.forEach(function (label) {
      if (ok) {
        label.textContent = 'Add to Cart · $' + total.toFixed(2);
      } else {
        var firstMissing = null;
        var fields = root.querySelectorAll('[data-required="true"]');
        for (var i = 0; i < fields.length; i++) {
          if (fields[i].querySelectorAll('input:checked').length === 0) { firstMissing = fields[i]; break; }
        }
        var legend = firstMissing ? firstMissing.querySelector('.lafka-pdp-picker__label') : null;
        var hint = legend ? ('Pick a ' + legend.textContent.toLowerCase() + ' to continue') : 'Make a selection';
        label.textContent = hint;
      }
    });
  }

  // Quantity buttons. The canonical source of truth is the form's
  // <input name="quantity"> (lives in the desktop cart row); both desktop
  // and mobile +/- buttons mutate that input, then we mirror the value to
  // every [data-lafka-qty-display] span. Without this handler the buttons
  // were dead — clicking them did nothing — and submitting from the
  // mobile sticky bar always shipped qty 1 regardless of what the
  // operator clicked.
  function getQtyInput() {
    var form = root.closest('form.cart');
    return form ? form.querySelector('input[name="quantity"]') : null;
  }

  function syncQtyDisplays(value) {
    document.querySelectorAll('[data-lafka-qty-display]').forEach(function (el) {
      el.textContent = String(value);
    });
  }

  document.addEventListener('click', function (e) {
    var btn = e.target && e.target.closest ? e.target.closest('[data-lafka-qty]') : null;
    if (!btn) return;
    var qtyInput = getQtyInput();
    if (!qtyInput) return;
    var delta = parseInt(btn.getAttribute('data-lafka-qty'), 10) || 0;
    var min   = parseInt(qtyInput.getAttribute('min') || '1', 10) || 1;
    var maxAttr = qtyInput.getAttribute('max');
    var max   = maxAttr ? parseInt(maxAttr, 10) : Infinity;
    var current = parseInt(qtyInput.value, 10) || min;
    var next  = Math.max(min, Math.min(max, current + delta));
    if (next === current) return;
    qtyInput.value = String(next);
    syncQtyDisplays(next);
    // Trigger the addons-update event so addon-cost × qty totals refresh.
    if (window.jQuery) {
      var $form = window.jQuery(qtyInput).closest('form.cart');
      if ($form.length) $form.trigger('lafka-product-addons-update');
    }
    // Trigger native change event so any other listeners pick it up.
    qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
  });

  // Direct keyboard edits to the input also need to mirror to the mobile
  // display. The element exists on both branches (variable + simple).
  document.addEventListener('input', function (e) {
    if (!e.target || !e.target.matches) return;
    if (!e.target.matches('input[name="quantity"]')) return;
    var v = parseInt(e.target.value, 10);
    if (!isNaN(v)) syncQtyDisplays(v);
  });

  root.addEventListener('change', recompute);
  document.addEventListener('change', function (e) {
    if (e.target.matches && e.target.matches('input[name^="addon-"]')) recompute();
  });
  recompute();
})();
