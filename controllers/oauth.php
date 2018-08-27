<?php

class OauthController extends PluginController
{
    public function request_access_token_action()
    {
        URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        //Muss den Nutzer weiterleiten auf den Server, wo der Nutzer die App freischaltet
        $powerfolder = Config::get()->POWERFOLDER_ENDPOINT ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT_USER;
        if ($powerfolder[strlen($powerfolder) - 1] !== "/") {
            $powerfolder .= "/";
        }
        URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $client_id = Config::get()->POWERFOLDER_CLIENT_ID ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_CLIENT_ID_USER;
        $redirect_uri = PluginEngine::getURL($this->plugin, array(), "oauth/receive_access_token", true);

        $url = $powerfolder."oauth/allow";

        $_SESSION['oauth2state'] = md5(uniqid());
        $url .= "?state=".urlencode($_SESSION['oauth2state'])
                . "&response_type=code"
                . "&redirect_uri=".rawurlencode($redirect_uri)
                . "&client_id=".urlencode($client_id);

        header("Location: ".$url);
        $this->render_nothing();
    }

    public function receive_access_token_action()
    {
        //Save the access token and refresh-token
        $powerfolder = Config::get()->POWERFOLDER_ENDPOINT ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT_USER;
        if ($powerfolder[strlen($powerfolder) - 1] !== "/") {
            $powerfolder .= "/";
        }

        if (Request::get("state") !== $_SESSION['oauth2state']) {
            throw new AccessDeniedException("State stimmt nicht überein. Anfrage wird abgewiesen. Probieren Sie es erneut.");
        }


        $client_id  = \Config::get()->POWERFOLDER_CLIENT_ID ?: \UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_CLIENT_ID_USER; // The client ID assigned to you by the provider
        $client_secret = \Config::get()->POWERFOLDER_CLIENT_SECRET ?: \UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_CLIENT_SECRET_USER; // The client password assigned to you by the provider

        $payload = array(
            'grant_type' => "authorization_code",
            'code' => Request::get("code"),
            'client_id' => $client_id,
            'client_secret' => $client_secret
        );

        $header = array();
        $header[] = "Accept: application/json";
        $header[] = "Content-Type: application/json";
        //$header[] = "Authorization: Basic ".base64_encode($client_id . ":" .$client_secret);

        $r = curl_init();
        curl_setopt($r, CURLOPT_URL, $powerfolder."oauth/token"); //powerfolder
        curl_setopt($r, CURLOPT_POST, 1);
        curl_setopt($r, CURLOPT_HTTPHEADER, $header);
        curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($r, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($r);
        curl_close($r);

        $json = json_decode($response, true);

        if (!$json['access_token']) {
            var_dump($response);
            die();
            PageLayout::postError(_("Authentifizierungsfehler:")." ".$response);
            $this->redirect(URLHelper::getURL("dispatch.php/files/index"));
        } else {
            if ($response === false) {
                PageLayout::postError(_("Fehler beim Abrufen der OAuth-Token:"), array(curl_error($r)));
            }
            if (false) {
                var_dump($json);
                $this->render_nothing();
                return;
            }
            $config = \UserConfig::get($GLOBALS['user']->id);
            $config->store("POWERFOLDER_ACCESS_TOKEN", $json['access_token']);
            $config->store("POWERFOLDER_REFRESH_TOKEN", $json['refresh_token']);
            $config->store("POWERFOLDER_ACCESS_TOKEN_EXPIRES", time() + $json['expires_in']);
            if (UserConfig::get($GLOBALS['user']->id)->OWNCLOUD_ACTIVATED) {
                $this->redirect(URLHelper::getURL("dispatch.php/files/system/" . $this->plugin->getPluginId()));
            } else {
                $this->redirect(URLHelper::getURL("dispatch.php/files"));
            }
        }


    }
}

