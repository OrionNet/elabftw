<?php
/**
 * \Elabftw\Elabftw\Formkey
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

/**
 * Generate and validate keys for input forms.
 * **Note** : for a page with several *form* elements this will work only for 1 *form*!
 */
class Formkey
{
    /** here we store the generated form key */
    private $formkey;

    /** here we store the old form key */
    private $oldFormkey;

    /**
     * Store the form key that was previously set
     *
     */
    public function __construct()
    {
        if (isset($_SESSION['formkey'])) {
            $this->oldFormkey = $_SESSION['formkey'];
        }
    }

    /**
     * Generate the form key based on IP address
     *
     * @return string md5sum of IP address + unique number
     */
    private function generateFormkey()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        // mt_rand() is better than rand()
        $uniqid = uniqid(mt_rand(), true);

        return md5($ip . $uniqid);
    }

    /**
     * Return the form key for inclusion in HTML
     *
     * @return string $hinput Hidden input html
     */
    public function getFormkey()
    {
        // generate the key and store it inside the class
        $this->formkey = $this->generateFormkey();
        // store the form key in the session
        $_SESSION['formkey'] = $this->formkey;
        // output the form key
        return "<input type='hidden' name='formkey' value='" . $this->formkey . "' />";
    }

    /**
     * Validate the form key against the one previously set
     *
     * @return bool True if there is no CSRF going on (hopefully)
     */
    public function validate()
    {
        return $_POST['formkey'] === $this->oldFormkey;
    }
}
