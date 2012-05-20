<rdf:Description rdf:about="<?php the_permalink_rss() ?>">
<rdf:type rdf:resource="http://rdfs.org/sioc/ns#Post"/>
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
</rdf:Description>

<rdf:Description rdf:about="<?php echo get_author_posts_url($post->post_author); ?>">
<sioc:creator_of rdf:resource="<?php the_permalink_rss() ?>"/>
</rdf:Description>

<?php
$post_author_Array[] = $post->post_author;

$post_author_Array = array_unique($post_author_Array);

sort($post_author_Array);

?>

