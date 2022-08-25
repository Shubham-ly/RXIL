
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
        register_rest_route('wp/v2', 'get-global-styles', 
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

<?php
    add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v2', '/get-nav-links', array(
            'methods' => 'GET',
            'callback' => 'get_nav_links',
        ));
    });

    function get_page_link_info($page) {
        return array(
            'name' => $page->post_title,
            'slug' => $page->post_name,
        );
    }

    function get_nav_links() {
        $pages = get_pages(array('sort_column' => 'menu_order'));
        $nav_links = [];
        foreach( $pages as $page ) {
            
            if ($page->post_parent) continue;

            $sub_page_query = array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'parent' => $page->ID,
                'sort_column' => 'menu_order'
            );
            $sub_pages = get_pages( $sub_page_query );
            $sub_pages_link_info = [];

            foreach ( $sub_pages as $sub_page) {
                $sub_pages_link_info[] = get_page_link_info($sub_page);
            }

            $nav_links[] = array_merge(
                get_page_link_info($page),
                array( 'sub_links' => $sub_pages_link_info ),
            );
        }
        return $nav_links;
    }

?>

<?php 
    add_action( 'rest_api_init', function() {
        register_rest_route('wp/v2', '/get-page-by-id/(?P<slug>[a-zA-Z0-9-]+)', array(
            'method' => 'GET',
            'callback' => 'fetch_page_content'
        ));
    });

    function fetch_page_content($request) {
        $page_slug = $request->get_param('slug');
        $page = get_page_by_path($page_slug);
        $response = wp_remote_get(home_url() . "/" . $page_slug);
        $page_styles = get_css_from_page($response);
        $page->{'styles'} = $page_styles;
        return $page;
    }

?>

<?php 


    add_action( 'rest_api_init', function() {
        register_rest_route('wp/v2', '/get-page-resources/(?P<slug>[a-zA-Z0-9-]+)', array(
            'method' => 'GET',
            'callback' => 'get_page_resources'
        ));
    });

    function get_page_resources ($request) {
        $page = $request->get_param('slug');
        
        $current_directory = home_url() . '/wp-content/themes/twentytwentytwo-child/';

        return array(
            'style' => $current_directory . '/styles/' . $page . '.css',
            'script' => $current_directory . '/scripts/' . $page . '.js'
        );
    }


?>

<?php 
    add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v2', '/get-page-body/(?P<slug>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => 'get_page_body',
        ));
    });

    function DOMinnerHTML(DOMNode $element) { 
        $innerHTML = ""; 
        $children  = $element->childNodes;
        foreach ($children as $child)  { 
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHTML; 
    } 

    function get_page_body($request) {
        $page_slug = $request->get_param('slug');
        $response = wp_remote_get(home_url() . '/' . $page_slug);

        if (is_array($response)) {
            $content = $response['body'];

            $document = new DOMDocument();
            libxml_use_internal_errors(true);
            $document->loadHTML($content);
            libxml_use_internal_errors(false);

            $body = $document->getElementsByTagName('body')->item(0);
            $res = '';
            foreach ($body->childNodes as $child) {
                $res .= DOMinnerHTML($child);
            }
            return $res;

        }
    }

?>