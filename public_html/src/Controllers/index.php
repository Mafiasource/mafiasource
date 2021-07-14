<?PHP

require_once __DIR__ . '/.inc.head.php';

require_once __DIR__ . '/.inc.online.message.php';
require_once __DIR__ . '/.inc.statistics.php';
require_once __DIR__ . '/.inc.sliders.php';

$totalPlayers = $user->getUsersCount();
$langs["PLAYERS_BEFORE_MSG"] = $route->replaceMessagePart("<strong>".$totalPlayers."</strong>", $langs["PLAYERS_BEFORE_MSG"], '/{totalPlayers}/');

require_once __DIR__ . '/.inc.foot.php';

// Render view
echo $twig->render('/src/Views/index.twig', $twigVars);