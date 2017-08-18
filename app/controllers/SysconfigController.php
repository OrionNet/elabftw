<?php
/**
 * app/controllers/SysconfigController.php
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
 * Deal with ajax requests sent from the sysconfig page or full form from sysconfig.php
 *
 */
try {
    require_once '../../app/init.inc.php';

    if (!$Session->get('is_sysadmin')) {
        throw new Exception('Non sysadmin user tried to access sysadmin panel.');
    }

    $tab = '1';
    $redirect = false;

    $Teams = new Teams();
    $Config = new Config();

    // PROMOTE SYSADMIN
    if ($Request->request->has('promoteSysadmin')) {
        $Users = new Users();
        if ($Users->promoteSysadmin($_POST['email'])) {
            echo json_encode(array(
                'res' => true,
                'msg' => _('User promoted')
            ));
        } else {
            echo json_encode(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
    }

    // CREATE TEAM
    if ($Request->request->has('teamsCreate')) {
        if ($Teams->create($_POST['teamsName'])) {
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

    // UPDATE TEAM
    if ($Request->request->has('teamsUpdate')) {
        $orgid = "";
        if (isset($_POST['teamsUpdateOrgid'])) {
            $orgid = $_POST['teamsUpdateOrgid'];
        }
        if ($Teams->updateName($_POST['teamsUpdateId'], $_POST['teamsUpdateName'], $orgid)) {
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

    // DESTROY TEAM
    if ($Request->request->has('teamsDestroy')) {
        if ($Teams->destroy($_POST['teamsDestroyId'])) {
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

    // SEND TEST EMAIL
    if ($Request->request->has('testemailSend')) {
        $Sysconfig = new Sysconfig(new Email($Config));
        if ($Sysconfig->testemailSend($_POST['testemailEmail'])) {
            echo json_encode(array(
                'res' => true,
                'msg' => _('Email sent')
            ));
        } else {
            echo json_encode(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
    }

    // SEND MASS EMAIL
    if ($Request->request->has('massEmail')) {
        $Sysconfig = new Sysconfig(new Email($Config));
        if ($Sysconfig->massEmail($_POST['subject'], $_POST['body'])) {
            echo json_encode(array(
                'res' => true,
                'msg' => _('Email sent')
            ));
        } else {
            echo json_encode(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
    }

    // DESTROY LOGS
    if ($Request->request->has('logsDestroy')) {
        $Logs = new Logs();
        if ($Logs->destroy()) {
            echo json_encode(array(
                'res' => true,
                'msg' => _('Logs cleared')
            ));
        } else {
            echo json_encode(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
    }

    // TAB 3 to 6 + 8
    if ($Request->request->has('updateConfig')) {
        $redirect = true;

        if (isset($_POST['lang'])) {
            $tab = '3';
        }

        if (isset($_POST['stampshare'])) {
            $tab = '4';
        }

        if (isset($_POST['admin_validate'])) {
            $tab = '5';
        }

        if (isset($_POST['mail_method'])) {
            $tab = '6';
        }

        if (isset($_POST['saml_debug'])) {
            $tab = '8';
        }

        if (!$Config->update($_POST)) {
            throw new Exception('Error updating config');
        }

    }

    // CLEAR STAMP PASS
    if (isset($_GET['clearStamppass']) && $_GET['clearStamppass'] === '1') {
        $redirect = true;
        $tab = '4';
        if (!$Config->destroyStamppass()) {
            throw new Exception('Error clearing the timestamp password');
        }
    }

    $Session->getFlashBag()->add('ok', _('Configuration updated successfully.'));

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
    // we can show error message to sysadmin
    $Session->getFlashBag()->add('ko', $e->getMessage());
} finally {
    if ($redirect) {
        header('Location: ../../sysconfig.php?tab=' . $tab);
    }
}
