<?php
# Licensed under a CC0 1.0 Universal (CC0 1.0) Public Domain Dedication
# http://creativecommons.org/publicdomain/zero/1.0/

require( "../live/config.php" );

$site = 'https://calumryan.com/';
$token_endpoint = 'https://tokens.indieauth.com/token';

$_HEADERS = array();

$getHeaders = get_headers($site);


if (!function_exists('apache_request_headers')) {

    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $_HEADERS[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }

}

if (!isset($_HEADERS['Authorization'])) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
    echo 'Missing "Authorization" header.';
    exit;
}

if (!isset($_POST['h'])) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    echo 'Missing "h" value.';
    exit;
}

$options = array(
    CURLOPT_URL => $token_endpoint,
    CURLOPT_HTTPGET => TRUE,
    CURLOPT_USERAGENT => $site,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HEADER => FALSE,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: '.$_HEADERS['Authorization']
    )
);

$curl = curl_init();
curl_setopt_array($curl, $options);
$source = curl_exec($curl);
curl_close($curl);

parse_str($source, $values);

if (!isset($values['me'])) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    echo 'Missing "me" value in authentication token.';
    exit;
}
if (!isset($values['scope'])) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    echo 'Missing "scope" value in authentication token.';
    exit;
}
if (substr($values['me'], -1) != '/') {
    $values['me'].= '/';
}
if (substr($site, -1) != '/') {
    $site.= '/';
}
if (strtolower($values['me']) != strtolower($site)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    echo 'Mismatching "me" value in authentication token.';
    exit;
}

if ( isset( $_HEADERS['Content-Type'] ) ) {

    if ( $_HEADERS['Content-Type'] == 'application/json' ) {

        //Receive the RAW post data via the php://input IO stream.
        $content = file_get_contents("php://input");

        //Make sure that it is a POST request.
        if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
            throw new Exception('Request method must be POST!');
        }

        //Make sure that the content type of the POST request has been set to application/json
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if(strcasecmp($contentType, 'application/json') != 0){
            throw new Exception('Content type must be: application/json');
        }

        //Receive the RAW post data.
        $content = trim(file_get_contents("php://input"));

        //Attempt to decode the incoming RAW post data from JSON.
        $decoded = json_decode($content, true);

        //If json_decode failed, the JSON is invalid.
        if(!is_array($decoded)){
            throw new Exception('Received content contained invalid JSON!');
        }

        $debug = var_export($decoded, true);

        $_POST['content'] = $decoded['properties']['checkin'][0]['properties']['name'][0];

        $contentType = 'json';

        // Start session and insert post
        session_start();
        $note = new Note;
        $note->storeFormValues( $_POST, $contentType );
        $note->insert();

    }

} else {

  $contentType = 'text';

  // Start session and insert post
  session_start();
  $note = new Note;
  $note->storeFormValues( $_POST, $contentType );
  $note->insert();

}
