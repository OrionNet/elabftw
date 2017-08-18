<?php
/**
 * LoginController.php
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

use Exception;
use OneLogin_Saml2_Auth;

try {
    require_once '../init.inc.php';

    // default location for redirect
    $location = '../../login.php';

    $FormKey = new FormKey($Session);
    $Auth = new Auth();
    $Saml = new Saml(new Config, new Idps);

    if ($Request->request->has('idp_id')) { // login with SAML
        $idpId = $Request->request->get('idp_id');
        $settings = $Saml->getSettings($idpId);
        $SamlAuth = new OneLogin_Saml2_Auth($settings);
        $returnUrl = $settings['baseurl'] . "/index.php?acs&idp=" . $idpId;
        $SamlAuth->login($returnUrl);

    } else {

        // FORMKEY
        if (!$Request->request->has('formkey') || !$FormKey->validate($Request->request->get('formkey'))) {
            throw new Exception(_("Your session expired. Please retry."));
        }

        // EMAIL
        if (!$Request->request->has('email') || !$Request->request->has('password')) {
            throw new Exception(_('A mandatory field is missing!'));
        }

        if ($Request->request->has('rememberme')) {
            $rememberme = $Request->request->get('rememberme');
        } else {
            $rememberme = 'off';
        }

        // the actual login
        if ($Auth->login($Request->request->get('email'), $Request->request->get('password'), $rememberme)) {
            if ($Request->cookies->has('redirect')) {
                $location = $Request->cookies->get('redirect');
            } else {
                $location = '../../experiments.php';
            }
        } else {
            // log the attempt if the login failed
            $Logs = new Logs();
            $Logs->create('Warning', $_SERVER['REMOTE_ADDR'], 'Failed login attempt');
            // inform the user
            $Session->getFlashBag()->add(
                'ko',
                _("Login failed. Either you mistyped your password or your account isn't activated yet.")
            );
            var_dump($Session->all());
            if (!$Session->has('failed_attempt')) {
                $Session->set('failed_attempt', 1);
            } else {
                $n = $Session->get('failed_attempt');
                $n += 1;
                //$Session->set('failed_attempt', $Session->get('failed_attempt') + 1);
                $Session->set('failed_attempt', $n);
            }
        }
    }

} catch (Exception $e) {
    $Session->getFlashBag()->add('ko', $e->getMessage());

} finally {
    header("location: $location");
}
