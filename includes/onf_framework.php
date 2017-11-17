<?php
// * ONF Framework v1.4.5 *
session_start();

class Framework
{
	public $files;
	private $config;
	private $detection;
	private $activeLang;
	private $directFolder;

	public function __construct($settings = null, $folder = "", $ip = "") {
		$this->directFolder = $folder;

		if($settings) $this->set('settings', $settings);

		//no detection -- fr default
		if($this->get('settings')->external) {
			$this->detection = (object) [
				"language" => "fr",
				"languages" => ["en", "fr", "de"],
			    "topnav" => "onf",
				"environment" => "dev",
				"domain" => "https://".str_replace("www.", "", preg_replace("/\/(.+)/", "", $_SERVER["SERVER_NAME"])),
				"framework_domain" => "https://trestrescourt.onf.ca"
			];
		}
		else{
			if(isset($ip)) $_SESSION["ip"] = $ip;

			$this->detection = (object) include("core/detection.php");
		}

		$this->activeLang = (object) json_decode(file_get_contents(__DIR__."/admin/languages.json"));
		$this->_setAssetsURL();
		$this->_setLandingContent();
	}

	//show all meta string for sharing (facebook, twitter)
	public function show_share_meta($fromAPI = false, $host = "") {
		
		$domain = ($fromAPI && $host != "") ? $host : $this->get('geoloc')->domain;
		
		$this->_check();

		//---get share
		$lang = $this->get('geoloc')->language;
		$contents = (object) $this->get('settings')->share->$lang;
		
		//---array of str
		$output = [];
		$hasVideo = isset($contents->video_url) && $contents->video_url != "";
		$share_url = ($fromAPI && $host != "") ? $host : $this->get('geoloc')->domain.$_SERVER["REQUEST_URI"]; 
	
		//gneral
		array_push($output, "<title>".$contents->title."</title>");
		
		//facebook
		array_push($output, '<meta name="description" content="'.$contents->description.'" />');
		array_push($output, '<meta property="og:description" content="'.$contents->description.'" />');
		array_push($output, "<meta property='og:url' content='".$share_url."' />");
		array_push($output, "<meta property='og:type' content='website' />");
		array_push($output, "<meta property='og:title' content='".$contents->title."' />");
		array_push($output, "<meta property='og:image' content='".$domain.$contents->image."'/>");

		if($hasVideo) {
			array_push($output, "<meta property='og:video' content='".$contents->video_url."' />");
			array_push($output, "<meta property='og:video:secure_url' content='".$contents->video_secure_url."' />");
			array_push($output, "<meta property='og:video:width' content='".$contents->video_width."' />");
			array_push($output, "<meta property='og:video:height' content='".$contents->video_height."' />");
			array_push($output, "<meta property='og:video:type' content='".$contents->video_type."' />");
		}

		//twitter
		array_push($output, "<meta name='twitter:card' content='". ((!$hasVideo) ? 'summary_large_image' : 'player') ."' />");
		array_push($output, "<meta name='twitter:site' content='@onf' />");
		array_push($output, "<meta name='twitter:title' content='".$contents->title."' />");
		array_push($output, '<meta name="twitter:description" content="'.$contents->tweet.'" />');
		array_push($output, "<meta name='twitter:image' content='".$domain.$contents->image."' />");

		if($hasVideo) {
			array_push($output, "<meta name='twitter:player' content='".$contents->video_url."' />");
			array_push($output, "<meta name='twitter:player:width' content='".$contents->video_width."' />");
			array_push($output, "<meta name='twitter:player:height' content='".$contents->video_height."' />");
		}

		//display
		if(!$fromAPI) {

			$this->_display($output);
			
		} else {

			$array = [];
			foreach($output as $key => $value) $array[$key] = $value;

			return $array;
		}
	}

	//display dependencies
	public function show_dependencies($fromAPI = false) {
		$this->_check();

		$output = [];

		//css
		$output = ["<link rel='stylesheet' href='".$this->files["css_tel_intl"]."' />"];
		array_push($output, "<link rel='stylesheet' href='".$this->files["css"]."' />");
		
		//jquery
		array_push($output, "<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>");
		array_push($output, "<script>window.jQuery || document.write(\"<script src='".$this->files['jquery']."'><\/script>\")</script>");
		
		//smart tag
		array_push($output, "<script src='".$this->files['smarttag']."'></script>");

		//js files for Arte
		if($this->_isArte()) {	
			array_push($output, "<script type='text/javascript' src='https://www.arte.tv/components/bundles/Header.min.js'></script>");
			array_push($output, "<script type='text/javascript' src='https://static-cdn.arte.tv/static/styleguide/2.7.1/bundles/Footer.min.js'></script>");
		}
		else{
			array_push($output, "<link rel='stylesheet' href='".$this->files["css_onf"]."' />");
			array_push($output, "<link rel='stylesheet' href='".$this->files["css_onf_fonts"]."' media='all' />");
		}
		
		//js framework
		array_push($output, "<script src='".$this->files['js_tel_intl']."'></script>");   
		array_push($output, "<script src='".$this->files['framework_js']."'></script>");
		
		//mobile detect
		array_push($output, "<script src='".$this->files['mobile_detect']."'></script>");

		//display
		if(!$fromAPI) {

			$this->_display($output);
			
		} else {

			$array = [];
			foreach($output as $key => $value) {
				
				$array[$key] = $value;
				// $array[$key] = preg_replace('/(\/common)/', $this->get('geoloc')->domain."$1", $value);
			}

			return $array;
		}
	}
    
    //create and show the header
    public function show_header($fromAPI = false) {
    	$this->_check();

    	$output = [];
    	$langs = $this->_getActiveLang(strtoupper($this->get("settings")->projectName));

    	//HEADER ARTE
    	if($this->_isArte()) {
    		array_push($output, "<!-- Header -->");
			array_push($output, "<script type='text/javascript'>");
			array_push($output, "var header, ArteHeader = Header.default;");
			array_push($output, "var baseUrl = 'https://www.arte.tv/components/src/';");
			array_push($output, "ArteHeader.BASE_URL = baseUrl;");
			array_push($output, "ArteHeader.setJQuery(jQuery);");
			array_push($output, "jQuery(document).ready(function() {");
			array_push($output, "header = new ArteHeader({");
			array_push($output, "lang: '".$this->get("geoloc")->language."'");
			array_push($output, ",logo: true");
			array_push($output, ",sso: false");
			array_push($output, ",'cookieBanner' : false");
			array_push($output, "});");
			array_push($output, "header.on(ArteHeader.Events.LOADED, function(event) {");
			array_push($output, "header  ");   


    		if($this->get('settings')->switch_lang_by_query) {
    			array_push($output, ".setLang('fr', '?lang=fr')");
				array_push($output, ".setLang('de', '?lang=de')");
				array_push($output, ".setLang('en', '?lang=en')");	
    		}
    		else{
				array_push($output, ".setLang('fr', 'fr')");
				array_push($output, ".setLang('de', 'de')");
				array_push($output, ".setLang('en', 'en')");
    		}

			array_push($output, ".render();");
			array_push($output, "$('#arte-header').find('.next-language__list li').find('a[data-code=\"es\"], a[data-code=\"pl\"]').hide();");
			
			if(!$langs->fr) array_push($output, "$('#arte-header').find('.next-language__list li').find('a[data-code=\"fr\"]').hide();");
			if(!$langs->en) array_push($output, "$('#arte-header').find('.next-language__list li').find('a[data-code=\"en\"]').hide();");
			if(!$langs->de) array_push($output, "$('#arte-header').find('.next-language__list li').find('a[data-code=\"de\"]').hide();");
			
			array_push($output, "});");
			array_push($output, "jQuery('#arte-footer').on('loaded', function(event) {");
			array_push($output, "var api = jQuery('#arte-footer').data('plugin-arte-footer');");
			array_push($output, "api.render();");
			array_push($output, "});");
			array_push($output, "jQuery('#arte-footer').arteFooter({");
			array_push($output, "lang:'".$this->get("geoloc")->language."',");
			array_push($output, "blank:false");
			array_push($output, "});"); 
			array_push($output, "});");
			array_push($output, "</script>");

			//--
			array_push($output, "<div class='hh_onf' id='arte-header'></div>");
    	}
    	else{
    		$folder = $this->get('geoloc')->framework_domain."/common/";
    		$isFrench = ($this->get('geoloc')->language === "fr");

    		array_push($output, "<header id='header'>");
    		array_push($output, "<div class='header-logo'>");
    		array_push($output, "<a href='".((!$isFrench) ? 'http://www.nfb.ca' : 'http://www.onf.ca')."'>");
    		array_push($output, "<img src='".$folder."images/logo-ONF.svg' alt='National Film Board'>");
    		array_push($output, "</a>");
    		array_push($output, "</div>");
    		array_push($output, "<div class='header-title'>");

    		$lang = $this->get('geoloc')->language;
    		$shareContent = (object) $this->get('settings')->share->$lang;
    		$landingDatas = $this->get('settings')->landing[strtoupper($this->get('settings')->projectName)]; 

    		array_push($output, "<a href='".$landingDatas["url"][$lang]."' target='_self'>".$shareContent->title."</a>");
    		
    		array_push($output, "</div>");
    		array_push($output, "<div class='icons'>");
    		array_push($output, "<a id='twitterLink' class='social hidden' target='_blank' href='#'>");
    		array_push($output, "<img src='".$folder."images/twitter.svg' alt='Twitter'>");
    		array_push($output, "</a>");
    		array_push($output, "<a id='facebookLink' class='social hidden' target='_blank' href='#'>");
    		array_push($output, "<img src='".$folder."images/facebook-official.svg' alt='Facebook'>");
    		array_push($output, "</a>");
    		array_push($output, "<div class='separator'></div>");
    	
    		$path = preg_replace("[\/api\/]", "", $_SERVER["REQUEST_URI"]);
    		$seps = preg_split("[\/]", $path);
    		$url = (count($seps) > 2) ? "/".$seps[1] : "";
    		
    		foreach($this->get('geoloc')->languages as $language) {
    			if($language !== $this->get('geoloc')->language) {

    				if($this->get('settings')->switch_lang_by_query) {
	 		   			if($langs->$language) array_push($output, "<a href='".$url."?lang=".$language."'>".strtoupper($language)."</a>");
    				}
    				else{
	 		   			if($langs->$language) array_push($output, "<a href='".$url."/".$language."'>".strtoupper($language)."</a>");
    				}
	    		}
	    	}

    		array_push($output, "</div>");
    		array_push($output, "</header>");	
 	
 			array_push($output, "<script src='".$this->files['js_onf']."'></script>");    	
    	}
    	
		//xiti stats
		array_push($output, "<script type='text/javascript'>");
		array_push($output, "var ATTag = new ATInternet.Tracker.Tag({");
		array_push($output, "log: 'logc136'");
		array_push($output, ",logSSL: 'logs1136'");
		array_push($output, ",secure: true");
		// array_push($output, ",site: 581265"); //dev
		array_push($output, ",site: 581264"); // prod
		array_push($output, ",domain: 'xiti.com'");
		array_push($output, "});"); //dev
		array_push($output, "</script>");
    	
    	//display
		if(!$fromAPI) {

			$this->_display($output);
			
		} else {

			$array = [];
			foreach($output as $key => $value)
			{
				$array[$key] = $value;
			}

			return $array;
		}
    }

    //create and show the footer
    public function show_footer($fromAPI = false) {
    	$this->_check();

    	$output = [];

    	//HEADER ARTE
    	if($this->_isArte()) {
    		array_push($output, "<div class='hh_onf' id='arte-footer'></div>");
    	}
    	else{
	    	
    	}

    	//display
		if(!$fromAPI) {

			$this->_display($output);
			
		} else {

			$array = [];
			foreach($output as $key => $value) $array[$key] = $value;

			return $array;
		}
    }

	//display analytics
	public function show_tagging_tools ($fromAPI = false) {
		$this->_check();

		$datas = $this->get('settings')->analytics;
		
		$output = [];
		array_push($output, "<script>");
		array_push($output, "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){");
        array_push($output, "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),");
        array_push($output, "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)");
        array_push($output, "})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');");
          
        foreach($datas as $item) array_push($output, "ga('create', '".$item["id"]."', 'auto', {'name':'".$item["name"]."'});");
        foreach($datas as $item) array_push($output, "ga('".$item["name"].".send', 'pageview');");
        
        foreach($datas as $item) {
        	array_push($output, "ga('".$item["name"].".send', {");
        	array_push($output, "hitType: 'event',");
        	array_push($output, "eventCategory: 'Interactive',");
        	array_push($output, "eventAction: 'auto_begin',");
        	array_push($output, "eventLabel: '/interactive/trestrescourt'");
        	array_push($output, "});");
        }
        array_push($output, "</script>");

		//display
		if(!$fromAPI) {

			$this->_display($output);
			
		} else {

			$array = [];
			foreach($output as $key => $value) $array[$key] = $value;

			return $array;
		}
	}

	public function exportToJS($forceName = "") {

		$projectName = ($forceName != "") ? $forceName: $this->get('settings')->projectName;

		$output = [
			"analytics" => $this->get('settings')->analytics,
			"projectName" => $this->get('settings')->projectName,
			"language" => $this->get('geoloc')->language,
			"mobile_only" => $this->get('settings')->mobile_only,
			"landing" => $this->get('settings')->landing[strtoupper($projectName)]
		];

		return json_encode($output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);
	}

	//----get
	public function get($property, $fromAPI = false) {
		
		switch($property)
		{
			case "geoloc":
			
			if(!$fromAPI) {
				return  $this->detection;
			} else {

				$array = [];
				foreach($this->detection as $key => $value) $array[$key] = $value;

				return $array;
			}
			break;

			
			case "settings":
			if(!$fromAPI) {
				return  $this->config;
			} else {

				$array = [];
				foreach($this->config as $key => $value) $array[$key] = $value;

				return $array;
			}
			break;
		}

		return null;
	}

	//---set
	public function set($property, $value) {
		switch($property) {
			case "language":
			$this->get("geoloc")->language = $value;
			break;

			case "topnav":
			$this->get("geoloc")->topnav = ($value == "arte") ? $value : "onf";
			break;

			case "settings":
			$this->config = (object) json_decode(file_get_contents($value));
			$this->_addAnalytics();
			break;

			case "folder":
			$this->directFolder = $value;

			if($this->directFolder === "") {
				$this->get('settings')->root_folder = "/";
			}
			break;
		}
	}

	//----

	private function _getActiveLang($projectName) {
		return $this->activeLang->$projectName;	
	}

	//basic display
    private function _display($output) {
    	
    	echo "\n";
		foreach($output as $str) echo $str."\n";
    }

    private function _check() {
    	if(!$this->get('settings')) {
    		echo "ERROR: Undefined settings file.";
    		exit;
    	}
    }

    private function _isArte() {
    	return $this->get("geoloc")->topnav === "arte";
    }

    private function _addAnalytics() {

    	$analytics = (isset($this->get("settings")->analytics)) ? $this->get("settings")->analytics : null;
    
    	$this->get('settings')->analytics = 
		[
			[
				"name" => "onfglobal",
				"id" => "UA-32257069-1"
			],
			[
				"name" => "onfproject",
				"id" => "UA-42015401-31"
			]
		];

		if(isset($analytics)) {
			array_push($this->get('settings')->analytics,
			[ 
				"name" => $analytics->name,
				"id" => $analytics->id
			]);
		}
    }

    private function _setAssetsURL() {
    	$domain = $this->get('geoloc')->framework_domain;

    	//define files urls
		$this->files = [
			"css" => $domain."/common/css/onf_framework.css",
			"css_tel_intl" => $domain."/common/css/intlTelInput.css",
			"css_onf" => $domain."/common/css/header.min.css",
			"css_onf_fonts" => $domain."/common/fonts/fonts.css",
			"framework_js" => $domain."/common/js/onf_framework.js",
			"js_onf" => $domain."/common/js/header.min.js",
			"js_tel_intl" => $domain."/common/js/intlTelInput.min.js",
			"smarttag" => $domain."/common/js/smarttag.js",
			"jquery" => $domain."/common/js/jquery-3.2.1.min.js",
			"mobile_detect" => $domain."/common/js/mobile-detect.min.js",
		];

		//direct link feature
		if($this->directFolder === "") {
			$this->get('settings')->root_folder = "/";
		}
    }

    private function _setLandingContent() {
    	$this->get('settings')->landing = [
    		"SLEEP TOGETHER" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/cododo",
    				"en" => "https://veryveryshort.com/sleeptogether",
    				"de" => "https://veryveryshort.com/sleeptogether"
    			],
				"title" => [
					"fr" => "CODODO",
					"en" => "SLEEP TOGETHER",
					"de" => "SLEEP TOGETHER de"
				],
				"author" => [
					"fr" => "par Laura Juo-Hsin Chen",
					"en" => "by Laura Juo-Hsin Chen",
					"de" => "by Laura Juo-Hsin Chen de"
				],
          		"tagline" => [
          			"fr" => "Ne vous endormez plus jamais seul.",
          			"en" => "Never go to sleep alone again.",
          			"de" => "Enter the rabbit hole and let’s all sleep together. de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "L'ONF et ARTE ne conservent aucun numéro de téléphone. de"
          		],
          		"sms" => [
          			"fr" => "https://trestrescourt.com/cododo 🌚",
          			"en" => "https://veryveryshort.com/sleeptogether 🌚",
          			"de" => "https://veryveryshort.com/sleeptogether 🌚"
          		]
          	],
        	"STIR" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/appeldureveil",
    				"en" => "https://veryveryshort.com/stir",
    				"de" => "https://veryveryshort.com/stir"
    			],
				"title" => [
					"fr" => "L'APPEL DU RÉVEIL",
					"en" => "STIR",
					"de" => "STIR de"
				],
				"author" => [
					"fr" => "par Rebecca Lieberman et Julia Irwin",
					"en" => "by Rebecca Lieberman and Julia Irwin",
					"de" => "by Rebecca Lieberman and Julia Irwin de"
				],
          		"tagline" => [
          			"fr" => "Être réveillé par un étranger n’aura jamais été si doux.",
          			"en" => "Waking up to the sound of a stranger’s voice never felt so right.",
          			"de" => "Waking up to the sound of a stranger’s voice never felt so right. de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "L'ONF et ARTE ne conservent aucun numéro de téléphone. de"
          		],
          		"sms" => [
          			"fr" => "https://trestrescourt.com/appeldureveil ⏰",
          			"en" => "https://veryveryshort.com/stir ⏰",
          			"de" => "https://veryveryshort.com/stir ⏰"
          		]
          	],
          	"BIAS" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/apriori",
    				"en" => "https://veryveryshort.com/bias",
    				"de" => "https://veryveryshort.com/bias"
    			],
				"title" => [
					"fr" => "A PRIORI",
					"en" => "BIAS",
					"de" => "BIAS de"
				],
				"author" => [
					"fr" => "par Nicolas S. Roy, Rebecca West et Catherine D'Amours",
					"en" => "by Nicolas S. Roy, Rebecca West and Catherine D'Amours",
					"de" => "by Nicolas S. Roy, Rebecca West and Catherine D'Amours de"
				],
          		"tagline" => [
          			"fr" => "Votre esprit vous joue-t-il des tours?",
          			"en" => "Is your mind made up?",
          			"de" => "Is your mind made up? de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "L'ONF et ARTE ne conservent aucun numéro de téléphone. de"
          		],
          		"sms" => [
          			"fr" => "https://trestrescourt.com/apriori 👁",
          			"en" => "https://veryveryshort.com/bias 👁",
          			"de" => "https://veryveryshort.com/bias 👁"
          		]
          	],
          	"WHERE IS HOME" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/etrechezsoi",
    				"en" => "https://veryveryshort.com/whereishome",
    				"de" => "https://veryveryshort.com/whereishome"
    			],
				"title" => [
					"fr" => "ÊTRE CHEZ SOI",
					"en" => "WHERE IS HOME?",
					"de" => "WHERE IS HOME? de"
				],
				"author" => [
					"fr" => "par Ifeatu Nnaobi",
					"en" => "by Ifeatu Nnaobi",
					"de" => "by Ifeatu Nnaobi de"
				],
          		"tagline" => [
          			"fr" => "Parfois chez soi n'est pas sous son toit.",
          			"en" => "Sometimes you have to look for home outside the box.",
          			"de" => "Sometimes you have to look for home outside the box. de"
          		],
          		"warning" => [
          			"fr" => "Découvrez cette expérience sur l'app Instagram de votre mobile.<br/><a href='https://instagram.com/etrechezsoi'>@etrechezsoi</a>",
          			"en" => "Discover this experience on your Insta.<br/><a href='https://instagram.com/whereis_home'>@whereis_home</a>",
          			"de" => "Discover this experience on your Insta [lien a venir @whereishome_nfb] de"
          		],
          		"misc" => [
          			"fr" => "",
          			"en" => "",
          			"de" => ""
          		],
          		"sms" => [
          			"fr" => "",
          			"en" => "",
          			"de" => ""
          		]
          	],
          	"PIGEON VOYAGEUR" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/pigeonvoyageur",
    				"en" => "https://veryveryshort.com/carrierpigeon",
    				"de" => "https://veryveryshort.com/carrierpigeon"
    			],
				"title" => [
					"fr" => "PIGEON VOYAGEUR",
					"en" => "CARRIER PIGEON",
					"de" => "CARRIER PIGEON de"
				],
				"author" => [
					"fr" => "par Folklore",
					"en" => "by Folklore",
					"de" => "by Folklore de"
				],
          		"tagline" => [
          			"fr" => "Suivez vos communications sur l'autoroute de l'information.",
          			"en" => "Follow your communications on the information superhighway.",
          			"de" => "Follow your communications on the information superhighway. de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "The NFB and ARTE do not keep any phone number."
          		],
          		"sms" => [
          			"fr" => "https://trestrescourt.com/pigeonvoyageur 🐦",
          			"en" => "https://veryveryshort.com/carrierpigeon 🐦",
          			"de" => "https://veryveryshort.com/carrierpigeon 🐦"
          		]
          	],
          	"FLIPFLY" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/envolee",
    				"en" => "https://veryveryshort.com/flipfly",
    				"de" => "https://veryveryshort.com/flipfly"
    			],
				"title" => [
					"fr" => "ENVOLÉE",
					"en" => "FLIPFLY",
					"de" => "FLIPFLY de"
				],
				"author" => [
					"fr" => "par Lucile Cossou, Gabriel Dalmasso et Rémy Bonté-Duval",
					"en" => "by Lucile Cossou, Gabriel Dalmasso and Rémy Bonté-Duval",
					"de" => "by Lucile Cossou, Gabriel Dalmasso and Rémy Bonté-Duval de"
				],
          		"tagline" => [
          			"fr" => "Prêt pour le décollage?",
          			"en" => "Ready for take-off?",
          			"de" => "Ready for take-off? de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "The NFB and ARTE do not keep any phone number."
          		],
          		"sms" => [
          			"fr" => "https://trestrescourt.com/envolee ✈️",
          			"en" => "https://veryveryshort.com/flipfly ✈️",
          			"de" => "https://veryveryshort.com/flipfly ✈️"
          		]
          	],
          	"THE PAPER SAIL" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/voiledepapier",
    				"en" => "https://veryveryshort.com/papersail",
    				"de" => "https://veryveryshort.com/papersail"
    			],
				"title" => [
					"fr" => "LA VOILE DE PAPIER",
					"en" => "THE PAPER SAIL",
					"de" => "THE PAPER SAIL de"
				],
				"author" => [
					"fr" => "par Cosmgrafik & Gaeel",
					"en" => "by Cosmgrafik & Gaeel",
					"de" => "by Cosmgrafik & Gaeel"
				],
          		"tagline" => [
          			"fr" => "Pliez bagage et partez à la découverte.",
          			"en" => "Hoist the sail to discover unexplored seas.",
          			"de" => "Hoist the sail to discover unexplored seas. de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "The NFB and ARTE do not keep any phone number."
          		],
          		"sms" => [
          			"fr" => "https://trestrescourt.com/voiledepapier ⛵️",
          			"en" => "https://veryveryshort.com/papersail ⛵️",
          			"de" => "https://veryveryshort.com/papersail ⛵️"
          		]
          	],
          	"REVOLVE" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/revolutio",
    				"en" => "https://veryveryshort.com/revolve",
    				"de" => "https://veryveryshort.com/revolve"
    			],
				"title" => [
					"fr" => "REVOLUTIO",
					"en" => "REVOLVE",
					"de" => "REVOLVE de"
				],
				"author" => [
					"fr" => "par Bram Loogman et Joaquin Wall",
					"en" => "by Bram Loogman and Joaquin Wall",
					"de" => "by Bram Loogman and Joaquin Wall de"
				],
          		"tagline" => [
          			"fr" => "Tournez sur le rythme!",
          			"en" => "Spin to the rhythm!",
          			"de" => "Spin to the rhythm! de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "The NFB and ARTE do not keep any phone number."
          		],
          		"sms" => [
          			"fr" => "https://trestrescourt.com/revolutio 💃",
          			"en" => "https://veryveryshort.com/revolve 💃",
          			"de" => "https://veryveryshort.com/revolve 💃"
          		]
          	],
          	"VIRAL ADVISOR" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/viralconseil",
    				"en" => "https://veryveryshort.com/viraladvisor",
    				"de" => "https://veryveryshort.com/viraladvisor"
    			],
				"title" => [
					"fr" => "VIRAL CONSEIL",
					"en" => "VIRAL ADVISOR",
					"de" => "VIRAL ADVISOR de"
				],
				"author" => [
					"fr" => "par Dries Depoorter et David Surprenant",
					"en" => "by Dries Depoorter et David Surprenant",
					"de" => "by Dries Depoorter et David Surprenant de"
				],
          		"tagline" => [
          			"fr" => "Montrez votre meilleur profil.",
          			"en" => "Become your best online self.",
          			"de" => "Become your best online self. de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "The NFB and ARTE do not keep any phone number."
          		],
          		"sms" => [
          			"fr" => "https://trestrescourt.com/viralconseil 💯",
          			"en" => "https://veryveryshort.com/viraladvisor 💯",
          			"de" => "https://veryveryshort.com/viraladvisor 💯"
          		]
          	],
          	"A TEMPORARY CONTACT" => [
    			"url" => [
    				"fr" => "https://trestrescourt.com/temporarycontact_fr",
    				"en" => "https://veryveryshort.com/temporarycontact",
    				"de" => "https://veryveryshort.com/temporarycontact"
    			],
				"title" => [
					"fr" => "CONTACT ÉPHÉMÈRE",
					"en" => "A TEMPORARY CONTACT",
					"de" => "A TEMPORARY CONTACT de"
				],
				"author" => [
					"fr" => "par Sara Kolster and Nirit Peled",
					"en" => "by Sara Kolster and Nirit Peled",
					"de" => "by Sara Kolster and Nirit Peled de"
				],
          		"tagline" => [
          			"fr" => "Montez à bord pour débuter votre voyage… vers la prison.",
          			"en" => "Hop on your phone to embark on a journey… to prison.",
          			"de" => "Hop on your phone to embark on a journey… to prison. de"
          		],
          		"warning" => [
          			"fr" => "Cette expérience est optimisée pour appareil mobile.<br/>Entrez votre numéro de téléphone pour recevoir l'expérience par message texte.",
          			"en" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile.",
          			"de" => "This experience is optimized for mobile platforms.<br/>Enter your phone number to receive the experience directly on your mobile. de"
          		],
          		"misc" => [
          			"fr" => "L'ONF et ARTE ne conservent aucun numéro de téléphone.",
          			"en" => "The NFB and ARTE do not keep any phone number.",
          			"de" => "The NFB and ARTE do not keep any phone number."
          		],
          		"sms" => [
          			"fr" => "Voici comment faire l'expérience :\\n\\n✅ Téléchargez WhatsApp sur votre mobile\\n✅  Ajoutez XXXX (XXXXX) à vos contacts\\n✅  Envoyez le premier message\\n\\nVous êtes maintenant à bord! 🚎",
          			"en" => "Here's how to do this experience :\\n\\n✅ Download WhatsApp on your mobile\\n✅  Add A Temporary Contact (XXXXX) to your address book\\n✅  Send the first message\\n\\nYou are now on board! 🚎",
          			"de" => "Here's how to do this experience :\\n\\n✅ Download WhatsApp on your mobile\\n✅  Add A Temporary Contact (XXXXX) to your address book\\n✅  Send the first message\\n\\nYou are now on board! 🚎"
          		]
          	]
        ];
    }
}
?>