<?PHP

use src\Business\UserService;
use src\Business\CasinoService;
use src\Business\PossessionService;

require_once __DIR__ . '/.inc.head.ajax.php';

$possession = new PossessionService();
$possessionId = 13; // Dobbling | Possession logic
$possessId = $possession->getPossessIdByPossessionId($possessionId, $userData->getStateID(), $userData->getCityID()); // Possess table record id |Stad bezitting
$pData = $possession->getPossessionByPossessId($possessId); // Possession table data + possess table data

$casinoService = new CasinoService($pData);

if(isset($_POST['security-token']) && isset($_POST['stake']) && isset($_POST['play-dobbling']))
{
    $userService = new UserService();
    
    require_once __DIR__ . '/.valuesAnimation.php';
    $userDataBefore = $userData;
    $cashMoneyBefore = $userDataBefore->getCash();
    
    $response = $casinoService->playDobbling($_POST, $pData);
    
    $userDataAfter = $user->getUserData();
    $cashMoneyAfter = $userDataAfter->getCash();
    
    require_once __DIR__ . '/.inc.foot.ajax.php';
    $twigVars['response'] = $response;
    
    echo $twig->render('/src/Views/game/Ajax/.default.response.twig', $twigVars);
    
    require_once __DIR__ . '/.moneyAnimation.php';
    if(isset($cashMoneyBefore) && isset($cashMoneyAfter) && $cashMoneyBefore != $cashMoneyAfter) valueAnimation("#casinoStakeAmount", $cashMoneyBefore, $cashMoneyAfter);
}