<?php
require __DIR__. '/../onf_framework.php';

//define templates path
$container = $app->getContainer();

//check directory (subdomain or link to folder )
$settings = $container->get('settings');

//framework instance
$framework = new Framework($settings["url"], $settings["directory"]); 
$www_folder = ".".$framework->get('settings')->root_folder.$framework->get('settings')->www_folder;

//define the ViewRenderer
$container['renderer'] = new Slim\Views\PhpRenderer($www_folder);
$container["view_admin"] = function($container) {
    return new Slim\Views\PhpRenderer('./includes/admin/');
};
    
//default
$app->get($framework->get('settings')->root_folder, function ($request, $response, $args) use ($framework) {

    $queries = $request->getQueryParams();
    $name = $request->getParam("name");
    
    if(isset($queries["lang"])) {

        //wrong language --> is it a direct link to a project
        if(array_search($queries["lang"], $framework->get('geoloc')->languages) === FALSE) {
            return $response->withRedirect($framework->get('settings')->root_folder);
        }

        $framework->set('language', $queries["lang"]);
    }

    return $this->renderer->render($response, "/index.php", [ "force_name" => $name, "framework" => $framework ]);
});

//all languages
$app->get($framework->get('settings')->root_folder.'{language:en|fr|de}', function ($request, $response, $args) use ($framework) {
    
    $langParam = $request->getAttribute('language');
    $name = $request->getParam("name");

    //wrong language --> is it a direct link to a project
    if(array_search($langParam, $framework->get('geoloc')->languages) === FALSE) {
        return $response->withRedirect($framework->get('settings')->root_folder);
    }

    $framework->set('language', $langParam);

    return $this->renderer->render($response, "/index.php", [ "force_name" => $name, "framework" => $framework ]);
});

//---ADMIN
$app->get("/onf_admin", function($request, $response, $args) use ($framework, $app) {
    return $this->view_admin->render($response, "languages.php", [ "framework" => $framework ]);
});

$app->post("/onf_admin_save", function($request, $response, $args) use ($framework, $app) {

    $output = $request->getParam("json");
    file_put_contents(__DIR__.'/../admin/languages.json', $output);
    
    $body = $response->getBody();
    $body->write(true);
});

//---REST HTTP
$app->post("/api/all", function ($request, $response, $args) use ($framework, $app) {

    //new instance of the framework
    $framework = new Framework($request->getParam("settings"), $request->getParam("folder"), $request->getParam("ip")); 
   
    //change lang
    $lang = $request->getParam('language');
    if(isset($lang) && array_search($lang, $framework->get('geoloc')->languages) !== FALSE) {
        $framework->set('language', $lang);
    }

    $response = $response->withHeader('Content-type', 'application/json');
    $body = $response->getBody();

    $json1 = $framework->show_dependencies(true);
    $json2 = $framework->show_share_meta(true, $request->getParam('host'));
    $json3 = $framework->show_header(true);
    $json4 = $framework->show_footer(true);
    $json5 = $framework->show_tagging_tools(true);
    $json6 = $framework->get("geoloc", true);
    $json7 = $framework->get("settings", true);
    $json8 = $framework->exportToJS();

    $output = json_encode( array_merge([
        "dependencies" => $json1,
        "share" => $json2,
        "header" => $json3,
        "footer" => $json4,
        "tag" => $json5,
        "geoloc" => $json6,
        "settings" => $json7,
        "export_to_js" => $json8]), JSON_PRETTY_PRINT ); 

    $body->write( $output );
});

$app->post("/api/geoloc", function ($request, $response, $args) use ($framework, $app) {

    $language = $request->getParam('language');
    $ip = $request->getParam("ip");
    $domain = $request->getParam("domain");

    $_SESSION["ip"] = $ip;
    $detection = (object) include("detection.php");

    if(isset($domain)) $detection->domain = $domain;
    if(isset($language) && array_search($language, $framework->get('geoloc')->languages) !== FALSE) $detection->language = $language;

    return $response->withJson( $detection, null, JSON_PRETTY_PRINT );
});

//---CALL NUMBER
$app->post("/api/send-texto", function($request, $response, $args) use ($framework, $app) {

    $number = $request->getParam("number");
    $sms = $request->getParam("sms");
    
    // Your Account Sid and Auth Token from twilio.com/user/account
    $sid = "AC31e10a6337cb73a0811096a3221c9960";
    $token = "daa90528651e25b92138f51ef9bba4e2";
    $client = new Twilio\Rest\Client($sid, $token);

    $client->messages->create(
      $number,
      array(
        'from' => "+18445181616",
        'body' => $sms
      )
    );

    $body = $response->getBody();
    $body->write('sent');
});
?>