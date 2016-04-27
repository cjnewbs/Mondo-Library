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

//this needs to be present on pages after the OAuth stage has completed
$mondo->loadSession();
if ($mondo->checkAuthStatus() === true){
    header("Location: http://localhost/mondo/example.php");
    exit('Already logged in, redirecting...');
}else {
    $mondo->_state = $mondo->OAuthCSRF(50);

    $_SESSION['state'] = $mondo->_state;
}
?><html>
<head>
    <meta charset="UTF-8">
    <title>Connect To Mondo</title>
</head>
<body>
<div class="container" style="text-align: center; padding: 25%;">
    <p><a href="<?php echo $mondo->_authPageURI; ?>client_id=<?php echo $mondo->_clientID; ?>&redirect_uri=<?php echo $mondo->_redirectURI; ?>&response_type=code&state=<?php echo $mondo->_state; ?>">Click Here</a></p>
</div>
</body>
</html>