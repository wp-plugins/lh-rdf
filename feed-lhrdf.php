<?php
/**
 * SIOC RDF Feed Template.
 *
 * @package WordPress
 */



include('function_library.php');



if ( is_singular() ){

} else {

if ( !have_posts() ){

//echo "not found";

header("HTTP/1.0 404 Not Found"); 

}

}




header('Content-Type: ' . feed_content_type('rdf') . '; charset=' . get_option('blog_charset'), true);

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
<?php if (function_exists('LH_relationships_add_compliant_rdf_namespace')) { 

echo "\n<rdf:RDF\n";

LH_relationships_add_compliant_rdf_namespace();

echo ">";


} else { ?>
<rdf:RDF
	xmlns:rss="http://purl.org/rss/1.0/"
 	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
 	xmlns:dc="http://purl.org/dc/elements/1.1/"
 	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
 	xmlns:admin="http://webns.net/mvcb/"
 	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:lh="http://localhero.biz/namespace/lhero/"
	xmlns:skos="http://www.w3.org/2004/02/skos/core#"
	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
	xmlns:sioc="http://rdfs.org/sioc/ns#"
	xmlns:tag="http://www.holygoat.co.uk/owl/redwood/0.1/tags/"
	xmlns:moat="http://moat-project.org/ns#"
	xmlns:foaf="http://xmlns.com/foaf/0.1/"
	xmlns:dcterms="http://purl.org/dc/terms/"
	xmlns:sioct="http://rdfs.org/sioc/types#"
	<?php do_action('rdf_ns'); ?>
>

<?php
}

if ( is_singular() ){
?>

<!-- sioc_type = post -->


<foaf:Document rdf:about="">
<dc:title>SIOC Post profile for "LocalHero Beta"</dc:title>
<dc:description>A SIOC profile describes the structure and contents of a weblog in a machine readable form. For more information please refer to http://sioc-project.org/.</dc:description>
<foaf:primaryTopic rdf:resource="<?php the_permalink_rss() ?>"/>
<admin:generatorAgent rdf:resource="http://localhero.biz/plugins/lh-rdf/"/>
</foaf:Document>

<?php 

if (!$post_type || $post_type == "post" || $post_type == "page" ){

include('type-post.php');

} elseif ($post_type == "lh-place"){

include('type-lh-place.php');

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



include('author.php');


} elseif (is_category()){

$category = get_category_by_path(get_query_var('category_name'),false);

$post_taxonomy = get_term( $category->cat_ID, "category");

include('taxonomy-category.php');

} elseif (is_tag()){

$tag = get_query_var('tag_id');

$tag = get_tag($tag);

$post_taxonomy = get_term( $tag->term_id, "post_tag");

include('taxonomy-tag.php');

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
<sioc:host_of rdf:resource="<?php bloginfo_rss("url") ?>/#posts"/>
<?php
$args = array(
  'public'   => true,
  '_builtin' => false
);
$output = 'objects';
$post_types = get_post_types($args,$output);
  foreach ($post_types  as $post_type ) {
if ($post_type->has_archive){
echo "<sioc:host_of rdf:resource=\"".get_bloginfo('url')."/".$post_type->has_archive."/\" />";
}
}
?>

</rdf:Description>

<?php
 foreach ($post_types  as $post_type ) {
if ($post_type->has_archive){
echo "<sioc:Forum rdf:about=\"".get_bloginfo('url')."/".$post_type->has_archive."/\" >
<rdfs:seeAlso rdf:resource=\"".get_bloginfo('url')."/".$post_type->has_archive."/?feed=lhrdf\"/>
</sioc:Forum>";
}
}
?>


 
<sioc:Forum rdf:about="<?php
if (get_query_var('post_type')){ 
$post_type = get_query_var('post_type');
echo get_post_type_archive_link($post_type); 
} else {
echo get_bloginfo('url')."/#posts";
}

?>">
<?php 
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

if (!$post_type || $post_type == "post" || $post_type == "page" ){

include('type-post.php');

} elseif ($post_type == "lh-place"){

include('type-lh-place.php');

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

include('concept-scheme.php');

include('extended-content.php');

}

?>
</rdf:RDF>