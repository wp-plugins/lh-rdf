
<sioc:UserAccount rdf:about="<?php echo get_author_posts_url($authordata->ID); ?>">
<foaf:accountName><?php echo $authordata->user_nicename; ?></foaf:accountName>
<sioc:name><?php echo $authordata->display_name; ?></sioc:name>
<lh:post_author><?php echo $authordata->ID; ?></lh:post_author >
</sioc:UserAccount>

