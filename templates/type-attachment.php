<?php
function lh_rdf_get_image_size_links() {

	/* Set up an empty array for the links. */
	$links = array();

	/* Get the intermediate image sizes and add the full size to the array. */
	$sizes = get_intermediate_image_sizes();
        $sizes[] = 'full';
	/* Loop through each of the image sizes. */
$i = 0;
foreach ( $sizes as $size ) {

/* Get the image source, width, height, and whether it's intermediate. */
$image = wp_get_attachment_image_src( get_the_ID(), $size );

$links[$i]['url'] = $image[0];
$links[$i]['type'] = $size;

$i++;

}


return $links;

}

?>
<rdf:Description rdf:about="<?php the_permalink_rss() ?>">
<?php $mime = get_post_mime_type(); 

if (wp_attachment_is_image($post->ID)){ ?>
<rdf:type rdf:resource="http://xmlns.com/foaf/0.1/Image"/>
<?php $images = lh_rdf_get_image_size_links();

foreach ( $images as $image ) {
?>
<foaf:thumbnail rdf:resource="<?php echo $image['url']; ?>" />
<?php } 
} else { ?>
<rdf:type rdf:resource="http://xmlns.com/foaf/0.1/Document"/>
<?php } ?>
<foaf:name><?php the_title_rss() ?></foaf:name>
<dcterms:format rdf:resource="http://purl.org/NET/mediatypes/<?php echo $mime; ?>"/>
<sioc:has_creator rdf:resource="<?php echo get_author_posts_url($post->post_author); ?>" />
<?php do_action('rdf_item'); ?>
</rdf:Description>
<?php 
if ($images){
foreach ( $images as $image ) {
?>
<rdf:Description rdf:about="<?php echo $image['url']; ?>">
<rdf:type rdf:resource="http://xmlns.com/foaf/0.1/Image"/>
<rdf:type rdf:resource="http://localhero.biz/uri/localhero-namespace/<?php echo $image['type']; ?>"  />
</rdf:Description>
<?php } }?>