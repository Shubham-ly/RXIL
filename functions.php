
<?php 
    function get_css_from_page($response) {
        if (is_array($response)) {
            $content = $response['body'];
            $document = new DOMDocument();
            try {
                libxml_use_internal_errors(true);
                $document->loadHTML($content);
                libxml_use_internal_errors(false);
            } catch (Exception $e) {}
            $styles_array = [];

            foreach( $document->getElementsByTagName('link') as $style) {
                if ($style->hasAttribute('rel') && $style->getAttribute('rel') == 'stylesheet') {
                    $styles_array['links'][] = $style->getAttribute('href');
                }
            }

            foreach( $document->getElementsByTagName('style') as $style) {
                $styles_array['inline'][] = $style->nodeValue;
            }

            return $styles_array;
        }
    }
?>

<?php
    add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
    function enqueue_parent_styles() {
        wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
    }
?>

<?php 
    add_action('rest_api_init', function () {
        register_rest_route('wp/v2', 'get-home', 
            array(
                'methods'  => 'GET',
                'callback' => 'get_home',
            )
        );
    });
    function get_home($request) {
        $response = wp_remote_get(home_url());
        return get_css_from_page($response);
    }
?>

<?php 
    function add_cors_http_header(){
        header("Access-Control-Allow-Origin: *");
    }
    add_action('init','add_cors_http_header');
?>

<?php 

    add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v2', '/get-post-css', array(
            'methods' => 'GET',
            'callback' => 'get_post_css',
        ));
    });

    function get_post_css($request) {
        $post_name = $request->get_param('name');
        if ($post_name) {
            $response = wp_remote_get(home_url() . "/" . $post_name);
            return get_css_from_page($response);
        }
    }

?>

<?php
    add_action('rest_api_init', 'register_rest_images' );
    function register_rest_images(){
        register_rest_field( array('post'),
            'fimg_url',
            array(
                'get_callback'    => 'get_rest_featured_image',
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
    function get_rest_featured_image( $object, $field_name, $request ) {
        if( $object['featured_media'] ){
            $img = wp_get_attachment_image_src( $object['featured_media'], 'app-thumb' );
            return $img[0];
        }
        return false;
    }
?>

<?php 
    function format_post($post) {
        if ( $post ) {
            $categories = get_the_category( $post->ID );
            $filteredCategory = [];
            foreach($categories as $cd){
                $data['name'] = $cd->cat_name;
                $data['id'] = $cd->term_id;
                $data['slug'] = $cd->slug;
                $filteredCategory[] = $data;
            }

            $post->{'categories'} = $filteredCategory;
        }

        $post_thumbnail_id = get_post_thumbnail_id($post->ID);
        $imageSrc = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
        $post->{'featured_media'} = $imageSrc ? $imageSrc[0]: false;

        $post->{'author_name'} = get_the_author_meta('display_name', $post->post_author);
        $post->{'excerpt'} = get_the_excerpt($post->ID);

        $fields = get_fields($post->ID);
        if ($fields) {
            foreach(array_keys($fields) as $key) {
                $post->{$key} = $fields[$key];
            }
        }

        return $post;
    }
?>

<?php 
    add_action('rest_api_init', function () {
        register_rest_route('wp/v2', '/get-all-posts', array(
            'methods' => 'GET',
            'callback' => 'get_all_posts'
        ));
    });

    function get_all_posts($request) {

        $page = $request->get_param('page') ? $request->get_param('page') : 1;

        $year = $request->get_param('year') ? $request->get_param('year') : date('Y');

        $query = new WP_Query(
            array(
                'post_type' => 'post', 
                'paged' => $page, 
                'posts_per_page' => 12,
                'date_query' => array(
                    array( 
                        'year' => $year,
                        'compare' => '=='
                    ),
                )
            )
        );

        $posts = $query->posts;
        $data = [];
        foreach ( $posts as $post) {
           format_post($post);
        }
        $data['posts'] = $posts;
        $data['max_num_pages'] = $query->max_num_pages;
        $data['from_year'] = $year;
        return $data;
    }

?>

<?php 
    add_action('rest_api_init', function() {
        register_rest_route('wp/v2', '/get-post-by-id/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => 'get_post_by_id'
        ));
    });

    function get_post_by_id($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);
        format_post($post);
        return $post;
    }

?>