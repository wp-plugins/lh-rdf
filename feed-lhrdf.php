<?php
/**
 * SIOC RDF Feed Template.
 *
 * @package WordPress
 */

/**
 * get the flickr ID of an image
 **/	

function lh_getImageID($input){
	
	if (preg_match('/flickr\.com\/photos\/.*\/(\d+)/', $input, $img_id)){
		return $img_id[1];
		}
	if(preg_match('/flickr\.com\/photos\/.*\/(\d+)\/.*/', $input, $img_id)){
		return $img_id[1];
		}
	if(preg_match('/static\.flickr\.com\/.*\/(\d+)_.*/', $input, $img_id)){
		return $img_id[1];
		}
	if (preg_match('/(\d+)/', $input, $img_id)){
		return $img_id[0];
	}
	else{
		//echo "no img id can be found for ".$input;	
		return -1;
	}
}



/**
 * returns flickr photopage of an flickr image
 **/	
	
function lh_getPhotopage($src){	
	global $flickrKey;
		# find out if input src was already the photopage 
		preg_match('/flickr\.com\/photos\/.*\/(\d+)/', $src, $img_id);
		if($img_id[1])
			return $src;
		
		#  if not get id from url
		$id = lh_getImageID($src);
		
		# call flickr API to get photopage belonging to id 
		if($id != -1){
			$params = array(
				'api_key'	=> $flickrKey,
				'method'	=> 'flickr.photos.getInfo',
				'photo_id'	=> $id,
				'format'	=> 'php_serial',
			);
			$encoded_params = array();
			foreach ($params as $k => $v){
				$encoded_params[] = urlencode($k).'='.urlencode($v);
			}
							
			$url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);
			$rsp = file_get_contents($url);
			$rsp_obj = unserialize($rsp);
			if ($rsp_obj['stat'] == 'ok'){
				$photo_urls = $rsp_obj['photo']['urls'];
				foreach ($photo_urls as $i => $v){
					if($v[0]['type'] == 'photopage')
						return $v[0]['_content'];
				}
			}
			//else{ echo "flickr API call failed";}	
		}
}




/**
 * extracts images from post content
 **/

function lh_extractImages($content, $uri) {
		if ( false === $content )
				return '';
		$host = parse_url($uri);
		$pattern = '/<img ([^>]*)src=(\"|\')([^<>]+?\.(png|jpeg|jpg|jpe|gif))[^<>\'\"]*(\2)([^>\/]*)\/*>/is';
		preg_match_all($pattern, $content, $matches);
		if (empty($matches[0]) )
				return '';
		$sources = array();
			
		foreach ($matches[3] as $src) {
				// if no http in url
				if(strpos($src, 'http') === false){
					// if it doesn't have a relative uri
					if( strpos($src, '../') === false && strpos($src, './') === false && strpos($src, '/') === 0)
						$src = 'http://'.str_replace('//','/', $host['host'].'/'.$src);
					else
						$src = 'http://'.str_replace('//','/', $host['host'].'/'.dirname($host['path']).'/'.$src);
				}	
				//if the pic comes from flickr
				if(preg_match('/flickr\.com/i', $src)){
					$flickr_img_page = "";
					$flickr_img_page = lh_getPhotopage($src);
					if($flickr_img_page != ""){
						$flickr2rdf='http://www.kanzaki.com/works/2005/imgdsc/flickr2rdf?u='.$flickr_img_page;
						$rdf .= "\n\t" . '<sioc:embeds><foaf:Image rdf:about="'.clean_url($src) .'"><rdfs:seeAlso rdf:resource="'.$flickr2rdf.'" /></foaf:Image></sioc:embeds>';
					}
				}
				else
					$rdf .= "\n\t" . '<sioc:embeds foaf:Image="' . clean_url($src) . '"/>';
				}
			return $rdf;
}



/**
 * extracts links from post content
 **/

function lh_extractLinks( $html ) {
    $rdf = '';
    preg_match_all ('/<a\b([^>]+)>(.*?)<\/a>/ims', $html, $out, PREG_SET_ORDER);
    foreach ($out as $val) {
        if ( preg_match ( '/href\s*=\s*"([^"]*)"/ims', $val[1], $anchor ) ) {
            if ( preg_match( '/type\s*=\s*"application\/rdf\+xml/i', $val[1]) ) {
                $rdf .= "\n\t" . '<rdfs:seeAlso rdf:resource="' . wp_specialchars(trim($anchor[1]),1) . '" rdfs:label="' . apply_filters( 'the_title_rss', apply_filters( 'the_title', wp_specialchars($val[2],1))) . '"/>';
            } else {
                $rdf .= "\n\t" . '<sioc:links_to rdf:resource="' . wp_specialchars(trim($anchor[1]),1) . '" rdfs:label="' . apply_filters( 'the_title_rss', apply_filters( 'the_title', wp_specialchars($val[2],1))) . '"/>';
            }
        }
    }
    return $rdf;
}



if ( is_singular() ){

} elseif (is_author()){

} elseif (is_category()){

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

<sioc:Post rdf:about="<?php the_permalink_rss() ?>">
<sioc:link rdf:resource="<?php the_permalink_rss() ?>"/>
<sioc:has_container rdf:resource="<?php
if (get_query_var('post_type')){ 
$post_type = get_query_var('post_type');
echo get_post_type_archive_link( $post_type );
} else {
bloginfo_rss("url");
echo "/#posts";
}
?>"/>
<dc:title><?php the_title_rss() ?></dc:title>
<dc:date><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT'), false); ?></dc:date>
<dcterms:created><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></dcterms:created>
<sioc:content><![CDATA[<?php the_excerpt_rss() ?>]]></sioc:content>
<?php if ( strlen( $post->post_content ) > 0 ){ ?>
<content:encoded><![CDATA[<?php $content = apply_filters('the_content', $post->post_content);
echo $content;
 ?>]]></content:encoded>
<?php } else  { ?>
<content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
<?php } 
$extract = lh_extractLinks($post->post_content);
echo $extract;
$post_uri = htmlspecialchars(get_permalink() );
$extract = lh_extractImages($post->post_content, $post_uri);
echo $extract;

?>


<sioc:has_creator>
<sioc:User rdf:about="<?php echo get_author_posts_url($post->post_author); ?>" rdfs:label="<?php echo get_author_name($post->post_author); ?>">
<rdfs:seeAlso rdf:resource="<?php echo get_author_posts_url($post->post_author); ?>?feed=lhrdf"/>
</sioc:User>
</sioc:has_creator>

<?php
$categories = get_the_category();
$j = 0;
while ($j < count($categories)) {

echo "\n<sioc:topic>
<sioct:Category rdfs:label=\"".$categories[$j]->category_nicename."\" rdf:about=\"".get_category_link($categories[$j]->cat_ID)."\">
<rdfs:seeAlso rdf:resource=\"".get_category_link($categories[$j]->cat_ID)."?feed=lhrdf\"/>
</sioct:Category>
</sioc:topic>\n";

$j++;
}

$tags = get_the_tags();

if (is_array($tags)){

$tags = array_values($tags);

}

if ($tags[0]){


$j = 0;

while ($j < count($tags)) {

echo "\n<sioc:topic>
<sioct:tag rdfs:label=\"".$tags[$j]->name."\" rdf:about=\"".get_tag_link($tags[$j]->term_id)."\">\n";

echo "<rdfs:seeAlso rdf:resource=\"".get_tag_link($tags[$j]->term_id)."?feed=lhrdf\"/>\n";


echo "</sioct:tag>
</sioc:topic>\n\n";

$j++;

}





$j = 0;

while ($j < count($tags)) {

echo "<tag:RestrictedTagging>\n<tag:taggedResource rdf:about=\"";

the_permalink_rss();

echo "\">\n";

echo "<tag:associatedTag rdf:resource=\"".get_tag_link($tags[$j]->term_id)."\"/>\n";

echo "<foaf:maker rdf:resource=\"".get_author_posts_url($post->post_author)."\"/>\n";

if (function_exists('lhrdf_register_activation_hook')){

echo "<moat:tagMeaning rdf:resource=\"".get_bloginfo('url')."/dereferencer/taxonomy/tag/".$tags[$j]->name."/".get_the_author_meta('user_nicename',$post->post_author)."\"/>\n";

}

echo "</tag:taggedResource>\n</tag:RestrictedTagging>\n";

$j++;

}


}

?>
<dcterms:identifier><?php echo $post->ID; ?></dcterms:identifier>
<lh:post_type rdf:resource="<?php echo "http://codex.wordpress.org/Post_Types#".$post->post_type; ?>"/>
<?php  if ( has_post_thumbnail()) {
$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');
 ?>
<lh:post_thumbnail rdf:resource="<?php echo $large_image_url[0]; ?>"/>
<?php } ?>
<lh:Post_Formats rdf:resource="<?php $lh_format = get_post_format($post->ID);
if (!$lh_format){ $lh_format = "standard";}
echo "http://codex.wordpress.org/Post_Formats#".$lh_format;
?>"/>
<?php do_action('rdf_item'); ?>

</sioc:Post>
<?php

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
?>

<foaf:Person rdf:about="<?php echo get_author_posts_url($post->post_author); ?>#foaf">
<foaf:mbox_sha1sum><?php if (function_exists('sha1')){
$sha1 = sha1('mailto:' . $authordata->user_email);
} else if (function_exists('mhash')){
$sha1 = bin2hex(mhash(MHASH_SHA1, 'mailto:' . $authordata->user_email));
}
echo $sha1; ?></foaf:mbox_sha1sum>
<foaf:homepage rdf:resource="<?php echo $authordata->user_url; ?>"/>
<foaf:holdsAccount rdf:resource="<?php echo get_author_posts_url($post->post_author); ?>"/>
</foaf:Person>

<sioc:User rdf:about="<?php echo get_author_posts_url($post->post_author); ?>">
<foaf:accountName><?php echo $authordata->user_nicename; ?></foaf:accountName>
<sioc:name><?php echo $authordata->display_name; ?></sioc:name>
<lh:post_author><?php echo $post->post_author; ?></lh:post_author >

</sioc:User>


<?php


} elseif (is_category()){


$category = get_category_by_path(get_query_var('category_name'),false);

//print_r($category);


echo "\n<skos:Concept rdf:about=\"".get_category_link($category->cat_ID)."\"><skos:prefLabel xml:lang=\"en\">".$category->name."</skos:prefLabel><skos:scopeNote>".$category->category_description."</skos:scopeNote>";

$subcategories = get_categories('parent='.$category->cat_ID); 

$i = 0;

while ($i < count($subcategories)) {

echo "<skos:narrower>
<skos:Concept rdf:about=\"".get_category_link($subcategories[$i]->cat_ID)."\"><rdfs:seeAlso rdf:resource=\"".get_category_link($subcategories[$i]->cat_ID)."?feed=lhrdf\"/></skos:Concept>
</skos:narrower>";


$i++;

}

if ($category->category_parent){

echo "<skos:broader><skos:Concept rdf:about=\"".get_category_link($category->category_parent)."\"><rdfs:seeAlso rdf:resource=\"".get_category_link($category->category_parent)."?feed=lhrdf\"/></skos:Concept></skos:broader>";

}

?>

<skos:inScheme rdf:resource="<?php bloginfo_rss("url") ?>/#categories"/>

<?php

echo "</skos:Concept>\n";

} elseif (is_tag()){

$tag = get_query_var('tag_id');

$tag = get_tag($tag);

?>


<moat:Tag rdf:about="<?php echo get_tag_link($tag->term_id); ?>">
<moat:name><?php echo $tag->name; ?></moat:name>
</moat:Tag>


<?php

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

if (!$post_type || $post_type == "post" || $post_type == "page" ){

include('type-post.php');

} elseif ($post_type == "lh-place"){

include('type-lh-place.php');

}


?>
<?php endwhile;  ?>



<skos:ConceptScheme rdf:about="<?php bloginfo_rss("url") ?>/#categories">
<dc:title><?php bloginfo_rss('name'); ?></dc:title>
<dc:description><?php bloginfo_rss('description') ?></dc:description>
<dc:creator><?php the_author_meta( 'nickname', '1' ); ?> </dc:creator>
<dc:date><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT'), false); ?></dc:date>
<dc:language>en</dc:language>

<?php

$categories = get_categories(array(
	'parent' => 0,
) ); 

$j = 0;

while ($j < count($categories)) {


?>

<skos:hasTopConcept>
<skos:Concept rdf:about="<?php echo get_category_link($categories[$j]->cat_ID);
?>"><rdfs:seeAlso rdf:resource="<?php echo get_category_link($categories[$j]->cat_ID);
?>?feed=lhrdf"/></skos:Concept></skos:hasTopConcept>

<?php

$j++;

}

?>

</skos:ConceptScheme>

<?php


}

?>
</rdf:RDF>