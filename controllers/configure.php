<?php

class ConfigureController extends PluginController
{
    public function myarea_action()
    {
        if (Navigation::hasItem("/files_dashboard/files")) {
            if (Navigation::hasItem('/files_dashboard/files/PowerfolderPlugin')) {
                Navigation::activateItem('/files_dashboard/files/PowerfolderPlugin');
            } else {
                Navigation::activateItem('/files_dashboard/files');
            }
        } elseif (Navigation::hasItem("/profile/files")) {
            if (Navigation::hasItem("/profile/files/PowerfolderPlugin")) {
                Navigation::activateItem('/profile/files/PowerfolderPlugin');
            } else {
                Navigation::activateItem('/profile/files');
            }
        }
        PageLayout::setTitle(_("Powerfolder"));
        if (Request::isPost()) {
            $config = UserConfig::get($GLOBALS['user']->id);
            $data = Request::getArray("powerfolder");
            foreach ($data as $key => $value) {
                $config->store("POWERFOLDER_".strtoupper($key), $value);
                $this->redirect(URLHelper::getURL("dispatch.php/files"));
            }
            if (!$data['activated']) {
                $config->store("POWERFOLDER_ACTIVATED", 0);
            } else {
                if (\Powerfolder\OAuth::hasAccessToken()) {
                    $this->redirect(URLHelper::getURL("dispatch.php/files/system/" . $this->plugin->getPluginId()));
                } else {
                    $this->redirect("oauth/request_access_token");
                }
            }
            PageLayout::postMessage(MessageBox::success(_("Konfiguration gespeichert.")));
            return;
        }
    }
}