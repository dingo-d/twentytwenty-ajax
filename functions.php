<?php

add_action( 'wp_enqueue_scripts', 'twentytwenty_ajax_enqueue_styles' );

function twentytwenty_ajax_enqueue_styles() {
    $parenthandle = 'twentytwenty-style';
    $theme = wp_get_theme();

    wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css',
        array(),  // if the parent theme code has a dependency, copy it to here
        $theme->parent()->get('Version')
    );

    wp_enqueue_style( 'twentytwenty-ajax-style', get_stylesheet_uri(),
        array( $parenthandle ),
        $theme->get('Version') // this only works if you have Version in the style header
    );
}

add_action( 'wp_enqueue_scripts', 'twentytwenty_ajax_enqueue_scripts' );

function twentytwenty_ajax_enqueue_scripts() {
	$theme_version = wp_get_theme()->get( 'Version' );
	$script_handle = 'twentytwenty-ajax';

	wp_enqueue_script( $script_handle, get_stylesheet_directory_uri() . '/assets/js/index.js',
		array( 'jquery' ),
		$theme_version,
		false
	);

	// Include WPML case.
	if( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ) ) ){
		$ajaxurl = admin_url( 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE );
	} else{
		$ajaxurl = admin_url( 'admin-ajax.php');
	}

	wp_localize_script( $script_handle, 'twentyTwentyAjaxLocalization', array(
		'ajaxurl' => $ajaxurl,
		'action' => 'twentytwenty_ajax_more_post',
		'noPosts' => esc_html__('No older posts found', 'twentytwenty-ajax'),
	) );
}

add_filter( 'post_class', 'twentytwenty_ajax_add_js_post_class', 10, 3 );

function twentytwenty_ajax_add_js_post_class( $classes, $class, $post_id ) {
	$classes[] = 'js-post';

	return $classes;
}

add_action( 'wp_ajax_nopriv_twentytwenty_ajax_more_post', 'twentytwenty_ajax_more_post_ajax' );
add_action( 'wp_ajax_twentytwenty_ajax_more_post', 'twentytwenty_ajax_more_post_ajax' );

function twentytwenty_ajax_more_post_ajax() {
	if ( ! isset( $_POST['morePostsNonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['morePostsNonce'] ), 'more_posts_nonce_action' ) ) {
		return wp_send_json_error( esc_html__( 'Number not only once is invalid', 'twentytwenty-ajax' ), 404 );
	}

	$posts_per_page = ! empty( $_POST['postsPerPage'] ) ? (int) $_POST['postsPerPage'] : 1;
	$offset = ! empty( $_POST['postOffset'] ) ? (int) $_POST['postOffset'] : 0;
	$category = ! empty( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
	$search = ! empty( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

	$query_args = array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'perm' => 'readable',
		'posts_per_page' => $posts_per_page,
		'offset' => $offset,
		'post__not_in' => get_option( 'sticky_posts' ),
	);

	if ( ! empty( $category ) ) {
		$query_args['cat'] = $category;
	}

	if ( ! empty( $search ) ) {
		$query_args['s'] = $search;
	}

	$posts_query = new WP_Query( $query_args );

	$posts_out = '';

	ob_start();
	if ($posts_query->have_posts()) {
		while ($posts_query->have_posts()) {
			$posts_query->the_post();

			echo '<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />';
			get_template_part( 'template-parts/content', 'post' );
		}
	}

	$posts_out = ob_get_clean();

	wp_reset_postdata();

	wp_send_json_success( $posts_out, 200 );
}
