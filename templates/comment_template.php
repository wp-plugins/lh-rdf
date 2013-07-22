<?php

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">


<?php echo get_query_var('msds_pif_cat');

echo get_query_var('feed'); 

$foo = getRewriteRules();

print_r($foo);

?>

foovar

			</div><!-- #content -->
		</div><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>