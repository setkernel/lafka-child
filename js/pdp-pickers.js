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

  function getAddonDelta(currentSize) {
    var total = 0;
    document.querySelectorAll('input[name^="lafka_addon"]:checked').forEach(function (input) {
      var label = input.closest('.lafka-pdp-topping');
      if (!label) return;
      var priceLabel = label.querySelector('[data-lafka-topping-price]');
      if (!priceLabel) return;
      var data;
      try { data = JSON.parse(priceLabel.dataset.lafkaToppingPrice || '{}'); }
      catch (e) { return; }
      var sizeKey = currentSize ? currentSize.replace(/^pa_size:/, '') : 'medium';
      var delta = parseFloat(data[sizeKey] || data.medium || '0');
      if (!isNaN(delta)) total += delta;
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

  function updateToppingLabels(currentSize) {
    document.querySelectorAll('[data-lafka-topping-price]').forEach(function (labelEl) {
      var data;
      try { data = JSON.parse(labelEl.dataset.lafkaToppingPrice || '{}'); }
      catch (e) { return; }
      var sizeKey = currentSize || 'medium';
      var price = parseFloat(data[sizeKey] || data.medium || '0');
      labelEl.textContent = price > 0 ? '+$' + price.toFixed(2) : '';
    });
  }

  function recompute() {
    var attrs = getSelectedAttrs();
    var sizeAttr = attrs['attribute_pa_size'] || attrs['pa_size'] || '';
    var basePrice = findVariationPrice(attrs);
    var addonDelta = getAddonDelta(sizeAttr);
    var total = (basePrice || 0) + addonDelta;

    if (priceEl && basePrice !== null) {
      priceEl.textContent = '$' + total.toFixed(2);
    }

    updateToppingLabels(sizeAttr);

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

  root.addEventListener('change', recompute);
  document.addEventListener('change', function (e) {
    if (e.target.matches && e.target.matches('input[name^="lafka_addon"]')) recompute();
  });
  recompute();
})();
