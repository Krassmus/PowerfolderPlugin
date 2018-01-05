<?php

class InitPlugin extends Migration {
    public function up() {
        $configs = array("POWERFOLDER_ENDPOINT", "POWERFOLDER_CLIENT_ID", "POWERFOLDER_CLIENT_SECRET", "POWERFOLDER_ACTIVATED", "POWERFOLDER_ACCESS_TOKEN", "POWERFOLDER_ACCESS_TOKEN_EXPIRES", "POWERFOLDER_REFRESH_TOKEN");
        foreach ($configs as $config) {
            Config::get()->create($config, array(
                'value' => "",
                'type' => $config === "OWNCLOUD_ACTIVATED" ? "boolean" : "string",
                'range' => "user",
                'section' => "Owncloud"
            ));
        }
    }
}