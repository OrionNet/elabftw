<?php
/**
 * app/footer.inc.php
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 */
namespace Elabftw\Elabftw;

$Users = new Users();

if ($Session->has('auth')) {
    // todolist
    $Todolist = new Todolist($Session->get('userid'));
    $Users->setId($Session->get('userid'));
    $todoItems = $Todolist->readAll();

    echo $Twig->render('todolist.html', array(
        'Users' => $Users,
        'todoItems' => $todoItems
    ));
}

// show some stats about generation time and number of SQL queries
$pdo = Db::getConnection();
$sqlNb = $pdo->getNumberOfQueries();
$generationTime = round((microtime(true) - $Request->server->get("REQUEST_TIME_FLOAT")), 5);

echo $Twig->render('footer.html', array(
    'Session' => $Session,
    'Users' => $Users,
    'sqlNb' => $sqlNb,
    'generationTime' => ' ' . $generationTime . ' '
));
