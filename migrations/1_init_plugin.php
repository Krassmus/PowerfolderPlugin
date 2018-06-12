<?php

class InitPlugin extends Migration {
    public function up() {
        $configs = array("POWERFOLDER_ENDPOINT", "POWERFOLDER_CLIENT_ID", "POWERFOLDER_CLIENT_SECRET", "POWERFOLDER_ACTIVATED");
        foreach ($configs as $config) {
            Config::get()->create($config, array(
                'value' => "",
                'type' => $config === "POWERFOLDER_ACTIVATED" ? "boolean" : "string",
                'range' => "global",
                'section' => "Powerfolder"
            ));
        }
        $configs = array("POWERFOLDER_ACCESS_TOKEN", "POWERFOLDER_ACCESS_TOKEN_EXPIRES", "POWERFOLDER_REFRESH_TOKEN");
        foreach ($configs as $config) {
            Config::get()->create($config, array(
                'value' => "",
                'type' => $config === "POWERFOLDER_ACTIVATED" ? "boolean" : "string",
                'range' => "user",
                'section' => "Powerfolder"
            ));
        }
    }

    public function down() {
        $configs = array("POWERFOLDER_ENDPOINT", "POWERFOLDER_CLIENT_ID", "POWERFOLDER_CLIENT_SECRET", "POWERFOLDER_ACTIVATED", "POWERFOLDER_ACCESS_TOKEN", "POWERFOLDER_ACCESS_TOKEN_EXPIRES", "POWERFOLDER_REFRESH_TOKEN");
        foreach ($configs as $config) {
            Config::get()->delete($config);
        }
    }
}