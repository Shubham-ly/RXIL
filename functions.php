<?php
	add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
	function enqueue_parent_styles() {
		wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
		wp_enqueue_style( 'theme-style', get_stylesheet_uri() );
	}

	add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
	function enqueue_scripts() {
		wp_enqueue_style( 'flickity-css',  'https://unpkg.com/flickity@2/dist/flickity.min.css');
		wp_enqueue_script( 'flickity-js', 'https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js' );
		wp_enqueue_script( 'home-script', get_stylesheet_directory_uri() . '/scripts/home.js' );
	}

?>

<?php     
	function add_resource_type_taxonomy () {
		register_taxonomy('resource-category', '', array(
			'hierarchical' => true,
			'labels' => array(
				'name' => _x( 'Resource Categories', 'taxonomy general name' ),
				'singular_name' => _x('Resource Category', 'taxonomy singular name'),
				'search_items' =>  __('Search Resource Category'),
				'all_items' => __( 'All Resource Categories'),
				'parent_item' => __( 'Parent Resource Category' ),
				'parent_item_colon' => __( 'Parent Resource Category:' ),
				'edit_item' => __( 'Edit Resource Category' ),
				'update_item' => __( 'Update Resource Category' ),
				'add_new_item' => __( 'Add New Resource Category' ),
				'new_item_name' => __( 'New Resource Category' ),
				'menu_name' => __( 'Resource Categories' ),
			),
			'show_in_rest' => true,
			'rewrite' => array(
				'slug' => 'resources',  
				'with_front' => false,
				'hierarchical' => true,
			),
		));
	}
	add_action('init', 'add_resource_type_taxonomy', 0);

	add_action('init', function() {
		register_taxonomy_for_object_type('resource-category', 'attachment');
	});

?>

<?php 
	add_theme_support( 'menus' );

	add_action( 'init', 'register_custom_menus' );

	function register_custom_menus() {
		register_nav_menus(
			array(
				'primary-menu' => __( 'Primary Menu' ),
			)
		);
	}
?>
