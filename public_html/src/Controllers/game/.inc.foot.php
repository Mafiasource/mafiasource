<?PHP

$message = $route->setActionMessage();
$twigVars = array(
    'routing' => $route,
    'settings' => $route->settings,
    'securityToken' => $security->getToken(),
    'langs' => $langs,
    'lang' => $lang,
    'message' => $message,
    'userData' => $userData,
    'online' => $user->getOnlinePlayers(),
    'prisonersCount' => $user->getPrisonersCount(),
    'time' => time(),
    'serverTime' => $serverTime,
    'statusDonatorColors' => $user->getStatusAndDonatorColors(),
    'lastShoutboxID' => $lastShoutboxID,
    'lastFamilyShoutboxID' => $lastFamilyShoutboxID,
    'unvotedPoll' => $unvotedPoll,
    'offline' => OFFLINE
);
$twigVars['langs']['TRAVELING'] = $route->replaceMessagePart($travelCounter, $twigVars['langs']['TRAVELING'], '/{sec}/');
if(strtotime("2021-01-01 14:00:00") < strtotime('now') && strtotime("2021-01-04 14:00:00") > strtotime('now'))
{
    $twigVars['eventName'] = "Credits x2";
    $twigVars['eventCountdown'] = countdownHmsTime("EventCountdown", strtotime("2021-01-04 14:00:00") - time());
}
if(strtotime("2021-01-05 14:00:00") < strtotime('now') && strtotime("2021-01-08 14:00:00") > strtotime('now'))
{
    $twigVars['eventName'] = $twigVars['langs']['WAITING_TIMES'] . " /2";
    $twigVars['eventCountdown'] = countdownHmsTime("EventCountdown", strtotime("2021-01-08 14:00:00") - time());
}