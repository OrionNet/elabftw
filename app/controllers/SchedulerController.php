<?php
/**
 * app/controllers/SchedulerController.php
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

/**
 * Controller for the scheduler
 *
 */
try {
    require_once '../../app/init.inc.php';
    $Database = new Database($Users);
    $Scheduler = new Scheduler($Database);
    $Response = new JsonResponse();

    // CREATE
    if ($Request->request->has('create')) {
        $Database->setId($Request->request->get('item'));
        if ($Scheduler->create(
            $Request->request->get('start'),
            $Request->request->get('end'),
            $Request->request->get('title')
        )) {
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

    // READ
    if ($Request->request->has('read')) {
        $Database->setId($Request->request->get('item'));
        $Response->setData($Scheduler->read());
    }

    // UPDATE START
    if ($Request->request->has('updateStart')) {
        $Scheduler->setId($Request->request->get('id'));
        if ($Scheduler->updateStart($Request->request->get('start'), $Request->request->get('end'))) {
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
    // UPDATE END
    if ($Request->request->has('updateEnd')) {
        $Scheduler->setId($Request->request->get('id'));
        if ($Scheduler->updateEnd($Request->request->get('end'))) {
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
    // DESTROY
    if ($Request->request->has('destroy')) {
        $Scheduler->setId($Request->request->get('id'));
        $eventArr = $Scheduler->readFromId();
        if ($eventArr['userid'] != $Session->get('userid')) {
            $Response->setData(array(
                'res' => false,
                'msg' => Tools::error(true)
            ));
        } else {
            if ($Scheduler->destroy()) {
                $Response->setData(array(
                    'res' => true,
                    'msg' => _('Event deleted successfully')
                ));
            } else {
                $Response->setData(array(
                    'res' => false,
                    'msg' => Tools::error()
                ));
            }
        }
    }

    $Response->send();

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
}
