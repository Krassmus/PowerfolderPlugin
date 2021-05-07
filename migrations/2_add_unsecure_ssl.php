<?php

class AddUnsecureSsl extends Migration
{
    public function up()
    {
        Config::get()->create("POWERFOLDER_SSL_VERIFYPEER", array(
            'value' => "1",
            'type' => "boolean",
            'range' => "global",
            'section' => "Owncloud"
        ));
    }

    public function down()
    {
        Config::get()->delete("POWERFOLDER_SSL_VERIFYPEER");
    }
}
