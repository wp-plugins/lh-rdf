<?php

function getRewriteRules() {
    global $wp_rewrite; // Global WP_Rewrite class object
    return $wp_rewrite->rewrite_rules(); 
} 


function add_query_vars($aVars) {
$aVars[] = "msds_pif_cat"; // represents the name of the product category as shown in the URL
return $aVars;
}
 
// hook add_query_vars function into query_vars
add_filter('query_vars', 'add_query_vars');

function add_rewrite_rules( $wp_rewrite ) {
$new_rules = array(
    'msds-pi/([^/]+)/?$' => 'index.php?msds_pif_cat='.
    $wp_rewrite->preg_index(1),
    'msds-pi/([^/]+)/feed/(feed|rdf|rss|rss2|atom|lhimagefeed|lhjsonld|lhrdf)/?$' => 'index.php?msds_pif_cat='.
    $wp_rewrite->preg_index(1).'&feed='.
    $wp_rewrite->preg_index(2)
);
// Always add your rules to the top, to make sure your rules have priority
$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

 
add_action('generate_rewrite_rules', 'add_rewrite_rules');


function custom_template_redirect() {
    global $wp_query, $post;
    if (get_query_var('msds_pif_cat')) {
if ($_GET[feed] == "lhjrdf"){




        include(LH_RDF_PLUGIN_DIR . '/feed-lhrdf.php');
  exit();

} else {


        include(LH_RDF_PLUGIN_DIR . '/templates/comment_template.php');
        exit();

}
    }
}

add_action('template_redirect', 'custom_template_redirect');


?>