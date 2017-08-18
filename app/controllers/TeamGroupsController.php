<?php
/**
 * app/controllers/TeamGroupsController.php
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
    $TeamGroups = new TeamGroups($Users);

    if (!$Session->get('is_admin')) {
        throw new Exception('Non admin user tried to access admin panel.');
    }

    // CREATE TEAM GROUP
    if ($Request->request->has('teamGroupCreate')) {
        $TeamGroups->create($Request->request->filter('teamGroupCreate', null, FILTER_SANITIZE_STRING));
    }

    // EDIT TEAM GROUP NAME FROM JEDITABLE
    if ($Request->request->has('teamGroupUpdateName')) {
        // the output is echoed so it gets back into jeditable input field
        echo $TeamGroups->update(
            filter_var($_POST['teamGroupUpdateName'], FILTER_SANITIZE_STRING),
            $_POST['id']
        );
    }

    // ADD OR REMOVE USER TO/FROM TEAM GROUP
    if ($Request->request->has('teamGroupUser')) {
        $TeamGroups->updateMember($_POST['teamGroupUser'], $_POST['teamGroupGroup'], $_POST['action']);
    }

    // DESTROY TEAM GROUP
    if ($Request->request->has('teamGroupDestroy')) {
        $TeamGroups->destroy($Request->request->get('teamGroupGroup'));
    }

} catch (Exception $e) {
    $Logs = new Logs();
    $Logs->create('Error', $Session->get('userid'), $e->getMessage());
}
