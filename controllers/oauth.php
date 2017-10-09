<?php

class OauthController extends PluginController
{
    public function request_access_token_action()
    {
        URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        //Muss den Nutzer weiterleiten auf den Server, wo der Nutzer die App freischaltet
        $powerfolder = Config::get()->POWERFOLDER_ENDPOINT ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT;
        if ($powerfolder[strlen($powerfolder) - 1] !== "/") {
            $powerfolder .= "/";
        }
        URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $client_id = Config::get()->POWERFOLDER_CLIENT_ID ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_CLIENT_ID;
        $redirect_uri = PluginEngine::getURL($this->plugin, array(), "oauth/receive_access_token", true);

        $url = $powerfolder."index.php/apps/oauth2/authorize";

        $_SESSION['oauth2state'] = md5(uniqid());
        $url .= "?state=".urlencode($_SESSION['oauth2state'])
                . "&response_type=code"
                . "&approval_prompt=auto"
                . "&redirect_uri=".urlencode($redirect_uri)
                . "&client_id=".urlencode($client_id);

        header("Location: ".$url);
        $this->render_nothing();
    }

    public function receive_access_token_action()
    {
        //Save the access token and refresh-token
        $powerfolder = Config::get()->POWERFOLDER_ENDPOINT ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT;
        if ($powerfolder[strlen($powerfolder) - 1] !== "/") {
            $powerfolder .= "/";
        }

        if (Request::get("state") !== $_SESSION['oauth2state']) {
            throw new AccessDeniedException();
        }


        $client_id  = \Config::get()->POWERFOLDER_CLIENT_ID ?: \UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_CLIENT_ID; // The client ID assigned to you by the provider
        $client_secret = \Config::get()->POWERFOLDER_CLIENT_SECRET ?: \UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_CLIENT_SECRET; // The client password assigned to you by the provider
        URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $redirect_uri = PluginEngine::getURL($this->plugin, array(), "oauth/receive_access_token", true);

        $payload = array(
            'grant_type' => "authorization_code",
            'code' => Request::get("code"),
            'redirect_uri' => $redirect_uri,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'format' => "json"
        );

        $header = array();
        $header[] = "Accept: application/json";
        $header[] = "Authorization: Basic ".base64_encode($client_id . ":" .$client_secret);

        $r = curl_init();
        curl_setopt($r, CURLOPT_URL, $powerfolder."index.php/apps/oauth2/api/v1/token?grant_type=authorization_code&code=".urlencode(Request::get("code"))."&redirect_uri=".urlencode($redirect_uri)); //nextcloud
        curl_setopt($r, CURLOPT_POST, 1);
        curl_setopt($r, CURLOPT_HTTPHEADER, $header);
        curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($r, CURLOPT_POSTFIELDS, $payload);

        $json = curl_exec($r);
        curl_close($r);

        $json = json_decode($json, true);

        if ($json['error']) {
            PageLayout::postError(_("Authentifizierungsfehler:")." ".$json['error']);
            $this->redirect(URLHelper::getURL("dispatch.php/files/index"));
        } else {
            if (false) {
                var_dump($json);
                $this->render_nothing();
                return;
            }
            $config = \UserConfig::get($GLOBALS['user']->id);
            $config->store("POWERFOLDER_ACCESS_TOKEN", $json['access_token']);
            $config->store("POWERFOLDER_REFRESH_TOKEN", $json['refresh_token']);
            $config->store("POWERFOLDER_ACCESS_TOKEN_EXPIRES", time() + $json['expires_in']);
            $this->redirect(URLHelper::getURL("dispatch.php/files/system/".$this->plugin->getPluginId()));
        }


    }
}

