<?php
/**
 * app/controllers/RevisionsController.php
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
 * Revisions controller
 */
try {
    require_once '../../app/init.inc.php';

    if ($Request->query->get('type') === 'experiments') {
        $Entity = new Experiments($Users);

    } elseif ($_GET['type'] === 'items') {

        $Entity = new Database($Users);

    } else {
        throw new Exception('Bad type!');
    }

    $Entity->setId($Request->query->get('item_id'));
    $Entity->canOrExplode('write');
    $Revisions = new Revisions($Entity);

    if ($Request->query->get('action') === 'restore') {
        $revId = Tools::checkId($Request->query->get('rev_id'));
        if ($revId === false) {
            throw new Exception(_('The id parameter is not valid!'));
        }

        $Revisions->restore($revId);

        header("Location: ../../" . $Entity::PAGE . ".php?mode=view&id=" . $Request->query->get('item_id'));
    }
} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
}
