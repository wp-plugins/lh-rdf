<skos:ConceptScheme rdf:about="<?php bloginfo_rss("url") ?>/#categories">
<dc:title><?php bloginfo_rss('name'); ?> Categories</dc:title>
<dc:description><?php bloginfo_rss('description') ?></dc:description>
<dc:creator><?php the_author_meta( 'nickname', '1' ); ?> </dc:creator>
<sioc:has_creator rdf:resource="<?php echo get_author_posts_url('1'); ?>" />
<dc:date><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT'), false); ?></dc:date>
<dc:language>en</dc:language>
<?php

$categories = get_categories(array(
	'parent' => 0,
) ); 

$j = 0;

while ($j < count($categories)) {


?>
<skos:hasTopConcept rdf:resource="<?php echo get_category_link($categories[$j]->cat_ID);
?>"/>
<?php

$post_taxonomy_Array[] = $categories[$j]->cat_ID;

$post_taxonomy_Array = array_unique($post_taxonomy_Array);

sort($post_taxonomy_Array);

$j++;

}

?>
</skos:ConceptScheme>
