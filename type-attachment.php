<rdf:Description rdf:about="<?php the_permalink_rss() ?>">
<?php $mime = get_post_mime_type(); 

if ($mime == "image/jpeg" || $mime == "image/gif"){ ?>
<rdf:type rdf:resource="http://xmlns.com/foaf/0.1/Image"/>
<?php } else { ?>
<rdf:type rdf:resource="http://xmlns.com/foaf/0.1/Document"/>
<?php } ?>
<foaf:name><?php the_title_rss() ?></foaf:name>
<?php do_action('rdf_item'); ?>
</rdf:Description>
<?php
$post_author_Array[] = $post->post_author;

$post_author_Array = array_unique($post_author_Array);

sort($post_author_Array);

?>