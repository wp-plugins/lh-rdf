<rdf:Description rdf:about="<?php the_permalink_rss() ?>">
<rdf:type rdf:resource="http://xmlns.com/foaf/0.1/Agent"/>
<foaf:name><?php the_title_rss() ?></foaf:name>
<?php do_action('rdf_item'); ?>
</rdf:Description>
<?php
$post_author_Array[] = $post->post_author;

$post_author_Array = array_unique($post_author_Array);

sort($post_author_Array);

?>