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

$mondo->saveSession();

if ($mondo->_state === $mondo->_incomingState)
{
    
    $post_data = "grant_type=authorization_code&client_id=" . urlencode($mondo->_clientID) . "&client_secret=" . urlencode($mondo->_clientSecret) . "&redirect_uri=" . urlencode($mondo->_redirectURI) . "&code=" . urlencode($mondo->_tempToken);
    
    $response = $mondo->get($mondo->_tokenExchangeURI, null, $post_data);
    
    if (json_decode($response) === null){
        echo $response;
    }else {
        $mondo_values = json_decode($response, true);
        $_SESSION['access_token'] = $mondo_values['access_token'];
        $_SESSION['refresh_token'] = $mondo_values['refresh_token'];
        $_SESSION['user_id'] = $mondo_values['user_id'];
        
        echo '<p><a href="example.php">Get Account Info</a></p>';
    }

} else {
    //ABORT
    echo '<p>State data mismatch</p>';
    echo '<p>Possible attempted Cross Site Request Forgery</p>';

}