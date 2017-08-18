<?php
/**
 * app/controllers/AdminController.php
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
 * Deal with ajax requests sent from the admin page
 *
 */
try {
    require_once '../../app/init.inc.php';

    $redirect = false;

    if (!$Session->get('is_admin')) {
        throw new Exception('Non admin user tried to access admin panel.');
    }

    // UPDATE ORDERING
    if ($Request->request->has('updateOrdering')) {
        if ($_POST['table'] === 'status') {
            $Entity = new Status($Users);
        } elseif ($_POST['table'] === 'items_types') {
            $Entity = new ItemsTypes($Users);
        } elseif ($_POST['table'] === 'experiments_templates') {
            // remove the create new entry
            unset($_POST['ordering'][0]);
            $Entity = new Templates($Users);
        }

        if ($Entity->updateOrdering($_POST)) {
            echo json_encode(array(
                'res' => true,
                'msg' => _('Saved')
            ));
        } else {
            echo json_encode(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
    }

    // UPDATE TEAM SETTINGS
    if ($Request->request->has('teamsUpdateFull')) {
        $redirect = true;
        $Teams = new Teams($Session->get('team'));
        if ($Teams->update($_POST)) {
            $_SESSION['ok'][] = _('Configuration updated successfully.');
        } else {
            $_SESSION['ko'][] = _('An error occurred!');
        }
    }

    // CLEAR STAMP PASS
    if ($Request->request->get('clearStamppass')) {
        $redirect = true;
        $Teams = new Teams($Session->get('team'));
        if (!$Teams->destroyStamppass()) {
            throw new Exception('Error clearing the timestamp password');
        }
    }

    // UPDATE COMMON TEMPLATE
    if ($Request->request->has('commonTplUpdate')) {
        $Templates = new Templates($Users);
        $Templates->updateCommon($Request->request->get('commonTplUpdate'));
    }

    if ($redirect) {
        header('Location: ../../admin.php?tab=1');
    }

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
}
