jQuery(document).ready(function($) {
	'use strict';

	const $wrapper = $('.js-post-container');
	const $button = $('.js-load-more');
	const $loader = $('.js-loader');
	const $nonce = $('#more_posts_nonce');
	const postsPerPage = $wrapper.find('.js-post:not(.sticky)').length;
	const category = $wrapper.data('category');
	const search = $wrapper.data('search');

	$button.on('click', function(event) {
		loadAjaxPosts(event);
	});

	function loadAjaxPosts(event) {
		event.preventDefault();

		if (!($loader.hasClass('is-loading') || $loader.hasClass('no-posts'))) {
			let postNumber = $wrapper.find('.js-post:not(.sticky)').length;

			$.ajax({
				'type': 'POST',
				'url': twentyTwentyAjaxLocalization.ajaxurl,
				'data': {
					'postsPerPage': postsPerPage,
					'postOffset': postNumber,
					'category': category,
					'search': search,
					'morePostsNonce': $nonce.val(),
					'action': twentyTwentyAjaxLocalization.action,
				},
				beforeSend: function () {
					$loader.addClass('is-loading');
				}
			})
				.done(function(response) {
					const contents = response.data;

					if (contents.length) {
						$wrapper.append(contents);
						$loader.removeClass('is-loading');
					} else {
						$button.html(twentyTwentyAjaxLocalization.noPosts);
						$loader.removeClass('is-loading');
						$loader.addClass('no-posts');
					}
				})
				.fail(function(error) {
					console.error(error)
				});
		}
	}
});
