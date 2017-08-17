<?php
/**
 * app/controllers/CommentsController.php
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
 * Controller for the experiments comments
 *
 */
try {
    require_once '../../app/init.inc.php';

    $Comments = new Comments(new Experiments($Users));
    $Response = new JsonResponse();

    // CREATE
    if ($Request->request->has('create')) {
        $Comments->Entity->setId($Request->request->get('id'));
        if ($Comments->create($Request->request->get('comment'))) {
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

    // UPDATE
    if ($Request->request->has('update')) {
        if ($Comments->update($Request->request->get('commentsUpdate'), $Request->request->get('id'))) {
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
        if ($Comments->destroy($Request->request->get('id'), $_SESSION['userid'])) {
            $Response->setData(array(
                'res' => true,
                'msg' => _('Comment successfully deleted')
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
    $Logs->create('Error', $_SESSION['userid'], $e->getMessage());
}
