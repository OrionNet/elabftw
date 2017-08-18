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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Deal with requests sent from the admin page
 *
 */
try {
    require_once '../../app/init.inc.php';

    if (!$Session->get('is_admin')) {
        throw new Exception('Non admin user tried to access admin panel.');
    }

    $Response = new JsonResponse();

    // UPDATE ORDERING
    if ($Request->request->has('updateOrdering')) {
        if ($Request->request->get('table') === 'status') {
            $Entity = new Status($Users);
        } elseif ($Request->request->get('table') === 'items_types') {
            $Entity = new ItemsTypes($Users);
        } elseif ($Request->request->get('table') === 'experiments_templates') {
            // remove the create new entry
            unset($Request->request->get('ordering')[0]);
            $Entity = new Templates($Users);
        }

        if ($Entity->updateOrdering($Request->request->all())) {
            $Response->setData(array(
                'res' => true,
                'msg' => _('Saved')
            ));
        } else {
            $Response->setData(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
    }

    // UPDATE TEAM SETTINGS
    if ($Request->request->has('teamsUpdateFull')) {
        $Teams = new Teams($Session->get('team'));
        if ($Teams->update($Request->request->all())) {
            $Session->getFlashBag()->add('ok', _('Configuration updated successfully.'));
        } else {
            $Session->getFlashBag()->add('ko', Tools::error());
        }
        $Response = new RedirectResponse("../../admin.php?tab=1");
    }

    // CLEAR STAMP PASS
    if ($Request->request->get('clearStamppass')) {
        $Teams = new Teams($Session->get('team'));
        if (!$Teams->destroyStamppass()) {
            throw new Exception('Error clearing the timestamp password');
        }
        $Response = new RedirectResponse("../../admin.php?tab=1");
    }

    // UPDATE COMMON TEMPLATE
    if ($Request->request->has('commonTplUpdate')) {
        $Templates = new Templates($Users);
        if ($Templates->updateCommon($Request->request->get('commonTplUpdate'))) {
            $Response->setData(array(
                'res' => true,
                'msg' => _('Saved')
            ));
        } else {
            $Response->setData(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
    }

    $Response->send();

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
}
