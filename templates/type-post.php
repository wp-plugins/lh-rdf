<rdf:Description rdf:about="<?php the_permalink_rss() ?>">
<rdf:type rdf:resource="http://rdfs.org/sioc/ns#Post"/>
<rdf:type rdf:resource="<?php echo "http://localhero.biz/uri/localhero-namespace/type_".$post->post_type; ?>"/>
<rdf:type rdf:resource="<?php $lh_format = get_post_format($post->ID);
if (!$lh_format){ $lh_format = "standard";}
echo "http://localhero.biz/uri/localhero-namespace/format_".$lh_format;
?>"/>
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
<dc:abstract><?php if ($post->post_excerpt){ echo $post->post_excerpt;  } else {  echo lh_rdf_truncate($post->post_content, "120");  } ?></dc:abstract>
<sioc:content><![CDATA[<?php echo strip_tags($post->post_content); ?>]]></sioc:content>
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

if (is_page()){

$args = array(
'parent' => $post->ID,
'child_of' => $post->ID
); 
$pages = get_pages($args); 

foreach ($pages  as $page ){


echo "<sioc:host_of rdf:resource=\"".get_permalink($page->ID)."\"  />";


}


}


?>
<sioc:has_creator rdf:resource="<?php echo get_author_posts_url($post->post_author); ?>" />
<?php
$categories = get_the_category();
$j = 0;
while ($j < count($categories)) {

echo "<sioc:topic rdf:resource=\"".get_category_link($categories[$j]->cat_ID)."\"/>\n";

$post_taxonomy_Array[] = $categories[$j]->cat_ID;

$post_taxonomy_Array = array_unique($post_taxonomy_Array);

sort($post_taxonomy_Array);


$j++;
}

$tags = get_the_tags();

if (is_array($tags)){

$tags = array_values($tags);

}

if ($tags[0]){


$j = 0;

while ($j < count($tags)) {

echo "<sioc:topic rdf:resource=\"".get_tag_link($tags[$j]->term_id)."\"/>\n";

$post_taxonomy_Array[] = $tags[$j]->term_id;

$post_taxonomy_Array = array_unique($post_taxonomy_Array);

sort($post_taxonomy_Array);


$j++;

}





$j = 0;

while ($j < count($tags)) {

echo "<tag:Tagging rdf:resource=\"".get_bloginfo('url')."/dereferencer/taxonomy/tag/".$tags[$j]->name."/".get_the_author_meta('user_nicename',$post->post_author)."\"/>\n";

$j++;

}


}

?>
<dcterms:identifier><?php echo $post->ID; ?></dcterms:identifier>
<?php  if ( has_post_thumbnail()) {
$thumbnail = get_post( get_post_thumbnail_id());
echo "<lh:post_thumbnail rdf:resource=\"".$thumbnail->guid."\"/>\n";
} ?>
<?php

$args = array( 'post_type' => 'attachment', 'numberposts' => null, 'post_status' => null, 'post_parent' => $post->ID ); 

$attachments = get_posts($args);


if ($attachments) {
foreach ($attachments as $attachment) {
echo "<sioc:attachment rdf:resource=\"".$attachment->guid."\"/>\n";
}
}

?>


<?php do_action('rdf_item'); ?>

</rdf:Description>

<?php lh_rdf_single_hook(); ?>

<?php


if ( function_exists('lh_rdf_serialize_lh_rdf_array')){

lh_rdf_serialize_lh_rdf_array($post->ID);

}


if ( function_exists('lh_relationships_return_unique_sparql_object_by_post_ID')){

$objects = lh_relationships_return_unique_sparql_object_by_post_ID($post->guid);

foreach ($objects as $object ){

$seealso = get_post($object->objectid);

echo "<rdf:Description rdf:about=\"".$seealso->guid."\">
<rdfs:seeAlso rdf:resource=\"".get_permalink($object->objectid)."?feed=lhrdf\"  />
</rdf:Description>\n\n";

}

}


$args = array( 'post_type' => 'attachment', 'numberposts' => null, 'post_status' => null, 'post_parent' => $post->ID ); 

$attachments = get_posts($args);

if ($attachments) {
foreach ($attachments as $attachment) {

echo "\n<rdf:Description rdf:about=\"".$attachment->guid."\">
<rdfs:seeAlso rdf:resource=\"".get_attachment_link( $attachment->ID)."?feed=lhrdf\"  />
</rdf:Description>\n";

}
}

if ( has_post_thumbnail()) {

$thumbnail = get_post( get_post_thumbnail_id());

if ($thumbnail->post_parent != $post->ID){

echo "\n<rdf:Description rdf:about=\"".$thumbnail->guid."\">
<rdfs:seeAlso rdf:resource=\"".get_attachment_link($thumbnail->ID)."?feed=lhrdf\"  />
</rdf:Description>\n";

}

}





?>