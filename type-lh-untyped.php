<rdf:Description rdf:about="<?php the_permalink_rss() ?>">
<rdf:type rdf:resource="http://xmlns.com/foaf/0.1/Document"/>
<foaf:name><?php the_title_rss() ?></foaf:name>
<foaf:type><?php echo $post_type; ?></foaf:type>
<dc:abstract><?php if ($post->post_excerpt){ echo $post->post_excerpt;  } else {  echo lh_rdf_truncate($post->post_content, "120");  } ?></dc:abstract>
<?php do_action('rdf_item'); ?>
</rdf:Description>
<?php
$post_author_Array[] = $post->post_author;

$post_author_Array = array_unique($post_author_Array);

sort($post_author_Array);

?>