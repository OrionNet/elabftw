<?php
/**
 * app/controllers/UcpController.php
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
 * Deal with ajax requests sent from the user control panel
 *
 */
try {
    require_once '../../app/init.inc.php';

    $tab = 1;
    $redirect = false;

    // TAB 1 : PREFERENCES
    if ($Request->request->has('lang')) {
        $redirect = true;
        if ($Users->updatePreferences($Request->request->all())) {
            $Session->getFlashBag()->add('ok', _('Preferences updated.'));
        } else {
            $Session->getFlashBag()->add('ko', Tools::error());
        }
    }
    // END TAB 1

    // TAB 2 : ACCOUNT
    if ($Request->request->has('currpass')) {
        $tab = '2';
        $redirect = true;

        if ($Users->updateAccount($Request->request->all())) {
            $Session->getFlashBag()->add('ok', _('Profile updated.'));
        } else {
            $Session->getFlashBag()->add('ko', Tools::error());
        }
    }
    // END TAB 2

    // TAB 3 : EXPERIMENTS TEMPLATES

    // ADD NEW TPL
    if ($Request->request->has('new_tpl_form')) {
        $tab = '3';
        $redirect = true;

        // do nothing if the template name is empty
        if (empty($Request->request->get('new_tpl_name'))) {
            throw new Exception(_('You must specify a name for the template!'));
        }
        // template name must be 3 chars at least
        if (strlen($Request->request->get('new_tpl_name')) < 3) {
            throw new Exception(_('The template name must be 3 characters long.'));
        }

        $tpl_name = $Request->request->filter('new_tpl_name', null, FILTER_SANITIZE_STRING);
        $tpl_body = Tools::checkBody($Request->request->get('new_tpl_body'));

        $Templates = new Templates($Users);
        if (!$Templates->create($tpl_name, $tpl_body, $Session->get('userid'))) {
            throw new Exception(Tools::error());
        }
        $Session->getFlashBag()->add('ok', _('Experiment template successfully added.'));
    }

    // EDIT TEMPLATES
    if ($Request->request->has('tpl_form')) {
        $tab = '3';
        $redirect = true;

        $Templates = new Templates($Users);
        $Templates->update(
            $Request->request->get('tpl_id')[0],
            $Request->request->get('tpl_name')[0],
            $Request->request->get('tpl_body')[0]
        );
        $Session->getFlashBag()->add('ok', _('Template successfully edited.'));
    }

    // TEMPLATES DESTROY
    if ($Request->request->has('templatesDestroy')) {
        if (Tools::checkId($Request->request->get('id')) === false) {
            throw new Exception('The id parameter is invalid!');
        }

        $Templates = new Templates($Users);
        $Response = new JsonResponse();

        if ($Templates->destroy($Request->request->get('id'), $Session->get('userid'))) {
            $Response->setData(array(
                'res' => true,
                'msg' => _('Template deleted successfully')
            ));
        } else {
            $Response->setData(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
    }

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
    $Session->getFlashBag()->add('ko', $e->getMessage());
} finally {
    if ($redirect) {
        $Response = new RedirectResponse("../../ucp.php?tab=" . $tab);
    }
    $Response->send();
}
