<?php

class InitPlugin extends Migration {
    public function up()
    {
        $configs =     array("POWERFOLDER_ENDPOINT", "POWERFOLDER_CLIENT_ID", "POWERFOLDER_CLIENT_SECRET");
        $userconfigs = array("POWERFOLDER_ENDPOINT_USER", "POWERFOLDER_CLIENT_ID_USER", "POWERFOLDER_CLIENT_SECRET_USER", "POWERFOLDER_ACTIVATED", "POWERFOLDER_ACCESS_TOKEN", "POWERFOLDER_ACCESS_TOKEN_EXPIRES", "POWERFOLDER_REFRESH_TOKEN");
        foreach ($configs as $config) {
            Config::get()->create($config, array(
                'value' => "",
                'type' => "string",
                'range' => "global",
                'section' => "Powerfolder"
            ));
        }
        foreach ($userconfigs as $config) {
            Config::get()->create($config, array(
                'value' => "",
                'type' => in_array($config, ["POWERFOLDER_ACTIVATED"]) ? "boolean" : "string",
                'range' => "user",
                'section' => "Powerfolder"
            ));
        }
    }

    public function down()
    {
        $configs =     array("POWERFOLDER_ENDPOINT", "POWERFOLDER_CLIENT_ID", "POWERFOLDER_CLIENT_SECRET");
        $userconfigs = array("POWERFOLDER_ENDPOINT_USER", "POWERFOLDER_CLIENT_ID_USER", "POWERFOLDER_CLIENT_SECRET_USER", "POWERFOLDER_ACTIVATED", "POWERFOLDER_ACCESS_TOKEN", "POWERFOLDER_ACCESS_TOKEN_EXPIRES", "POWERFOLDER_REFRESH_TOKEN");
        foreach ($configs as $config) {
            Config::get()->delete($config);
        }
        foreach ($userconfigs as $config) {
            Config::get()->delete($config);
        }
    }
}