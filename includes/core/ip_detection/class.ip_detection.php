<?php

class IP_Detection{
    private $dbSettings = array(
                                'dev' => array(
                                    'hostname' => "localhost",
                                    'username' => "ennemi",
                                    'password' => "SFd24fsE%2x",
                                    'database' => "geoip"
                                    ),
                                'stage' => array(
                                    'hostname' => "fmm-stage-db.cdbate53n5mc.us-east-1.rds.amazonaws.com",
                                    'username' => "geoip",
                                    'password' => "JDll3lk435DPJ",
                                    'database' => "geoip"
                                    ),
                                'production' => array(
                                    'hostname' => "inter-prod.cstvzisey1xd.us-east-1.rds.amazonaws.com",
                                    'username' => "ennemi",
                                    'password' => "SFd24fsE%2x",
                                    'database' => "geoip"
                                    ),
                                );
    private $dbConn = null;
    private $ipCountry = "";
    private $ipRegion = "";
    private $ipLanguage = "en";
    private $ipTopNav = "nfb";
    public $siteEnv = "production";
    
    //FUNCTIONS

     public function __construct($current_domain = ""){
        //Detect the site Enviroment
        $this->set_SiteEnv($current_domain);
        
    }
    
    private function set_siteEnv($current_domain = ""){
        if(preg_match('/dev/',$current_domain)) { //$origin_url contains "dev"
            $this->siteEnv = "dev";
        } else if(preg_match('/stage/',$current_domain)){ //$origin_url contains "stage"
            $this->siteEnv = "stage";
        } else { //catch-all: must be production
            $this->siteEnv = "production";
        }        
    }
    
    public function getBrowserLanguage(){
    //to turn off the browser detection and use IP instead
    return 0;
    if(array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)){
        if(preg_match('/fr/',$_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $this->ipLanguage = "fr";
        }
        else if(preg_match('/de/',$_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $this->ipLanguage = "de";
        } 
        return 1;
    }
    return 0;
    }

    public function dbConnect(){
        $this->dbConn = mysqli_connect($this->dbSettings[$this->siteEnv]['hostname'], $this->dbSettings[$this->siteEnv]['username'], $this->dbSettings[$this->siteEnv]['password']);
        if(!$this->dbConn){
            return FALSE;
        }
        $dbSelected = mysqli_select_db($this->dbConn, $this->dbSettings[$this->siteEnv]['database']);
        if(!$dbSelected){
            return FALSE;
        }
        
        return TRUE;
    }
    
    public function getIPInfo($lIpAddress = ""){
        $longIP= ip2long($lIpAddress);
        //echo "long=".$long."<br>";
        if ($longIP == -1 || $longIP === FALSE) {
                return FALSE;
        }
        //SELECT * 
        //FROM pme_geoipcity 
        //WHERE startIPNum = (SELECT MAX(startIPNum) FROM pme_geoipcity  WHERE startIPNum <= INET_ATON('207.179.146.70')) AND endIPNum >= INET_ATON('207.179.146.70');
    //new query:
    //$sQuery = "SELECT `GeoIPCity-134-Location`.country, `GeoIPCity-134-Location`.region, `country_language_header`.country_language, `country_language_header`.country_topnav
//          FROM `GeoIPCity-134-Blocks` 
//          INNER JOIN `GeoIPCity-134-Location` ON `GeoIPCity-134-Blocks`.locId = `GeoIPCity-134-Location`.locId   
//          INNER JOIN `country_language_header` ON `GeoIPCity-134-Location`.country = `country_language_header`.country_code
//           WHERE `GeoIPCity-134-Blocks`.startIPNum = (SELECT MAX(`GeoIPCity-134-Blocks`.startIPNum) FROM `GeoIPCity-134-Blocks`  WHERE `GeoIPCity-134-Blocks`.startIPNum <= INET_ATON('".$lIpAddress."')) AND `GeoIPCity-134-Blocks`.endIPNum >= INET_ATON('".$lIpAddress."');";
        $sQuery = "SELECT `GeoIPCity-134-Location`.country, `GeoIPCity-134-Location`.region, `country_language_header`.country_language, `country_language_header`.country_topnav  FROM `GeoIPCity-134-Blocks` INNER JOIN `GeoIPCity-134-Location` ON `GeoIPCity-134-Blocks`.locId = `GeoIPCity-134-Location`.locId INNER JOIN `country_language_header` ON `GeoIPCity-134-Location`.country = `country_language_header`.country_code  WHERE `GeoIPCity-134-Blocks`.startIPNum = (SELECT MAX(`GeoIPCity-134-Blocks`.startIPNum) FROM `GeoIPCity-134-Blocks`  WHERE `GeoIPCity-134-Blocks`.startIPNum <= ".$longIP.") AND `GeoIPCity-134-Blocks`.endIPNum >= ".$longIP.";";

//  $sQuery = "SELECT * FROM `GeoIPCity-134-Blocks` INNER JOIN `GeoIPCity-134-Location` ON `GeoIPCity-134-Blocks`.locId = `GeoIPCity-134-Location`.locId    WHERE `GeoIPCity-134-Blocks`.startIPNum = (SELECT MAX(`GeoIPCity-134-Blocks`.startIPNum) FROM `GeoIPCity-134-Blocks`  WHERE `GeoIPCity-134-Blocks`.startIPNum <= INET_ATON('".$lIpAddress."')) AND `GeoIPCity-134-Blocks`.endIPNum >= INET_ATON('".$lIpAddress."');";
       // $sQuery = "SELECT pme_geoipcity.country as country, pme_geoipcity.region as region, country_region.language as langauge FROM pme_geoipcity INNER JOIN country_region ON pme_geoipcity.country = country_region.country AND pme_geoipcity.region = country_region.region WHERE startIPNum = (SELECT MAX(startIPNum) FROM pme_geoipcity  WHERE startIPNum <= INET_ATON('".$lIpAddress."')) AND endIPNum >= INET_ATON('".$lIpAddress."');";
        $result = mysqli_query($this->dbConn, $sQuery);
        if(!$result){
            return FALSE;
        }
        if (mysqli_num_rows($result) == 0) {
            return FALSE;
        }        
        $row = mysqli_fetch_array($result);
        $this->ipCountry = $row[0];
        $this->ipRegion = $row[1];
        $this->ipLanguage = $row[2];        
    $this->ipTopNav = $row[3];
        return TRUE;
    }
    
    public function getIPCountry(){
        return $this->ipCountry;
    }
    
    public function getIPRegion(){
        return $this->ipRegion;
    }
    
    public function getIPLanguage(){
    if($this->ipCountry == "CA") if( $this->ipRegion == "QC") $this->ipLanguage="fr";
    return $this->ipLanguage;
    } 
    public function getSiteEnv(){
    return $this->siteEnv;
    }   
    public function getIPTopNav(){
    return $this->ipTopNav;
    }   
}



?>