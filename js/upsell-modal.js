/* lafka-child/js/upsell-modal.js
 * +Add buttons in upsell row: AJAX-add for simple products, navigate for variable.
 *
 * Uses textContent for all dynamic updates; never innerHTML.
 *
 * @since lafka-child 5.8.0
 */
(function ($) {
	'use strict';
	if (!$) return;

	document.addEventListener('click', function (e) {
		var btn = e.target.closest && e.target.closest('.lafka-pdp-upsell__add');
		if (!btn) return;
		e.preventDefault();
		var productId   = btn.dataset.productId;
		var productType = btn.dataset.productType;
		var permalink   = btn.dataset.permalink;

		if (productType === 'simple') {
			addSimple(btn, productId);
		} else {
			window.location.href = permalink;
		}
	});

	function addSimple(btn, productId) {
		btn.disabled = true;
		var originalText = btn.textContent;
		btn.textContent = '...';
		var endpoint = (window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url
			? window.wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart')
			: '/?wc-ajax=add_to_cart');
		$.ajax({
			url: endpoint,
			method: 'POST',
			data: { product_id: productId, quantity: 1 },
			success: function (response) {
				$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $(btn)]);
				btn.disabled = false;
				btn.textContent = '✓ Added';
				setTimeout(function () { btn.textContent = originalText; }, 2000);
			},
			error: function () {
				btn.disabled = false;
				btn.textContent = originalText;
				console.error('lafka-pdp: failed to add to cart');
			}
		});
	}
})(window.jQuery);
