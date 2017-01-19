<?php
/**
 * \Elabftw\Elabftw\Api
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
 * An API for elab
 * Get your api key from your profile page.
 * Send it in an Authorization header like so:
 * curl -kL -X GET -H "Authorization: $API_KEY" "https://elabftw.example.org/app/api/v1/items/7"
 */
class Api
{
    /** http method GET POST PUT DELETE */
    public $method;

    /** the model (experiments/items) */
    private $endpoint;

    /** optional arguments, like the id */
    public $args = array();

    /** our user */
    private $user;

    /**
     * Get data for user from the API key
     *
     */
    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($_GET['req'], '/'));
        $this->endpoint = array_shift($this->args);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $Users = new Users();
        if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
            throw new Exception('No API key received.');
        }
        $this->user = $Users->readFromApiKey($_SERVER['HTTP_AUTHORIZATION']);
        if (empty($this->user)) {
            throw new Exception('Invalid API key.');
        }
    }

    /**
     * Read an entity
     *
     * @param int|null $id id of the entity
     */
    public function getEntity($id = null) {
        if ($this->endpoint === 'experiments') {
            $Entity = new Experiments($this->user['team'], $this->user['userid'], $id);
        } elseif ($this->endpoint === 'items') {
            $Entity = new Database($this->user['team'], $this->user['userid'], $id);
        } else {
            return json_encode(array('error', 'Bad endpoint.'));
        }

        if (!$Entity->canRead) {
            throw new Exception(Tools::error(true));
        }

        return json_encode($Entity->entityData);
    }
}