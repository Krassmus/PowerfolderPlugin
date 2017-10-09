<?php

class ConfigureController extends PluginController
{
    public function myarea_action()
    {
        if (Navigation::hasItem("/profile/files/PowerfolderPlugin")) {
            Navigation::activateItem('/profile/files/PowerfolderPlugin');
        } else {
            Navigation::activateItem('/profile/files');
        }
        PageLayout::setTitle(_("Powerfolder"));
        if (Request::isPost()) {
            $config = UserConfig::get($GLOBALS['user']->id);
            $data = Request::getArray("powerfolder");
            foreach ($data as $key => $value) {
                $config->store("POWERFOLDER_".strtoupper($key), $value);
            }
            if (!$data['activated']) {
                $config->store("POWERFOLDER_ACTIVATED", 0);
            }
            PageLayout::postMessage(MessageBox::success(_("Konfiguration gespeichert.")));
        }
    }
}