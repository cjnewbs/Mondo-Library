<?php
function __autoload($class)
{
    if (file_exists("$class.php")) {
        require_once("$class.php");
    } else {
        die("Autoloader failed to load $class.php");
    }
}
session_start();
$mondo = new mondo;
require_once 'config.php';
// everything above this line should be present in all pages


$mondo->_state = $_SESSION['state'];

$mondo->_incomingState = $_GET['state'];

$mondo->_tempToken = $_GET['code'];

if ($mondo->_state === $mondo->_incomingState)
{
    
    $post_data = "grant_type=authorization_code&client_id=" . urlencode($mondo->_clientID) . "&client_secret=" . urlencode($mondo->_clientSecret) . "&redirect_uri=" . urlencode($mondo->_redirectURI) . "&code=" . urlencode($mondo->_tempToken);
    
    $url = 'https://api.getmondo.co.uk/oauth2/token';

    $response = $mondo->get($mondo->_tokenExchangeURI, null, $post_data);


    if (json_decode($response) === null){
        echo $response;
    }else {
        echo '<pre>';
        var_dump(json_decode($response));
    }

} else {
    //ABORT
    echo '<p>State data mismatch</p>';
    echo '<p>Possible attempted Cross Site Request Forgery</p>';

}