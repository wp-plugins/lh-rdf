<?php
/*
Plugin Name: LH RDF
Plugin URI: http://localhero.biz/plugins/lh-rdf/
Description: Adds a semantic/SIOC RDF feed to Wordpress
Author: shawfactor
Version: 0.30
Author URI: http://shawfactor.com/

== Changelog ==

= 0.01 =
* Mapped WP relationships to SIOC triples
= 0.02 =
* Added SKOS triples
= 0.03 =
* Added Autodiscovery
= 0.04 =
* Added content negotiation
= 0.05 =
* Added custom post type support
= 0.06 =
* Fixed SIOC topics and added SIOC terms
= 0.07 =
* Added Post thumbnail support
= 0.08 =
* Fixed Bugs
= 0.09 =
* Fixed Permalinks
= 0.10 =
* Fixed Date scheme
= 0.11 =
* Fixed Critical bug
= 0.12 =
* Fixed RDF bug, added Tag feed
= 0.13 =
* Added dcterms identifier
= 0.14 =
* Added openarchives and Incontext visualisation support
= 0.15 =
* Added flag to publish extended RDF
= 0.16 =
* Fixed Auto discovery bug
= 0.17 =
* Added visualiser shortcode
= 0.18 =
* Better handling of pages
= 0.19 =
* Mbox email hash
= 0.19 =
* Mbox email hash
= 0.20 =
* Image attachment support
= 0.21 =
* Added rdf/json output using easyrdf
= 0.22 =
* Fixed Visualiser
= 0.23 =
* Bug Fix and simplification
= 0.24 =
* RDF for attachments
= 0.25 =
* Thumbnail enhancements
= 0.26 =
* Various enhancements
= 0.27 =
* DC Abstract support
= 0.28 =
* File reorganisation and datadump
= 0.29 =
* Fixed wordpress pings to allow for semantic pinging
= 0.30 =
*Fixed hard code title value


License:
Released under the GPL license
http://www.gnu.org/copyleft/gpl.html

Copyright 2011  Peter Shaw  (email : pete@localhero.biz)


This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published bythe Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('LH_RDF_PLUGIN_DIR', dirname(__FILE__));
define('LH_RDF_EASYRDF_URL', 'https://github.com/njh/easyrdf/tarball/master');

function lh_rdf_install_easyrdf(){

if (file_exists(LH_RDF_PLUGIN_DIR . '/arc/ARC2.php') || class_exists('ARC2')) {

return true;

}


if (!is_writable(LH_RDF_PLUGIN_DIR)) {

return false;

}

$sDir = getcwd();
chdir(LH_RDF_PLUGIN_DIR);

// download Easyrdf
$sTarFileName 	= 'easyrdf.tar.gz';

	$sCmd 			= 'wget --no-check-certificate -T 2 -t 1 -O ' . $sTarFileName . ' ' . LH_RDF_EASYRDF_URL . ' 2>&1';
	$aOutput 		= array();
	exec($sCmd, $aOutput, $iResult);
	if ($iResult != 0) {
		chdir($sDir);
		return false;
	}

	// untar the file
	$sCmd 		= 'tar -xvzf ' . $sTarFileName . ' 2>&1';
	$aOutput 	= array();
	exec($sCmd, $aOutput, $iResult);
	if ($iResult != 0) {
		chdir($sDir);
		return false;
	}

	// delete old arc direcotry and tar file
	@rmdir('arc');
	@unlink($sTarFileName);

	// rename the ARC2 folder to arc
	$sCmd		= 'mv semsol-arc2-* arc 2>&1';
	$aOutput 	= array();
	exec($sCmd, $aOutput, $iResult);
	if ($iResult != 0) {
		chdir($sDir);
		return false;
	}
	
	chdir($sDir);
	return true;




}

//register_activation_hook(__FILE__, 'lh_rdf_install_easyrdf' );


include('library/lh-rdf-comments.php');

include('library/function_library.php');


function LH_rdf_output_rdf_xml() {

load_template(dirname(__FILE__) . '/feed-lhrdf.php');

}


if ($_GET[feed]){

remove_filter('template_redirect', 'redirect_canonical');

}

if ($_GET[feed] == "lhrdf"){


remove_filter('the_permalink_rss', 'lh_hum_permalink');

}

function LH_rdf_add_feed() {

add_feed('lhrdf', 'LH_rdf_output_rdf_xml');

}

add_action('init', 'LH_rdf_add_feed');


function LH_rdf_override_the_permalink($permalink) {

global $post;

$permalink_replacement = $post->guid;

return $permalink_replacement;

}

add_filter('the_permalink_rss', 'LH_rdf_override_the_permalink');


function LH_rdf_override_the_excerpt($permalink) {

global $post;

$permalink_replacement = $post->post_excerpt;

return $permalink_replacement;

}

add_filter('the_excerpt_rss', 'LH_rdf_override_the_excerpt');


// ************* Get control from WordPress *************

function LH_rdf_get_link() {
 
global $post;
	
if ( is_singular() ){

$base_mid = get_permalink()."?feed=lhrdf";


} elseif (is_author()){

$base_mid = get_author_posts_url($post->post_author);

$base_mid .= "?feed=lhrdf";

} else { 

$base_mid = "http://$_SERVER[HTTP_HOST]";

$base_mid .= "/";

$base_mid .= "?feed=lhrdf";


}


return $base_mid;

}



function LH_rdf_is_rdf_request() {
	if ( $_SERVER['HTTP_ACCEPT'] == 'application/rdf+xml' ) {
		return true;
	}
	return false;
}



function LH_rdf_get_control() {

if (!is_feed()){

if ( LH_rdf_is_rdf_request() ) {

if (!$_GET["lh_rdf_contentfilter"]){

$redir = LH_rdf_get_link();

if ( !empty($redir) ) {
@header( "Location: " .  LH_rdf_get_link() );
die();
}

}

}

} 
	
}


add_action('template_redirect', 'LH_rdf_get_control');


// Add sioc_link function to execute during a page HEAD section
function lh_rdf_sioc_link() {
	global $posts;
	
	if ( is_feed() ){



	} elseif (is_category()){



	} else {

	// Form the template link to SIOC metadata
	$base_url = '<link rel="meta" type="application/rdf+xml" title="SIOC" href="';
	$base_end = '" />' . "\n\n";
		# TODO - Use WP_Rewrite to get correct pathname
	$base_mid = LH_rdf_get_link();

	if ( !empty($base_mid) ){
		echo $base_url . $base_mid . $base_end;
	}
	}
}

add_action('wp_head', 'lh_rdf_sioc_link');


// Add an rdf sitemap
function lh_rdf_sitemap_link() {
echo "<link rel=\"sitemap\" type=\"application/rdf+xml\" title=\"SIOC\" href=\"".plugins_url()."/lh-rdf/index.rdf\" />";
}

add_action('wp_head', 'lh_rdf_sitemap_link');









function lh_rdf_print_incontext_visualiser(){

?>

<!-- Visualizer CSS files -->
<link type="text/css" href="<?php echo plugins_url( '' , __FILE__ );  ?>/context/visualizer.css" media="screen" rel="Stylesheet" /> 
<link type="text/css" href="<?php echo plugins_url( '' , __FILE__ );  ?>/context/visualizer-skin.css" media="screen" rel="Stylesheet" /> 

<script type="text/javascript" src="<?php echo plugins_url( '' , __FILE__ );  ?>/context/visualizer_compiled_min.js"></script>


<?php

}


function lh_rdf_incontext_visualiser_short_func( $atts ) {
	extract( shortcode_atts( array(
		'foo' => 'something',
		'bar' => 'something else',
	), $atts ) );

add_action('wp_footer', 'lh_rdf_print_incontext_visualiser');


return "<div id=\"visualizer_canvas\"
data-lh_rdf_visualiser_aggregation_var=\"".get_bloginfo('url')."#aggregation\"
data-lh_rdf_visualiser_schemaurl_var=\"".plugins_url( '' , __FILE__ )."/rdf_schema.php?lhrdf=json\" 
data-lh_rdf_visualiser_dataurl_var=\"".get_bloginfo('url')."/?feed=lhrdf&lh_rdf_extend=yes&lhrdf=json\" 
></div>";

}

add_shortcode( 'lh_rdf_incontext_visualiser_short', 'lh_rdf_incontext_visualiser_short_func' );

function lh_rdf_single_hook(){

do_action('lh_rdf_single_hook');

}

remove_action("publish_post", "generic_ping");



function lh_rdf_weblog_ping($server = '', $permalink = false, $path = '') { 

global $wp_version; 

include_once(ABSPATH . WPINC . '/class-IXR.php'); 
include_once(ABSPATH . WPINC . '/class-wp-http-ixr-client.php'); 

// using a timeout of 3 seconds should be enough to cover slow servers 

$client = new WP_HTTP_IXR_Client($server, ((!strlen(trim($path)) || ('/' == $path)) ? false : $path)); 

$client->timeout = 3; 
$client->useragent .= ' -- WordPress/'.$wp_version; 

// when set to true, this outputs debug messages by itself 

$client->debug = false; 

$home = trailingslashit( home_url() ); 

// the extendedPing format should be "blog name", "blog url", "check url" (post url), and "feed url",


if ( !$client->query('weblogUpdates.extendedPing', get_option('blogname'), $home, $permalink, get_bloginfo('rss2_url') ) ){

$client->query('weblogUpdates.ping', get_option('blogname'), $permalink."?feed=lhrdf");
}

} 


function lh_rdf_generic_ping($post_id = 0) { 
$services = get_option('ping_sites');
$services = explode("\n", $services);

foreach ( (array) $services as $service ) { 

$service = trim($service);

if ( '' != $service ){

$permalink = get_permalink($post_id);

lh_rdf_weblog_ping($service,$permalink);

} 

}

return $post_id;

}

add_action("publish_post", "lh_rdf_generic_ping");

add_action("publish_page", "lh_rdf_generic_ping");





?>