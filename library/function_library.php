<?php

/**
 * get the flickr ID of an image
 **/	


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
					$rdf .= "\n".'<sioc:embeds rdf:resource="' . clean_url($src) . '"/>'."\n";
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

function lh_rdf_truncate($string,$min) {
    $text = trim(strip_tags($string));
    if(strlen($text)>$min) {
        $blank = strpos($text,' ');
        if($blank) {
            # limit plus last word
            $extra = strpos(substr($text,$min),' ');
            $max = $min+$extra;
            $r = substr($text,0,$max);
            if(strlen($text)>=$max) $r=trim($r,'.').'...';
        } else {
            # if there are no spaces
            $r = substr($text,0,$min).'...';
        }
    } else {
        # if original length is lower than limit
        $r = $text;
    }
    return $r;
}

function lh_rdf_create_datadump(){


$xml = '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; 

if (function_exists('lh_relationships_return_compliant_namespace')) { 

$xml .= "\n<rdf:RDF\n";

$lhrdfnamespaces = lh_relationships_return_compliant_namespace();



$j = 0;

while ($j < count($lhrdfnamespaces)) {

$xml .= "xmlns:".$lhrdfnamespaces[$j]->prefix."=\"".$lhrdfnamespaces[$j]->namespace."\"\n";

$j++;

}

$xml .= ">\n";


} else { 

$xml .= "<rdf:RDF
	xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"
	xmlns:foaf=\"http://xmlns.com/foaf/0.1/\"
	xmlns:sioc=\"http://rdfs.org/sioc/ns#\"
	xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"
	xmlns:skos=\"http://www.w3.org/2004/02/skos/core#\"
	xmlns:moat=\"http://moat-project.org/ns#\"
	xmlns:lh=\"http://localhero.biz/namespace/lhero/\"
 	xmlns:admin=\"http://webns.net/mvcb/\"
 	xmlns:content=\"http://purl.org/rss/1.0/modules/content/\"
 	xmlns:dc=\"http://purl.org/dc/elements/1.1/\"
	xmlns:dcterms=\"http://purl.org/dc/terms/\"
	xmlns:sioct=\"http://rdfs.org/sioc/types#\"
	xmlns:tag=\"http://www.holygoat.co.uk/owl/redwood/0.1/tags/\"
	xmlns:rss=\"http://purl.org/rss/1.0/\"
	xmlns:georss=\"http://www.georss.org/georss\"
	xmlns:wgs84=\"http://www.w3.org/2003/01/geo/wgs84_pos#\"
	xmlns:xfn=\"http://vocab.sindice.com/xfn#\"
	xmlns:owl=\"http://www.w3.org/2002/07/owl#\"
	xmlns:ore=\"http://www.openarchives.org/ore/terms/\"
 	xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\"
	xmlns:sc:=\"http://sw.deri.org/2007/07/sitemapextension/scschema.xsd\"
	xmlns:void:=\"http://rdfs.org/ns/void#\"


>\n";

} 

$xml .= "<rdf:Description rdf:about=\"".get_bloginfo('url')."\">\n";

$xml .= "<rdf:type rdf:resource=\"http://rdfs.org/ns/void#Dataset\" />\n";

$args = array(
    '_builtin'              => false
); 

$post_types = get_post_types($args); 

$types = array_values($post_types);

array_push($types, "post", "page", "attachment");

$myquery['post_type'] =  $types;

$myquery['post_status'] = array('publish', 'inherit');   

$myquery['posts_per_page'] = '2000';   

$allposts = query_posts($myquery);

foreach( $allposts as $apost){


$xml .= "<void:dataDump rdf:resource=\"".get_permalink($apost->ID)."?feed=lhrdf\"/>\n";

} 


$users = get_users();

foreach( $users as $user){


$xml .= "<void:dataDump rdf:resource=\"".get_author_posts_url($user->ID)."?feed=lhrdf\"/>\n";

} 


$categories = get_categories(); 

foreach( $categories as $category){

$xml .= "<void:dataDump rdf:resource=\"".get_category_link( $category->term_id)."?feed=lhrdf\"/>\n";

}

$tags = get_tags();

foreach( $tags as $tag){

$xml .= "<void:dataDump rdf:resource=\"".get_tag_link( $tag->term_id)."?feed=lhrdf\"/>\n";

}

$xml .= "</rdf:Description>\n";


$xml .= "</rdf:RDF>";

file_put_contents(LH_RDF_PLUGIN_DIR.'/datadump/index.rdf', $xml); 


return $xml;

}

//Cron the run lh_rdf_create_datadump function so that this runs automatically


if( !wp_next_scheduled( 'lh_rdf_hourly_event' ) ) {
wp_schedule_event( time(), 'hourly', 'lh_rdf_hourly_event' );
}

add_action( 'lh_rdf_hourly_event', 'lh_rdf_create_datadump' );




?>