<?php


echo "\n<skos:Concept rdf:about=\"".get_category_link($post_taxonomy->term_id)."\"><skos:prefLabel xml:lang=\"en\">".$post_taxonomy->name."</skos:prefLabel><skos:scopeNote>".$post_taxonomy->description."</skos:scopeNote>";

if ($post_taxonomy->parent != "0"){

echo "<skos:broader><skos:Concept rdf:about=\"".get_category_link($post_taxonomy->parent)."\"><rdfs:seeAlso rdf:resource=\"".get_category_link($post_taxonomy->parent)."?feed=lhrdf\"/></skos:Concept></skos:broader>";


}

$subcategories = get_categories('parent='.$post_taxonomy->term_id); 

$i = 0;

while ($i < count($subcategories)) {

echo "<skos:narrower>
<skos:Concept rdf:about=\"".get_category_link($subcategories[$i]->cat_ID)."\"><rdfs:seeAlso rdf:resource=\"".get_category_link($subcategories[$i]->cat_ID)."?feed=lhrdf\"/></skos:Concept>
</skos:narrower>";


$i++;

}
?>

<skos:inScheme rdf:resource="<?php bloginfo_rss("url") ?>/#categories"/>

<?php

echo "</skos:Concept>";
?>