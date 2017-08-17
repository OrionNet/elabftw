<?php
/**
 * register.php
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Create an account
 *
 */
try {
    require_once 'app/init.inc.php';
    $pageTitle = _('Register');
    require_once 'app/head.inc.php';

    // Check if we're logged in
    if (isset($_SESSION['auth']) && $_SESSION['auth'] == 1) {
        throw new Exception(sprintf(
            _('Please %slogout%s before you register another account.'),
            "<a style='alert-link' href='app/logout.php'>",
            "</a>"
        ));
    }

    $Config = new Config();
    // local register might be disabled
    if ($Config->configArr['local_register'] === '0') {
        throw new Exception(_('No local account creation is allowed!'));
    }

    $Teams = new Teams();
    $teamsArr = $Teams->readAll();

    $Response = new Response();
    $Response->setContent($Twig->render('register.html', array(
        'teamsArr' => $teamsArr
    )));
    $Response->send();

} catch (Exception $e) {
    echo Tools::displayMessage($e->getMessage(), 'ko', false);
} finally {
    require_once 'app/footer.inc.php';
}
