<?php
/*
Plugin Name: LH RDF
Plugin URI: http://localhero.biz/plugins/lh-rdf/
Description: Adds a semantic/SIOC RDF feed to Wordpress
Author: shawfactor
Version: 0.0.2
Author URI: http://shawfactor.com/

== Changelog ==

= 0.01 =
* Mapped WP relationships to SIOC triples
= 0.02 =
* Added SKOS triples


License:
Released under the GPL license
http://www.gnu.org/copyleft/gpl.html

Copyright 2011  Peter Shaw  (email : pete@localhero.biz)


This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published bythe Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function LH_rdf_output_rdf_xml() {

load_template(dirname(__FILE__) . '/feed-lhrdf.php');

}



function LH_rdf_add_feed() {

add_feed('lhrdf', 'LH_rdf_output_rdf_xml');

}



add_action('init', 'LH_rdf_add_feed');

// ************* Get control from WordPress *************

function LH_rdf_get_link() {
 
global $post;
	
$base_mid = "http://$_SERVER[HTTP_HOST]";

$base_mid .= "?feed=lhrdf";

if ( is_singular() ){

$base_mid .= "&p=".$post->ID;

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

$redir = LH_rdf_get_link();

if ( !empty($redir) ) {
@header( "Location: " .  LH_rdf_get_link() );
die();
}

}

} 
	
}


add_action('template_redirect', 'LH_rdf_get_control');



function LH_rdf_sioc_link() {
	global $posts;
	
	if ( is_feed() ) return;

	// Form the template link to SIOC metadata
	$base_url = '<link rel="meta" type="application/rdf+xml" title="SIOC" href="';
	$base_end = '" />' . "\n\n";
		# TODO - Use WP_Rewrite to get correct pathname
	$base_mid = LH_rdf_get_link();

	if ( !empty($base_mid) ){
		echo $base_url . $base_mid . $base_end;
	}
}


// Add sioc_link function to execute during a page HEAD section
add_action('wp_head', 'LH_rdf_sioc_link');


?>