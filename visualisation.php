<?php


define('WP_USE_THEMES', false);

/** Loads the WordPress Environment and Template */
include("../../../wp-blog-header.php");

?>

<html>
<head>
<title>InContext Visualization</title>
<!-- Visualizer CSS files -->
<link type="text/css" href="incontext/content/visualizer.css" media="screen" rel="Stylesheet" />
<link type="text/css" href="incontext/content/visualizer-skin.css" media="screen" rel="Stylesheet" />
<!-- Add transparent PNG support to IE6 -->
<!-- Visualizer IE6 CSS file -->
<!--[if IE 6]>
<script type="text/javascript" src="incontext/content/iepngfix_tilebg.js"></script>
<link type="text/css" href="incontext/content/visualizer-ie6.css" rel="Stylesheet" />
<![endif]-->

<!-- Visualizer script include files -->
<script type="text/javascript" src="incontext/scripts/visualizer_compiled_min.js"></script>

<!-- Visualizer example code -->
<script type="text/javascript">
// initialize the visualizer
var app = new VisualizerApp("visualizer_canvas", "<?php bloginfo_rss("url") ?>#aggregation",
{ // configuration options, see the configuration options documentation page for more information
					debug: true,
					maxWidth: 800,
					dataUrl: "/?feed=lhrdf&lh_rdf_extend=yes",
schemaUrl: "incontext/rdf_schema.php",
titleProperties: ["http://purl.org/dc/elements/1.1/title", "http://purl.org/dc/terms/title", "http://xmlns.com/foaf/0.1/name", "http://www.w3.org/2004/02/skos/core#prefLabel", "http://xmlns.com/foaf/0.1/accountName"],
					dontShowProperties: ["http://www.openarchives.org/ore/terms/isDescribedBy", "http://purl.utwente.nl/ns/escape-system.owl#resourceUri"],
					annotationTypeId: "http://purl.utwente.nl/ns/escape-annotations.owl#RelationAnnotation",
					objectAnnotationTypeId: "http://purl.utwente.nl/ns/escape-annotations.owl#object",
					subjectAnnotationTypeId: "http://purl.utwente.nl/ns/escape-annotations.owl#subject",
					descriptionAnnotationTypeId: "http://purl.org/dc/terms/description",
					imageTypeId: "http://xmlns.com/foaf/0.1/img",
					useHistoryManager: true,
					baseClassTypes: {
"http://rdfs.org/sioc/ns#Post": "publication",
"http://purl.org/spar/fabio/WebSite": "event",
"http://xmlns.com/foaf/0.1/Project": "project",
"http://purl.org/dc/dcmitype/MovingImage": "video",
"http://purl.utwente.nl/ns/escape-events.owl#Event": "event",
"http://rdfs.org/sioc/types#Tag": "topic",
"http://xmlns.com/foaf/0.1/Image": "image"
				}
			});
		</script></head>
<body class="custom incontext-visualization">

<h1>InContext Visualization</h1>

<div id="visualizer_canvas"></div>
	
</body>
</html>