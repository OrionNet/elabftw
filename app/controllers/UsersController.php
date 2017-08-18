<?php
/**
 * app/controllers/UsersController.php
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
 * Users infos from admin page
 */
$redirect = true;

try {
    require_once '../../app/init.inc.php';

    $FormKey = new FormKey($Session);

    // (RE)GENERATE AN API KEY (from profile)
    if (isset($_POST['generateApiKey'])) {
        $redirect = false;
        if ($Users->generateApiKey()) {
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

    $tab = 1;
    $location = '../../admin.php?tab=' . $tab;

    // VALIDATE
    if (isset($_POST['usersValidate'])) {
        $tab = 2;
        if (!$Session->get('is_admin')) {
            throw new Exception('Non admin user tried to access admin panel.');
        }

        // loop the array
        foreach ($_POST['usersValidateIdArr'] as $userid) {
            $Session->getFlashBag()->add('ok', $Users->validate($userid));
        }
    }

    // UPDATE USERS
    if (isset($_POST['usersUpdate'])) {
        $tab = 2;
        if (!$Session->get('is_admin')) {
            throw new Exception('Non admin user tried to access admin panel.');
        }
        if (isset($_POST['fromSysconfig'])) {
            $location = "../../sysconfig.php?tab=$tab";
        } else {
            $location = "../../admin.php?tab=$tab";
        }

        if ($Users->update($_POST)) {
            $Session->getFlashBag()->add('ok', _('Configuration updated successfully.'));
        }
    }

    // DESTROY
    if ($FormKey->validate($Request->request->get('formkey'))
        && $Request->request->has('usersDestroy')) {

        $tab = 2;
        if (!$Session->get('is_admin')) {
            throw new Exception('Non admin user tried to access admin panel.');
        }

        if ($Users->destroy(
            $Request->request->get('usersDestroyEmail'),
            $Request->request->get('usersDestroyPassword')
        )) {
            $Session->getFlashBag()->add('ok', _('Everything was purged successfully.'));
        }
    }

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
    $Session->getFlashBag()->add('ko', Tools::error());

} finally {
    if ($redirect) {
        header("Location: $location");
    }
}
