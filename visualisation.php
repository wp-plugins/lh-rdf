<?php


define('WP_USE_THEMES', false);

/** Loads the WordPress Environment and Template */
include("../../../wp-blog-header.php");

?>

<!DOCTYPE html>
<html>
<head>
<title>InContext Visualization</title>
<!-- Add transparent PNG support to IE6 -->
<!-- Visualizer IE6 CSS file -->
<!--[if IE 6]>
<script type="text/javascript" src="incontext/content/iepngfix_tilebg.js"></script>
<link type="text/css" href="incontext/content/visualizer-ie6.css" rel="Stylesheet" />
<![endif]-->
</head>
<body class="custom incontext-visualization">

<h1>InContext Visualization</h1>

<div id="visualizer_canvas"></div>

<script id="lh_rdf_visualiser_options" type="text/javascript" 
data-lh_rdf_visualiser_aggregation_var="<?php bloginfo_rss("url") ?>#aggregation"
data-lh_rdf_visualiser_schemaurl_var="<?php bloginfo_rss("url") ?>/wp-content/plugins/lh-rdf/incontext/rdf_schema.php" 
data-lh_rdf_visualiser_dataurl_var="/?feed=lhrdf&lh_rdf_extend=yes" src="incontext/scripts/visualiser_init.js">
</script>
	
</body>
</html>