<?php

$j = 0;
while ($j < count($post_taxonomy_Array)) {

$sql = "SELECT b.taxonomy FROM ".$wpdb->prefix."terms a, ".$wpdb->prefix."term_taxonomy b WHERE a.term_id = b.term_id AND a.term_id = '".$post_taxonomy_Array[$j]."'";

global $wpdb;

$results = $wpdb->get_results($sql);

$post_taxonomy = get_term( $post_taxonomy_Array[$j], $results[0]->taxonomy);

if ($post_taxonomy->taxonomy == "category"){

if ($_GET["lh_rdf_extend"]){

include('taxonomy-category.php');

} else {

echo "\n<rdf:Description rdf:about=\"".get_category_link($post_taxonomy->term_id)."\">\n";
echo "<rdfs:seeAlso rdf:resource=\"".get_category_link($post_taxonomy->term_id)."?feed=lhrdf\"/>\n";
echo "</rdf:Description>\n";

}

} elseif ($post_taxonomy->taxonomy == "post_tag"){

if ($_GET["lh_rdf_extend"]){

include('taxonomy-tag.php');

} else {

echo "\n<rdf:Description rdf:about=\"".get_tag_link($post_taxonomy->term_id)."\">\n";
echo "<rdfs:seeAlso rdf:resource=\"".get_tag_link($post_taxonomy->term_id)."?feed=lhrdf\"/>\n";
echo "</rdf:Description>\n";


}

}

$j++;

}

$j = 0;

while ($j < count($post_author_Array)) {

$authordata = get_userdata($post_author_Array[$j]);

if ($_GET["lh_rdf_extend"]){

include('author.php');

} else {

echo "\n<rdf:Description rdf:about=\"".get_author_posts_url($authordata->ID)."\">\n";
echo "<rdfs:seeAlso rdf:resource=\"".get_author_posts_url($authordata->ID)."?feed=lhrdf\"/>\n";
echo "</rdf:Description>\n";

}

$j++;

}

?>