<?php

/**
 * get the flickr ID of an image
 **/	

include('lib/EasyRdf.php');


function lh_rdf_getImageID($input){
	
	if (preg_match('/flickr\.com\/photos\/.*\/(\d+)/', $input, $img_id)){
		return $img_id[1];
		}
	if(preg_match('/flickr\.com\/photos\/.*\/(\d+)\/.*/', $input, $img_id)){
		return $img_id[1];
		}
	if(preg_match('/static\.flickr\.com\/.*\/(\d+)_.*/', $input, $img_id)){
		return $img_id[1];
		}
	if (preg_match('/(\d+)/', $input, $img_id)){
		return $img_id[0];
	}
	else{
		//echo "no img id can be found for ".$input;	
		return -1;
	}
}



/**
 * returns flickr photopage of an flickr image
 **/	
	
function lh_getPhotopage($src){	
	global $flickrKey;
		# find out if input src was already the photopage 
		preg_match('/flickr\.com\/photos\/.*\/(\d+)/', $src, $img_id);
		if($img_id[1])
			return $src;
		
		#  if not get id from url
		$id = lh_rdf_getImageID($src);
		
		# call flickr API to get photopage belonging to id 
		if($id != -1){
			$params = array(
				'api_key'	=> $flickrKey,
				'method'	=> 'flickr.photos.getInfo',
				'photo_id'	=> $id,
				'format'	=> 'php_serial',
			);
			$encoded_params = array();
			foreach ($params as $k => $v){
				$encoded_params[] = urlencode($k).'='.urlencode($v);
			}
							
			$url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);
			$rsp = file_get_contents($url);
			$rsp_obj = unserialize($rsp);
			if ($rsp_obj['stat'] == 'ok'){
				$photo_urls = $rsp_obj['photo']['urls'];
				foreach ($photo_urls as $i => $v){
					if($v[0]['type'] == 'photopage')
						return $v[0]['_content'];
				}
			}
			//else{ echo "flickr API call failed";}	
		}
}




/**
 * extracts images from post content
 **/

function lh_extractImages($content, $uri) {
		if ( false === $content )
				return '';
		$host = parse_url($uri);
		$pattern = '/<img ([^>]*)src=(\"|\')([^<>]+?\.(png|jpeg|jpg|jpe|gif))[^<>\'\"]*(\2)([^>\/]*)\/*>/is';
		preg_match_all($pattern, $content, $matches);
		if (empty($matches[0]) )
				return '';
		$sources = array();
			
		foreach ($matches[3] as $src) {
				// if no http in url
				if(strpos($src, 'http') === false){
					// if it doesn't have a relative uri
					if( strpos($src, '../') === false && strpos($src, './') === false && strpos($src, '/') === 0)
						$src = 'http://'.str_replace('//','/', $host['host'].'/'.$src);
					else
						$src = 'http://'.str_replace('//','/', $host['host'].'/'.dirname($host['path']).'/'.$src);
				}	
				//if the pic comes from flickr
				if(preg_match('/flickr\.com/i', $src)){
					$flickr_img_page = "";
					$flickr_img_page = lh_getPhotopage($src);
					if($flickr_img_page != ""){
						$flickr2rdf='http://www.kanzaki.com/works/2005/imgdsc/flickr2rdf?u='.$flickr_img_page;
						$rdf .= "\n\t" . '<sioc:embeds><foaf:Image rdf:about="'.clean_url($src) .'"><rdfs:seeAlso rdf:resource="'.$flickr2rdf.'" /></foaf:Image></sioc:embeds>';
					}
				}
				else
					$rdf .= "\n\t" . '<sioc:embeds rdf:resource="' . clean_url($src) . '"/>';
				}
			return $rdf;
}



/**
 * extracts links from post content
 **/

function lh_extractLinks( $html ) {
    $rdf = '';
    preg_match_all ('/<a\b([^>]+)>(.*?)<\/a>/ims', $html, $out, PREG_SET_ORDER);
    foreach ($out as $val) {
        if ( preg_match ( '/href\s*=\s*"([^"]*)"/ims', $val[1], $anchor ) ) {
            if ( preg_match( '/type\s*=\s*"application\/rdf\+xml/i', $val[1]) ) {
                $rdf .= "\n\t" . '<rdfs:seeAlso rdf:resource="' . wp_specialchars(trim($anchor[1]),1) . '" rdfs:label="' . apply_filters( 'the_title_rss', apply_filters( 'the_title', wp_specialchars($val[2],1))) . '"/>';
            } else {
                $rdf .= '<sioc:links_to rdf:resource="' . wp_specialchars(trim($anchor[1]),1) . '"/>';
$rdf .= "\n";
            }
        }
    }
    return $rdf;
}

function lh_rdf_curPageURL(){
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function lh_rdf_get_email_sha1($email) {
	# Based on get_foaf_output_email_property by Morten Frederiksen
	
	$sha1 = '';
	# Try to calculate SHA1 hash of email URI.
	if (function_exists('sha1'))
		$sha1 = sha1('mailto:' . $email);
	else if (function_exists('mhash'))
		$sha1 = bin2hex(mhash(MHASH_SHA1, 'mailto:' . $email));
	
	if ('' != $sha1 )
			return $sha1;
	else
			return '';
}

function lh_rdf_serialize_lh_rdf_array($postid) {

//echo "postid is".$postid;

$rdfarray = get_post_meta($postid, "lh_rdf_array", true);



//print_r($rdfarray);

if ($rdfarray != ""){

if ( function_exists('lh_relationships_return_compliant_namespace')){


$lhrdfnamespaces = lh_relationships_return_compliant_namespace();


$j = 0;

while ($j < count($lhrdfnamespaces)) {

$prefix = $lhrdfnamespaces[$j]->prefix;


$namespaces[$prefix] = $lhrdfnamespaces[$j]->namespace;


$j++;

}

} else {

/* custom namespace prefixes */
$namesaces = array(
  'rss' => 'http://purl.org/rss/1.0/',
  'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
  'dc' => 'http://purl.org/dc/elements/1.1/',
  'sy' => 'http://purl.org/rss/1.0/modules/syndication/',
  'admin' => 'http://webns.net/mvcb/',
  'content' => 'http://purl.org/rss/1.0/modules/content/',
  'lh' => 'http://localhero.biz/namespace/lhero/',
  'skos' => 'http://www.w3.org/2004/02/skos/core#',
  'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
  'sioc' => 'http://rdfs.org/sioc/ns#',
  'tag' => 'http://www.holygoat.co.uk/owl/redwood/0.1/tags/',
  'moat' => 'http://moat-project.org/ns#',
  'foaf' => 'http://xmlns.com/foaf/0.1/',
  'dcterms' => 'http://purl.org/dc/terms/',
  'sioct' => 'http://rdfs.org/sioc/types#',
  'wgs84' => 'http://www.w3.org/2003/01/geo/wgs84_pos#'
);


}


if (file_exists(LH_TOOLS_PLUGIN_DIR . '/arc/ARC2.php') ) {


include_once(LH_TOOLS_PLUGIN_DIR . '/arc/ARC2.php');

$conf = array('ns' => $namespaces);


$ser = ARC2::getRDFXMLSerializer($conf);


if ($ser){

/* Serialize a resource index */
$doc = $ser->getSerializedIndex($rdfarray, 1);

echo $doc;

}

}

//EasyRdf_Namespace::delete('geo'); 
//EasyRdf_Namespace::set('wgs84', 'http://www.w3.org/2003/01/geo/wgs84_pos#');

//$graph = new EasyRdf_Graph();
//$graph->parse($rdfarray,"php");
//print_r($rdfarray);
//$data = EasyRdf_Serialiser_RdfXml::serialise($graph,"rdfxml");

//$data = $graph->serialise("rdfxml");

echo $data;
 

}

}



?>