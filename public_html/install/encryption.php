<?PHP

// Mafiasource online mafia RPG, this software is inspired by Crimeclub.
// Copyright � 2016 Michael Carrein, 2006 Crimeclub.nl
//
// Permission is hereby granted, free of charge, to any person obtaining a
// copy of this software and associated documentation files (the �Software�),
// to deal in the Software without restriction, including without limitation
// the rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Software, and to permit persons to whom the
// Software is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included
// in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED �AS IS�, WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
// NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
// OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
// THE USE OR OTHER DEALINGS IN THE SOFTWARE.

/** Front-controller - Main entry point installation of web application **/

use Doctrine\Common\ClassLoader;
use app\config\Routing;
use app\config\Security;
use install\config\InstallService;

// Set correct timezone
ini_set('date.timezone', 'Europe/Amsterdam');

// Include routing: controller > views
require_once __DIR__.'/../app/config/routing.php';
$route = new Routing();

// Set error reporting according to DEVELOPMENT global (/app/config/config.php)
$errRepInt = DEVELOPMENT === true ? 1 : 0;
ini_set("log_errors", $errRepInt);
ini_set('display_errors', $errRepInt);
ini_set('display_startup_errors', $errRepInt);
if($errRepInt === 0)
    error_reporting($errRepInt);
else
    error_reporting(-1);

$errRepInt = null;

$stream = @stream_socket_server('tcp://0.0.0.0:7600', $errno, $errmg, STREAM_SERVER_BIND);
if($stream)
{
    // Enable Autoloading with doctrine
    require_once DOC_ROOT . '/vendor/Doctrine/Common/ClassLoader.php';
    $classLoader = new ClassLoader('install'   ,   DOC_ROOT);
    $classLoader->register();
    $classLoader = null;
    
    // Start a (non-secure) installation session (Allow 'temp' sessions in install env)
    require_once __DIR__.'/../vendor/sessionManager.php';
    $session = new SessionManager();
    ini_set('session.save_handler', 'files');
    session_set_save_handler($session, true);
    session_save_path(__DIR__ . '/../app/cache/sessions');
    SessionManager::sessionStart("Mafiasource_Install");
    $session = null;
    
    // Security class (Anti CSRF, XSS attacks & more)
    require_once __DIR__.'/../app/config/security.php';
    $security = new Security();
    
    // Define PROTOCOL used throughout the application http / https? see: app/config/config.php
    if($route->settings['ssl'] === true) define("PROTOCOL", "https://"); else define("PROTOCOL", "http://");
    
    // Enable Twig engine & set some custom filters used throughout the application
    require_once __DIR__.'/../vendor/Twig/autoload.php';
    $loader = new \Twig\Loader\FilesystemLoader(DOC_ROOT); // Root templates folder to DOC root (Because we have tmpls in app/ and src/ )
    $twig = new \Twig\Environment($loader, [
        'cache' => $route->settings['twigCache'] // Caching? depends on dev mode see: app/config/config.php
    ]);
    $loader = null;
    require_once __DIR__.'/../app/config/twig.filters.php';
    
    $installService = new InstallService("localhost", "ms", "root", "");
    
    $securityFile = DOC_ROOT . '/../security.php';
    $securityReplacesMap = array();
    $masterEncryption = InstallService::generateMasterEncryptionIvAndKey();
    $securityReplacesMap[8] = "define('MASTERIV', stripslashes('" . addslashes(str_replace(array("\r", "\n"), '', $masterEncryption['iv'])) . "'));";
    $securityReplacesMap[9] = "define('MASTERKEY', stripslashes('" . addslashes(str_replace(array("\r", "\n"), '', $masterEncryption['key'])) . "'));";
    InstallService::replaceLinesByLineNumbers($securityFile, $securityReplacesMap);
    $_SESSION['install']['masterEncryption'] = true;
    $route->createActionMessage(Routing::successMessage("New master encryption keys were generated and written to the security file."));
    
    $message = $route->setActionMessage();
    $twigVars = array(
        'routing' => $route,
        'settings' => $route->settings,
        'securityToken' => $security->getToken(),
        'message' => $message,
        'domain' => $_SERVER['HTTP_HOST'],
        'protocol' => PROTOCOL,
        'offline' => OFFLINE
    );
    
    echo $twig->render('/install/Views/encryption.twig', $twigVars);
    
    // Session lockdown after controller did its job
    SessionManager::sessionWriteClose();
    fclose($stream);
}