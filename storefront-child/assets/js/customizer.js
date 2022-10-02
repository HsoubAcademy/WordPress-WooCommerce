(function ($) {

	wp.customize('storefront_header_theme', function (value) {
		value.bind(function (to) {
			if (to == 'theme1') {
				$('.woocommerce-active .site-header .site-branding').css({
					'float': 'right',
					'text-align': 'right',
					'width': 'auto'
				});

				$('.woocommerce-active .site-header .site-search').css({
					'float': 'left',
					'display': 'block'
				});

				$('.woocommerce-active .site-header .main-navigation').css({
					'float': 'right',
					'text-align': 'right',
					'width': 'auto'
				});

				$('.woocommerce-active .site-header .site-header-cart').css({
					'float': 'left',
					'display': 'block'
				});
			}
			else if (to == 'theme2') {
				$('.woocommerce-active .site-header .site-branding').css({
					'float': 'none',
					'text-align': 'center',
					'width': '100%'
				});

				$('.woocommerce-active .site-header .site-search').css({
					'display': 'none',
				});

				$('.woocommerce-active .site-header .main-navigation').css({
					'float': 'right',
					'text-align': 'right',
					'width': 'auto'
				});

				$('.woocommerce-active .site-header .site-header-cart').css({
					'float': 'left',
					'display': 'block'
				});
			}
			else if (to == 'theme3') {
				$('.woocommerce-active .site-header .site-branding').css({
					'float': 'left',
					'text-align': 'left',
					'width': 'auto'
				});

				$('.woocommerce-active .site-header .site-search').css({
					'float': 'right',
					'text-align': 'right'
				});

				$('.woocommerce-active .site-header .main-navigation').css({
					'float': 'left',
					'text-align': 'left',
					'width': 'auto'
				});

				$('.woocommerce-active .site-header .site-header-cart').css({
					'float': 'right',
					'text-align': 'right',
					'display': 'block'
				});
			}
			else if (to == 'theme4') {
				$('.woocommerce-active .site-header .site-branding').css({
					'float': 'none',
					'text-align': 'center',
					'width': '100%'
				});

				$('.woocommerce-active .site-header .site-search').css({
					'display': 'none',
				});

				$('.woocommerce-active .site-header .main-navigation').css({
					'float': 'none',
					'text-align': 'center',
					'width': '100%'
				});

				$('.woocommerce-active .site-header .site-header-cart').css({
					'display': 'none',
				});
			}
		});
	});

})(jQuery);