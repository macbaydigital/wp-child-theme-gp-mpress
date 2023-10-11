<?php
/**
 * GeneratePress child theme functions and definitions.
 **/

// Add custom taxonomy 'PageCat' for pages
function pages_tax(){
    register_taxonomy(
        "pagecat",
        "page",
        array(
            "hierarchical" => true, // Display tax as checkbox in Quick-Edit
            "label" => "PageCat",
            "rewrite" => array("slug" => "pagecat"),
            'show_in_rest' => true, // Display tax in Block-Editor
        )
    );
}
add_action("init", "pages_tax");

// Add PageCat column to pages-dashboard
function pages_tax_columns($columns){
    $new_columns = array(
        "cb" => "<input type='checkbox' />",
        "title" => "Title",
        "pagecat" => "PageCat"
    );
    return array_merge($columns, $new_columns);
}
add_filter("manage_edit-page_columns", "pages_tax_columns");

// Add values for PageCat column to pages-dashboard
function pages_tax_column($column, $post_id){
    if($column == "pagecat"){
        $terms = get_the_term_list($post_id, "pagecat", "", ",", "");
        if(is_string($terms)){
            echo $terms;
        } else {
            _e("No PageCat Assigned", "text-domain");
        }
    }
}
add_action("manage_page_posts_custom_column", "pages_tax_column", 10, 2);

// Add sorting by PageCat column
function pages_tax_sortable_columns($columns){
    $columns["pagecat"] = "pagecat";
    return $columns;
}
add_filter("manage_edit-page_sortable_columns", "pages_tax_sortable_columns");

// Add filter dropdown for PageCat column
function pages_tax_filter_dropdown(){
    global $typenow;
    if($typenow == "page"){
        $taxonomy = "pagecat";
        $current_taxonomy = isset($_GET["pagecat"]) ? $_GET["pagecat"] : "";
        $terms = get_terms($taxonomy);
        if(count($terms) > 0){
            echo "<select name='pagecat' id='dropdown_pagecat'>";
            echo "<option value=''>All PageCat</option>";
            foreach($terms as $term){
                $selected = ($term->slug == $current_taxonomy) ? " selected='selected'" : "";
                echo "<option value='" . $term->slug . "'" . $selected . ">" . $term->name . "</option>";
            }
            echo "</select>";
        }
    }
}
add_action("restrict_manage_posts", "pages_tax_filter_dropdown");

// Filter posts by PageCat
function pages_tax_filter_posts($query){
    global $pagenow;
    $type = "page";
    $taxonomy = "pagecat";
    if($pagenow == "edit.php" && $query->is_main_query() && isset($_GET["pagecat"]) && $_GET["pagecat"] != ""){
        $term = $_GET["pagecat"];
        $query->query_vars["tax_query"][] = array(
            "taxonomy" => $taxonomy,
            "field" => "slug",
            "terms" => array($term)
        );
    }
}
add_filter("parse_query", "pages_tax_filter_posts");

// GP Smooth Scrolling GLOBALLY - Need to activate in Customizer
add_filter( 'generate_smooth_scroll_elements', function( $elements ) {
  $elements[] = 'a[href*="#"]';
  return $elements;
} );

// GB Prevent redirect to GB-Dashboard after re-activation
add_filter( 'generateblocks_do_activation_redirect', '__return_false' );

// v.0.8.0

// Impressum-Link zum Privacy Policy Link auf Login-Seite hinzufügen (wp-login.php).
add_filter( 'the_privacy_policy_link', function( $link, $privacy_policy_url ) {

	return $link . '&nbsp; | &nbsp;<a href="/impressum/">Impressum</a>';

}, 10, 2 );

// Create shortcode [gp_nav] for Header-Navigation - from https://snippets.tdrayson.com/oskkjp
add_shortcode( 'gp_nav', 'tct_gp_nav' );
function tct_gp_nav( $atts ) {
    ob_start();
    generate_navigation_position();
    return ob_get_clean();

/**
* Enqueue Child Theme style.css to editor
* Credit to https://theadminbar.com/generate-youtube-bonus
**/
add_filter('block_editor_settings_all', function($editor_settings) {
    // Get the URL of the child theme's style.css
    $child_theme_style_url = get_stylesheet_directory_uri() . '/style.css';

    $editor_settings['styles'][] = array('css' => wp_remote_get($child_theme_style_url)['body']);
    return $editor_settings;
});
/* Enqueue Customizer CSS to editor */ 
add_filter( 'block_editor_settings_all', function( $editor_settings ) {
    $css = wp_get_custom_css_post()->post_content;
    $editor_settings['styles'][] = array( 'css' => $css );
    return $editor_settings;
} );
}
