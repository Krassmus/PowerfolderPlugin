<?php

class AddCustomName extends Migration
{
    public function up()
    {
        Config::get()->create("POWERFOLDER_NAME", array(
            'value' => "OwnCloud",
            'type' => "string",
            'range' => "global",
            'section' => "Owncloud"
        ));
    }

    public function down()
    {
        Config::get()->delete("POWERFOLDER_NAME");
    }
}
