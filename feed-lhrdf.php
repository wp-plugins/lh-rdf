<?php
/**
 * SIOC RDF Feed Template.
 *
 * @package WordPress
 */



include('lib/EasyRdf.php');


if ( is_singular() ){

} else {

if ( !have_posts() ){

//echo "not found";

header("HTTP/1.0 404 Not Found"); 

}

}


if ($_GET[lhrdf]){

ob_start(); 

} else {

header('Content-Type: ' . feed_content_type('rdf') . '; charset=' . get_option('blog_charset'), true);

}

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
<?php if (function_exists('lh_relationships_add_compliant_rdf_namespace')) { 

echo "\n<rdf:RDF\n";

lh_relationships_add_compliant_rdf_namespace();

echo ">";


} else { ?>
<rdf:RDF
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:foaf="http://xmlns.com/foaf/0.1/"
	xmlns:sioc="http://rdfs.org/sioc/ns#"
	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
	xmlns:skos="http://www.w3.org/2004/02/skos/core#"
	xmlns:moat="http://moat-project.org/ns#"
	xmlns:lh="http://localhero.biz/namespace/lhero/"
 	xmlns:admin="http://webns.net/mvcb/"
 	xmlns:content="http://purl.org/rss/1.0/modules/content/"
 	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:dcterms="http://purl.org/dc/terms/"
	xmlns:sioct="http://rdfs.org/sioc/types#"
	xmlns:tag="http://www.holygoat.co.uk/owl/redwood/0.1/tags/"
	xmlns:rss="http://purl.org/rss/1.0/"
	xmlns:georss="http://www.georss.org/georss"
	xmlns:wgs84="http://www.w3.org/2003/01/geo/wgs84_pos#"
	xmlns:xfn="http://vocab.sindice.com/xfn#"
	xmlns:owl="http://www.w3.org/2002/07/owl#"
	xmlns:ore="http://www.openarchives.org/ore/terms/"
 	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
>

<?php
}

if ( is_singular() ){
?>

<foaf:Document rdf:about="<?php echo htmlspecialchars(lh_rdf_curPageURL()); ?>">
<dc:title>SIOC Post profile for <?php bloginfo_rss('name'); wp_title_rss(); ?></dc:title>
<dc:description>A SIOC profile describes the structure and contents of a weblog in a machine readable form. For more information please refer to http://sioc-project.org/.</dc:description>
<foaf:primaryTopic rdf:resource="<?php the_permalink_rss() ?>"/>
<admin:generatorAgent rdf:resource="http://localhero.biz/plugins/lh-rdf/"/>
</foaf:Document>

<?php 

if (!$post->post_type || $post->post_type == "post" ){

echo "<!-- sioc_type = post -->\n";

include('templates/type-post.php');

} elseif ($post->post_type == "page" ){

echo "<!-- page -->\n";

include('templates/type-page.php');

} elseif ($post->post_type == "attachment" ){

echo "<!-- attachment -->\n";

include('templates/type-attachment.php');

} else {

echo "<!-- untyped -->\n";

include('templates/type-lh-untyped.php');

}




} elseif (is_author()){

?>

<!-- sioc_type = author -->

<foaf:Document rdf:about="">
<dc:title>SIOC Author profile for "LocalHero Beta"</dc:title>
<dc:description>A SIOC profile describes the structure and contents of a weblog in a machine readable form. For more information please refer to http://sioc-project.org/.</dc:description>
<foaf:primaryTopic rdf:resource="<?php echo get_author_posts_url($post->post_author); ?>"/>
<admin:generatorAgent rdf:resource="http://localhero.biz/plugins/lh-rdf/"/>
</foaf:Document>
<?php
$authordata = get_userdata($post->post_author);



include('templates/author.php');


} elseif (is_category()){

$category = get_category_by_path(get_query_var('category_name'),false);

$post_taxonomy = get_term( $category->cat_ID, "category");

include('templates/taxonomy-category.php');

} elseif (is_tag()){

$tag = get_query_var('tag_id');

$tag = get_tag($tag);

$post_taxonomy = get_term( $tag->term_id, "post_tag");

include('templates/taxonomy-tag.php');

} elseif (get_query_var('msds_pif_cat')){ 


echo "<!-- comment -->\n";

include('templates/type-comment.php');


} else {


?>

<foaf:Document rdf:about="">
<rdf:type rdf:resource="http://www.openarchives.org/ore/terms/ResourceMap"/>
<ore:describes rdf:resource="<?php bloginfo_rss("url") ?>#aggregation"/>
<dc:title>Resource feed of <?php bloginfo_rss('name'); ?></dc:title>
<dcterms:created><?php $args = array(
    'numberposts'     => 1,
    'orderby'         => 'post_date',
    'order'           => 'ASC'
); 

$firstpost = get_posts( $args );

echo mysql2date('Y-m-d\TH:i:s\Z', $firstpost[0]->post_date_gmt, false); 

?></dcterms:created>
<dcterms:modified><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT'), false); ?></dcterms:modified>
<dcterms:creator rdf:resource="<?php echo get_author_posts_url('1'); ?>"/>
<dc:description>A SIOC profile describes the structure and contents of a weblog in  machine readable form. For more information please refer to http://sioc-project.org/.</dc:description>
<foaf:primaryTopic rdf:resource="<?php bloginfo_rss("url") ?>"/>
<admin:generatorAgent rdf:resource="http://localhero.biz/plugins/lh-rdf/"/>
</foaf:Document>

<rdf:Description rdf:about="<?php bloginfo_rss("url") ?>#aggregation">
<rdf:type rdf:resource="http://purl.org/info:eu-repo/semantics/EnhancedPublication"/>
<rdf:type rdf:resource="http://www.openarchives.org/ore/terms/Aggregation"/>
<dcterms:title>Agregation of <?php bloginfo_rss('name'); ?></dcterms:title>
<ore:isDescribedBy rdf:resource=""/>
<?php while (have_posts()): the_post(); ?>
<ore:aggregates rdf:resource="<?php the_permalink_rss() ?>" />
<?php endwhile;  ?>
<ore:aggregates rdf:resource="<?php bloginfo_rss("url") ?>" /> 
<ore:aggregates rdf:resource="<?php bloginfo_rss("url") ?>/#categories" />
</rdf:Description>

<rdf:Description rdf:about="<?php bloginfo_rss("url") ?>">
<rdf:type rdf:resource="http://purl.org/spar/fabio/WebSite"/>
<rdf:type rdf:resource="http://rdfs.org/sioc/ns#Site"/>
<dc:title><?php bloginfo_rss('name'); ?></dc:title>
<dc:description>Website: <?php bloginfo_rss('name'); ?></dc:description>
<sioc:link rdf:resource="http://localhero.biz/"/>
<sioc:host_of rdf:resource="<?php bloginfo_rss("url") ?>/#posts" />
<?php
$args = array(
  'public'   => true,
  '_builtin' => false
);
$output = 'objects';
$post_types = get_post_types($args,$output);
  foreach ($post_types  as $post_type ) {
if ($post_type->has_archive){
echo "<sioc:host_of rdf:resource=\"".get_bloginfo('url')."/".$post_type->has_archive."/\"  />";
}
}

$args = array(
'parent' => 0
); 
$pages = get_pages($args); 

foreach ($pages  as $page ){


echo "<sioc:host_of rdf:resource=\"".get_permalink($page->ID)."\"  />";


}





?>

</rdf:Description>

<?php

foreach ($pages  as $page ){

echo "<rdf:Description rdf:about=\"".get_permalink($page->ID)."\">
<rdfs:seeAlso rdf:resource=\"".get_permalink($page->ID)."?feed=lhrdf\"  />
</rdf:Description>";



}




 foreach ($post_types  as $post_type ) {
if ($post_type->has_archive){
echo "<sioc:Forum rdf:about=\"".get_bloginfo('url')."/".$post_type->has_archive."/\" >
<rdfs:seeAlso rdf:resource=\"".get_bloginfo('url')."/".$post_type->has_archive."/?feed=lhrdf\"/>";
?><rdfs:label><?php bloginfo_rss('name');
echo " ".$post_type->has_archive; ?></rdfs:label><?php
echo "</sioc:Forum>";
}
}
?>


 
<sioc:Forum rdf:about="<?php
if (get_query_var('post_type')){ 
$post_type = get_query_var('post_type');
echo get_post_type_archive_link($post_type)."\">";
?><rdfs:label><?php bloginfo_rss('name');
echo get_post_type_archive_link($post_type) ?></rdfs:label><?php
} else {
echo get_bloginfo('url')."/#posts\">";
?><rdfs:label><?php bloginfo_rss('name'); ?> Posts</rdfs:label><?php
}

$post_type = get_query_var('post_type');
rewind_posts(); while (have_posts()): the_post(); ?>
<sioc:container_of rdf:resource="<?php the_permalink_rss() ?>" />
<?php endwhile;  ?>
<?php 

if (!$post_type){ $post_type = "post"; }

$count_posts = wp_count_posts($post_type);

$published_posts = $count_posts->publish;

$pageNumber = (get_query_var('paged')) ? get_query_var('paged') : 1;

$per_page = get_query_var('posts_per_page');

if (( function_exists('count_posts_and_pages')) && (!is_post_type_archive())){

$published = count_posts_and_pages();

} else {

$published = $published_posts;

}

$pages =  $published / $per_page;

$pages = ceil($pages);


if ($pageNumber == '1'){

$j = 1;

while ($j <= $pages) {

if ($j == '1'){


} else {

echo "<rdfs:seeAlso rdf:resource=\"";
bloginfo('url');
echo "/page/".$j."/?feed=lhrdf";

if ($post_type != 'post'){ echo "&amp;post_type=".$post_type; }

echo "\"/>\n";
}
$j++;
}

}



?>
</sioc:Forum>

<?php 


rewind_posts(); while (have_posts()): the_post(); ?>
<?php 

if ($_GET["lh_rdf_extend"]){

if (!$post->post_type || $post->post_type == "post" ){

echo "<!-- sioc_type = post -->\n";

include('templates/type-post.php');

} elseif ($post->post_type == "page" ){

echo "<!-- page -->\n";

include('templates/type-page.php');

} elseif ($post->post_type == "attachment" ){

echo "<!-- attachment -->\n";

include('templates/type-attachment.php');

} else {

echo "<!-- untyped -->\n";

include('templates/type-lh-untyped.php');

}

} else {

?>

<rdf:Description rdf:about="<?php the_permalink_rss() ?>">
<rdfs:seeAlso rdf:resource="<?php bloginfo_rss('url'); echo "/?p=".$post->ID."&amp;feed=lhrdf";
if (get_query_var('post_type')){ echo "&amp;post_type=".get_query_var('post_type'); } ?>"/>
</rdf:Description>

<?php


}
?>
<?php endwhile; 

include('templates/concept-scheme.php');

include('templates/extended-content.php');

}

?>
</rdf:RDF><?php



if ($_GET[lhrdf]){

$out = ob_get_contents();

ob_end_clean();

$graph = new EasyRdf_Graph();
$graph->parse($out,"rdfxml");
$data = $graph->serialise($_GET[lhrdf]);

if ($_GET[lhrdf] == "json"){


header("Content-Type: application/rdf+json");


} else {

header("Content-Type:text/plain");

}

echo $data;


}


?>