<?php

	
/***
* This Function returns modified content that is passed to it with all of the email 
* addresses properly obfuscated.
***/
function email_obfuscation($content){
	$parsed = preg_replace("/[\"\']mailto:([A-Za-z0-9._%-]+)\@([A-Za-z0-9._%-]+)\.([A-Za-z.]{2,4})[\"\'\?]/e", "'\"/obfuser/'.str_rot13('\\1').'+'.str_rot13('\\2').'+'.str_rot13('\\3').'\" rel=\"nofollow\"'", $content);
	$parsed = preg_replace("/>([A-Za-z0-9._%-]+)\@/e", "'>'.substr('\\1',0,-2).'..&#64;'", $parsed); // To be sure, truncate e-mail addresses that are *not* linked (bill.ga...@microsoft.com)
	
	return $parsed;// . email_obfus_js();
}

/***
* This Function returns the JS drop needed to re-enable the email addresses.
***/
function email_obfus_js(){
	return "\n<script type=\"text/javascript\"> var obfuscator = new EmailObfuscator(); </script>";
}

/***
* This Function will return info about the browser (useragent, etc.)
***/
function browser_info()
{
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	$is_mobile = FALSE;
	$is_facebook = FALSE;
	$is_bot = FALSE;
	$is_good_bot = FALSE;
	$mobile_device = 'Desktop';
	$good_bots = "/(facebookscraper|facebookexternalhit|Googlebot|Feedfetcher-Google|feedfetcher|googlebot|msnbot|MSNBOT_Mobile|livebot)/i";
	$bad_bots = "/(Indy|Blaiz|Java|libwww-perl|Python|OutfoxBot|User-Agent|PycURL|AlphaServer|T8Abot|Syntryx|WinHttp|WebBandit|nicebot)/i";

	
	if(preg_match('/ipod/',$useragent)||preg_match('/iphone/',strtolower($useragent))){ // we find iPhone in the user agent
		$mobile_device = 'iPhone';
		$is_mobile = TRUE;
	} else if (preg_match('/ipad/',strtolower($useragent))){  // we find android in the user agent
		$mobile_device = 'iPad';
		$is_mobile = TRUE;
	} else if (preg_match('/android/',strtolower($useragent))){  // we find android in the user agent
		$mobile_device = 'Android';
		$is_mobile = TRUE;
	} else if(preg_match('/blackberry/',strtolower($useragent))) { // we find blackberry in the user agent
		$mobile_device = 'Blackberry';
		$is_mobile = TRUE;
	} else if(preg_match('/facebook/',strtolower($useragent))) {//stripos('facebook',$useragent)>=0) { // we find facebook in the user agent
		$mobile_device = 'Facebook';
		$is_facebook = TRUE;
	} else if(preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile|o2|opera mini|palm( os)?|plucker|pocket|pre\/|psp|smartphone|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce; (iemobile|ppc)|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
		$is_mobile = TRUE;
		$mobile_device = 'Other';
	} else if(preg_match($good_bots,$useragent) || preg_match($bad_bots,$useragent)) {
		$is_bot = TRUE;
		$mobile_device = 'bad_bot';
		if(preg_match($good_bots,$useragent)){
			$is_good_bot = true;
			$mobile_device = 'good_bot';
		}
	} else if(((strpos($useragent,'text/vnd.wap.wml')>0)||(strpos($useragent,'application/vnd.wap.xhtml+xml')>0) || (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])))) { // is the device giving us a HTTP_X_WAP_PROFILE or HTTP_PROFILE header - only mobile devices would do this
		$mobile_device = 'Unknown';
		$is_mobile = TRUE;
	}
	return (object)array('is_mobile'=>$is_mobile,'is_bot'=>$is_bot,'is_good_bot'=>$is_good_bot,'is_facebook'=>$is_facebook,'device'=>$mobile_device,'user_agent'=>$useragent);
}

/***
* This Function will setup the facebook meta info
***/
function setup_meta($meta){
	$medium = (isset($meta->medium)) ? "<meta name=\"medium\" content=\"{$meta->medium}\"/>" : "";
	$output =<<<OUTPUT
	
	  <title>{$meta->title}</title>
	  {$medium}
	  <meta name="title" content="{$meta->title}"/>
	  <meta name="description" content="{$meta->description}"/>
	  <meta name="keywords" content="{$meta->keywords}"/>
	  <link rel="image_src" href="{$meta->image_src}" />
	  <link rel="target_url" href="{$meta->target_url}"/>
OUTPUT;

	return $output;
}

function merge_meta($base_meta, $project_meta){
	$output_object = $base_meta;
/* JSON STRUCTURE TO MERGE 
	"title": "NFB/Interactive - The Test Tube",
	"target_url":"http://testtube.nfb.ca",		 
	"image_src": "",
	"keywords" : "",
	"description": "",
	"medium": "multi",
	"facebook_meta":{
	},
	"twitter_meta":{
	},
	"google_meta":{
	},
	"code_injection" : "",
	"mobile_templates": {
		"iphone" : "",
		"ipad" : "",
		"android": "",
		"blackberry": "",
		"other": ""
	},
	"google_analytics": ""
*/
	
	$output_object->title = (strlen($project_meta->title) > 0) ? $project_meta->title : $base_meta->title;
	$output_object->target_url = (strlen($project_meta->target_url) > 0) ? $project_meta->target_url : $base_meta->target_url;
	$output_object->image_src = (strlen($project_meta->image_src) > 0) ? $project_meta->image_src : $base_meta->image_src;
	$output_object->keywords = (strlen($project_meta->keywords) > 0) ? $project_meta->keywords : $base_meta->keywords;
	$output_object->description = (strlen($project_meta->description) > 0) ? $project_meta->description : $base_meta->description;
	$output_object->medium = (strlen($project_meta->medium) > 0) ? $project_meta->medium : $base_meta->medium;
	$output_object->has_mobile = (strlen($project_meta->has_mobile) > 0) ? $project_meta->has_mobile : $base_meta->has_mobile;
	$output_object->facebook_meta = (count((array)$project_meta->facebook_meta) > 0) ? $project_meta->facebook_meta : $base_meta->facebook_meta;
	$output_object->twitter_meta = (count((array)$project_meta->twitter_meta) > 0) ? $project_meta->twitter_meta : $base_meta->twitter_meta;
	$output_object->google_meta = (count((array)$project_meta->google_meta) > 0) ? $project_meta->google_meta : $base_meta->google_meta;
	$output_object->code_injection = (strlen($project_meta->code_injection) > 0) ? $project_meta->code_injection : $base_meta->code_injection;
	
	if(isset($project_meta->mobile_templates)){
		if(isset($project_meta->mobile_templates->iphone)){
			$output_object->mobile_templates->iphone = (strlen($project_meta->mobile_templates->iphone) > 0) ? $project_meta->mobile_templates->iphone : $base_meta->mobile_templates->iphone;
		}
		if(isset($project_meta->mobile_templates->ipad)){
			$output_object->mobile_templates->ipad = (strlen($project_meta->mobile_templates->ipad) > 0) ? $project_meta->mobile_templates->ipad : $base_meta->mobile_templates->ipad;
		}
		if(isset($project_meta->mobile_templates->android)){
			$output_object->mobile_templates->android = (strlen($project_meta->mobile_templates->android) > 0) ? $project_meta->mobile_templates->android : $base_meta->mobile_templates->android;
		}
		if(isset($project_meta->mobile_templates->blackberry)){
			$output_object->mobile_templates->blackberry = (strlen($project_meta->mobile_templates->blackberry) > 0) ? $project_meta->mobile_templates->blackberry : $base_meta->mobile_templates->blackberry;
		}
		if(isset($project_meta->mobile_templates->other)){
			$output_object->mobile_templates->other = (strlen($project_meta->mobile_templates->other) > 0) ? $project_meta->mobile_templates->other : $base_meta->mobile_templates->other;
		}
	}
	
	$output_object->project_ga = (strlen($project_meta->google_analytics) > 0) ? $project_meta->google_analytics : $base_meta->google_analytics;
	
	return $output_object;
}

function get_visitor_ip(){     
	if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/***
* This Function will decode JSON info
***/
function cust_json_decode($json)
{ 
    $comment = false;
    $out = '$x=';
   
    for ($i=0; $i<strlen($json); $i++)
    {
        if (!$comment)
        {
            if ($json[$i] == '{' || $json[$i] == '[')        
				$out .= 'array(';
            else if ($json[$i] == '}'|| $json[$i] == ']')    
				$out .= ')';
            else if ($json[$i] == ':')    
				$out .= '=>';
            else                         
				$out .= $json[$i];           
        }
        else $out .= $json[$i];
        if ($json[$i] == '"')    $comment = !$comment;
    }
    eval($out . ';');
    return (object)$x;
} 

	/***
	* This Function will sort an array by the key of his sub-array.
	*
	* @Function sksort
	* @Param &amp;$array | Required | | Array | The array to sort
	* @Param $subkey | Optional | 'id' | String | The sub key to sort by
	* @Param $sort_ascending | Optional | FALSE | Boolean | Whether or not to sort the array ascending.
	* @Output None, the array is passed by reference.
	***/
	function sksort(&$array, $subkey="id", $sort='desc') {

		if (count($array))
			$temp_array[key($array)] = array_shift($array);
	
		foreach($array as $key => $val){
			$offset = 0;
			$found = false;
			foreach($temp_array as $tmp_key => $tmp_val)
			{
				if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
				{
					$temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
										array($key => $val),
										array_slice($temp_array,$offset)
										  );
					$found = true;
				}
				$offset++;
			}
			if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
		}
	
		if ($sort == 'asc') $array = array_reverse($temp_array);
	
		else $array = $temp_array;
	}



?>