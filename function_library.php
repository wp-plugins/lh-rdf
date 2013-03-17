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
					$rdf .= "\n\t" . '<sioc:embeds foaf:Image="' . clean_url($src) . '"/>';
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
                $rdf .= '<sioc:links_to rdf:resource="' . wp_specialchars(trim($anchor[1]),1) . '" rdfs:label="' . apply_filters( 'the_title_rss', apply_filters( 'the_title', wp_specialchars($val[2],1))) . '"/>';
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



?>