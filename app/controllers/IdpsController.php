<?php
/**
 * app/controllers/IdpsController.php
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

use Exception;

/**
 * Controller for IDPs
 *
 */
try {
    require_once '../../app/init.inc.php';
    $Idps = new Idps();

    if (!$_SESSION['is_sysadmin']) {
        throw new Exception('Non sysadmin user tried to access sysadmin controller.');
    }

    // CREATE IDP
    if (isset($_POST['idpsCreate'])) {
        if ($Idps->create(
            $Request->request->get('name'),
            $Request->request->get('entityid'),
            $Request->request->get('ssoUrl'),
            $Request->request->get('ssoBinding'),
            $Request->request->get('sloUrl'),
            $Request->request->get('sloBinding'),
            $Request->request->get('x509')
        )) {
            $_SESSION['ok'][] = _('Configuration updated successfully.');
        } else {
            $_SESSION['ko'][] = _('An error occurred!');
        }
    }

    // UPDATE IDP
    if (isset($_POST['idpsUpdate'])) {
        if ($Idps->update(
            $Request->request->get('id'),
            $Request->request->get('name'),
            $Request->request->get('entityid'),
            $Request->request->get('ssoUrl'),
            $Request->request->get('ssoBinding'),
            $Request->request->get('sloUrl'),
            $Request->request->get('sloBinding'),
            $Request->request->get('x509')
        )) {
            $_SESSION['ok'][] = _('Configuration updated successfully.');
        } else {
            $_SESSION['ko'][] = _('An error occurred!');
        }
    }

    // DESTROY IDP
    if ($Request->request->has('idpsDestroy')) {
        if ($Idps->destroy($Request->request->get('id'))) {
            $_SESSION['ok'][] = _('Configuration updated successfully.');
        } else {
            $_SESSION['ko'][] = _('An error occurred!');
        }
    }

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $_SESSION['userid'], $e->getMessage());
} finally {
    header('Location: ../../sysconfig.php?tab=8');
}
