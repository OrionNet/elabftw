<?php
/**
 * revisions.php
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
 * Show history of body of experiment or db item
 *
 */
try {
    require_once 'app/init.inc.php';
    $pageTitle = _('Revisions');
    $errflag = false;
    require_once 'app/head.inc.php';

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

    // BEGIN PAGE
    echo "<a href='" . $Entity::PAGE . ".php?mode=view&id=" . $_GET['item_id'] .
        "'><h4><img src='app/img/undo.png' alt='<--' /> " . _('Go back') . "</h4></a>";
    $revisionArr = $Revisions->read();
    foreach ($revisionArr as $revision) {
        echo "<div class='item'>" . _('Saved on:') . " " . $revision['savedate'] .
            " <a href='app/controllers/RevisionsController.php?item_id=" . $_GET['item_id'] .
            "&type=" . $_GET['type'] . "&action=restore&rev_id=" . $revision['id'] . "'>" . _('Restore') . "</a><br>";
        echo $revision['body'] . "</div>";
    }

} catch (Exception $e) {
    echo Tools::displayMessage($e->getMessage(), 'ko');
} finally {
    require_once 'app/footer.inc.php';
}
