<?PHP

use src\Business\AdminService;

require_once __DIR__ . '/.inc.head.php';

if(isset($_POST) && !empty($_POST))
{
    $requiredPost = isset($_POST['member-status']) && isset($_POST['round-no']) && isset($_POST['start-date']) && isset($_POST['end-date']) ? true : false;
    if(isset($_POST['submit-reset']) && $security->checkToken($_POST['submit-reset']) && $requiredPost)
    {
        $allowedFields = array("member-status", "round-no", "start-date", "end-date", "keep-team", "remove-families", "next-round-date");
        foreach($_POST AS $key => $value) if(in_array($key, $allowedFields) && isset($value)) $data[$key] = $security->xssEscape($value);
        
        $nrd = isset($data['next-round-date']) && !empty($data['next-round-date']) ? $data['next-round-date'] : null;
        $nrd = isset($nrd) && (DateTime::createFromFormat('Y-m-d H:i:s', $nrd) !== false) ? $nrd : "now";
        $data['next-round-date'] = null;
        
        $round = new AdminService("round");
        if(!isset($data) || !is_array($data) || !array_key_exists($allowedFields[0], $data))
            $response = $route->errorMessage("Je hebt ongeldige instellingen opgegeven!");
        else
            $response = $round->resetMafiasource($data, $nrd);
    }
    elseif(isset($_POST['submit-reset-sure']) && $security->checkToken($_POST['submit-reset-sure']) && $requiredPost)
    {
        $keepTeam = isset($_POST['keep-team']) ? $_POST['keep-team'] : null;
        $removeFamilies = isset($_POST['remove-families']) ? $_POST['remove-families'] : null;
        $responseMsg = "Ben je zeker dat je de volledige game en alle vooruitgang wil resetten?";
        $responseMsg .= $twig->render('/src/Views/admin/Ajax/reset-sure.btns.twig', array('securityToken' => $security->getToken()));
        
        $response = $route->errorMessage($responseMsg);
    }
    else
        $response = $route->errorMessage("Verkeerde gegevens ontvangen. (Vernieuw de pagina met shortkey: f5 of ctrl + f5)");
    
    $twigVars['response'] = $response;
    
    echo $twig->render('/src/Views/admin/Ajax/.default.response.twig', $twigVars);
    exit(0);
}
else
{
    $table = new AdminService("round");
    
    require_once __DIR__ . '/.inc.foot.php';
    $twigVars['offline'] = OFFLINE;
    $twigVars['previousRound'] = $table->getLastRecord()[0][0];
    
    echo $twig->render('/src/Views/admin/reset.twig', $twigVars);
}
