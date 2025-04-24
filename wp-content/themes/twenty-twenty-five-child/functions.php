<?php
/**
 * Add Twenty Twenty-Five Stylesheet
 */
function my_twenty_twenty_five_styles() {
	wp_enqueue_style( 'twentytwentyfive-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'my_twenty_twenty_five_styles', 99 );

/**
 * Add Child Theme Stylesheet
 */
function my_twenty_twenty_five_child_styles() {
	wp_enqueue_style( 'twenty-twenty-five-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'twentytwentyfive-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'my_twenty_twenty_five_child_styles' );

//My code for task 3 (to redirect the users away from the site if their IP address starts with 77.29.)
function redirect_ip_range_users() {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (strpos($ip_address, '77.29') === 0) {
        wp_redirect('https://www.google.com/');
        exit;
    }
}
add_action('template_redirect', 'redirect_ip_range_users');


//My code for task 4 (Custom Post Type: Projects)
function register_projects_post_type() {
    $labels = array(
        'name'               => 'Projects',
        'singular_name'      => 'Project',
        'menu_name'          => 'Projects',
        'name_admin_bar'     => 'Project',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Project',
        'new_item'           => 'New Project',
        'edit_item'          => 'Edit Project',
        'view_item'          => 'View Project',
        'all_items'          => 'All Projects',
        'search_items'       => 'Search Projects',
        'not_found'          => 'No projects found.',
        'not_found_in_trash' => 'No projects found in Trash.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite' => array('slug' => 'projects', 'with_front' => false),
        'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'show_in_rest'       => true, 
        'menu_icon'          => 'dashicons-portfolio',
    );

    register_post_type('projects', $args);
}
add_action('init', 'register_projects_post_type');


// To register the Custom Taxonomy: Project Type
function register_project_type_taxonomy() {
    $labels = array(
        'name'              => 'Project Types',
        'singular_name'     => 'Project Type',
        'search_items'      => 'Search Project Types',
        'all_items'         => 'All Project Types',
        'parent_item'       => 'Parent Project Type',
        'parent_item_colon' => 'Parent Project Type:',
        'edit_item'         => 'Edit Project Type',
        'update_item'       => 'Update Project Type',
        'add_new_item'      => 'Add New Project Type',
        'new_item_name'     => 'New Project Type Name',
        'menu_name'         => 'Project Types',
    );

    $args = array(
        'hierarchical'      => true, 
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array('slug' => 'project-type'),
        'show_in_rest'      => true, 
    );

    register_taxonomy('project_type', 'projects', $args);
}
add_action('init', 'register_project_type_taxonomy');


// My code for task 6 for AJAX endpoint
function wpb_ajax_fetch_projects() {
    $num_posts = is_user_logged_in() ? 6 : 3;
    $args = array(
        'post_type'      => 'projects',
        'posts_per_page' => $num_posts,
        'tax_query'      => array(
            array(
                'taxonomy' => 'project_type',
                'field'    => 'slug',
                'terms'    => 'architecture',
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );
    $data  = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $data[] = array(
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'link'  => get_permalink(),
            );
        }
        wp_reset_postdata();
    }

    wp_send_json( array(
        'success' => true,
        'data'    => $data,
    ) );
}

add_action( 'wp_ajax_fetch_projects', 'wpb_ajax_fetch_projects' );
add_action( 'wp_ajax_nopriv_fetch_projects', 'wpb_ajax_fetch_projects' );

// Enqueue script and localize AJAX URL
function wpb_enqueue_projects_script() {
    wp_enqueue_script(
      'projects-ajax-script',
      get_stylesheet_directory_uri() . '/js/projects-ajax.js',
      array('jquery'),
      null,
      true
    );
    wp_localize_script( 'projects-ajax-script', 'projectsAjax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'projects_nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'wpb_enqueue_projects_script' );


///short code to show the ajax result by clicking on the button "Load Projects"
function wpb_projects_test_shortcode() {
    return '
      <button id="fetch-projects-btn">Load Projects</button>
      <div id="projects-result"></div>
    ';
}
add_shortcode( 'projects_test', 'wpb_projects_test_shortcode' );
// please use this short code by creating a page and check the result for your task i have tested it for both logged and non logged users [projects_test]

/////code for task 7
function hs_give_me_coffee() {
    $endpoint = 'https://coffee.alexflipnote.dev/random.json';

    $response = wp_remote_get( $endpoint, array(
        'timeout'   => 10,
        'headers'   => array(
            'Accept' => 'application/json',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    if ( 200 !== intval( $code ) || empty( $body ) ) {
        return new WP_Error( 'coffee_error', 'Unexpected response code or empty body: ' . $code );
    }

    $data = json_decode( $body, true );
    if ( json_last_error() !== JSON_ERROR_NONE || empty( $data['file'] ) ) {
        return new WP_Error( 'coffee_error', 'Invalid JSON response.' );
    }

    return esc_url_raw( $data['file'] );
}


// I have created the Shortcode to display a random coffee image.
function hs_random_coffee_shortcode() {
    $coffee_url = hs_give_me_coffee();
    if ( is_wp_error( $coffee_url ) ) {
        return '<p>Error fetching coffee: ' . esc_html( $coffee_url->get_error_message() ) . '</p>';
    }
    return '<img src="' . esc_url( $coffee_url ) . '" alt="Random Coffee" style="max-width:100%;height:auto;">';
}
add_shortcode( 'random_coffee', 'hs_random_coffee_shortcode' );

// Please use this short to get random coffee image on each refresh [random_coffee]

/// code for task 8

function hs_get_one_kanye_quote() {
    $endpoint = 'https://api.kanye.rest';

    $response = wp_remote_get( $endpoint, array(
        'timeout' => 5,
        'headers' => array( 'Accept' => 'application/json' ),
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    if ( 200 !== intval( $code ) || empty( $body ) ) {
        return new WP_Error( 'kanye_error', 'Unexpected response: ' . $code );
    }

    $data = json_decode( $body, true );
    if ( json_last_error() !== JSON_ERROR_NONE || empty( $data['quote'] ) ) {
        return new WP_Error( 'kanye_error', 'Invalid JSON.' );
    }

    return sanitize_text_field( $data['quote'] );
}

// I am creating a shortcode to display 5 Kanye quotes on a page or post.
function hs_kanye_quotes_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'count' => 5,
    ), $atts, 'kanye_quotes' );

    $count = intval( $atts['count'] );
    if ( $count < 1 ) {
        return '<p>Invalid number of quotes.</p>';
    }

    $html = '<ul class="kanye-quotes">';
    for ( $i = 0; $i < $count; $i++ ) {
        $quote = hs_get_one_kanye_quote();
        if ( is_wp_error( $quote ) ) {
            $html .= '<li>Error fetching quote: ' . esc_html( $quote->get_error_message() ) . '</li>';
        } else {
            $html .= '<li>&ldquo;' . esc_html( $quote ) . '&rdquo;</li>';
        }
    }
    $html .= '</ul>';

    return $html;
}
add_shortcode( 'kanye_quotes', 'hs_kanye_quotes_shortcode' );

/// use this short code [kanye_quotes count="5"]

?>