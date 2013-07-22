<?php

echo "\n<sioct:Tag rdf:about=\"".get_tag_link($post_taxonomy->term_id)."\">
<rdfs:label>".$post_taxonomy->name."</rdfs:label>
<foaf:name>".$post_taxonomy->name."</foaf:name>
</sioct:Tag>";

?>