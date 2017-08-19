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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    $res = false;
    $msg = Tools::error();

    $Teams = new Teams();
    $Config = new Config();
    $Response = new JsonResponse();

    // PROMOTE SYSADMIN
    if ($Request->request->has('promoteSysadmin')) {
        if ($Users->promoteSysadmin($Request->request->get('email'))) {
            $res = true;
            $msg = _('User promoted');
        }
    }

    // CREATE TEAM
    if ($Request->request->has('teamsCreate')) {
        if ($Teams->create($Request->request->get('teamsName'))) {
            $res = true;
            $msg = _('Saved');
        }
    }

    // UPDATE TEAM
    if ($Request->request->has('teamsUpdate')) {
        $orgid = "";
        if ($Request->request->has('teamsUpdateOrgid')) {
            $orgid = $Request->request->get('teamsUpdateOrgid');
        }
        if ($Teams->updateName(
            $Request->request->get('teamsUpdateId'),
            $Request->request->get('teamsUpdateName'),
            $orgid
        )) {
            $res = true;
            $msg = _('Saved');
        }
    }

    // DESTROY TEAM
    if ($Request->request->has('teamsDestroy')) {
        if ($Teams->destroy($Request->request->get('teamsDestroyId'))) {
            $res = true;
            $msg = _('Saved');
        }
    }

    // SEND TEST EMAIL
    if ($Request->request->has('testemailSend')) {
        $Sysconfig = new Sysconfig(new Email($Config));
        if ($Sysconfig->testemailSend($Request->request->get('testemailEmail'))) {
            $res = true;
            $msg = _('Email sent');
        }
    }

    // SEND MASS EMAIL
    if ($Request->request->has('massEmail')) {
        $Sysconfig = new Sysconfig(new Email($Config));
        if ($Sysconfig->massEmail($Request->request->get('subject'), $Request->request->get('body'))) {
            $res = true;
            $msg = _('Email sent');
        }
    }

    // DESTROY LOGS
    if ($Request->request->has('logsDestroy')) {
        $Logs = new Logs();
        if ($Logs->destroy()) {
            $res = true;
            $msg = _('Logs cleared');
        }
    }

    // TAB 3 to 6 + 8
    if ($Request->request->has('updateConfig')) {
        $redirect = true;

        if ($Request->request->has('lang')) {
            $tab = '3';
        }

        if ($Request->request->has('stampshare')) {
            $tab = '4';
        }

        if ($Request->request->has('admin_validate')) {
            $tab = '5';
        }

        if ($Request->request->has('mail_method')) {
            $tab = '6';
        }

        if ($Request->request->has('saml_debug')) {
            $tab = '8';
        }

        if ($Config->update($Request->request->all())) {
            $res = true;
            $msg = _('Saved');
        }
    }

    // CLEAR STAMP PASS
    if ($Request->query->get('clearStamppass')) {
        $redirect = true;
        $tab = '4';
        if ($Config->destroyStamppass()) {
            $res = true;
            $msg = _('Saved');
        }
    }

    $Response->setData(array(
        'res' => $res,
        'msg' => $msg
    ));

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
    // we can show error message to sysadmin
    $Session->getFlashBag()->add('ko', $e->getMessage());
} finally {
    if ($redirect) {
        $Session->getFlashBag()->add('ok', _('Configuration updated successfully.'));
        $Response = new RedirectResponse("../../sysconfig.php?tab=" . $tab);
    }
    $Response->send();
}
