jQuery(document).ready(function ($) {
    'use strict';

    const $wrapper = $('.js-post-container');
    const $button = $('.js-load-more');
    const $loader = $('.js-loader');
    const $nonce = $('#more_posts_nonce');
    const postsPerPage = $wrapper.find('.js-post:not(.sticky)').length;
    const category = $wrapper.data('category');
    const search = $wrapper.data('search');

    $button.on('click', function (event) {
        loadAjaxPosts(event);
    });

    function loadAjaxPosts(event) {
        event.preventDefault();

        if (!($loader.hasClass('is-loading') || $loader.hasClass('no-posts'))) {
            const postNumber = $wrapper.find('.js-post:not(.sticky)').length;
            const data = new FormData();

            data.append('postsPerPage', postsPerPage);
            data.append('postOffset', postNumber);
            data.append('category', category);
            data.append('search', search);
            data.append('morePostsNonce', $nonce.val());
            data.append('action', twentyTwentyAjaxLocalization.action);

            $loader.addClass('is-loading');

            fetch(twentyTwentyAjaxLocalization.ajaxurl, {
                method: 'POST',
                body: data
            })
                .then(response => response.json())
                .then(response => {
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
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }
});
