<?php

class InitPlugin extends Migration {
    public function up() {
        $configs = array("POWERFOLDER_ENDPOINT", "POWERFOLDER_CLIENT_ID", "POWERFOLDER_CLIENT_SECRET");
        $statement = DBManager::get()->prepare(
            "INSERT IGNORE INTO `config` (
                `config_id` ,
                `parent_id` ,
                `field` ,
                `value` ,
                `is_default` ,
                `type` ,
                `range` ,
                `section` ,
                `position` ,
                `mkdate` ,
                `chdate` ,
                `description` ,
                `comment` ,
                `message_template`
            )
            VALUES (
                MD5(:field), 
                '', 
                :field, 
                '', 
                '0', 
                'string', 
                'global', 
                'Powerfolder', 
                '0', 
                UNIX_TIMESTAMP(), 
                UNIX_TIMESTAMP(), 
                '', 
                '', 
                ''
            );
        ");
        foreach ($configs as $config) {
            $statement->execute(array('field' => $config));
        }
    }
}