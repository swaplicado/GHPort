<?php namespace App\Utils;

use Carbon\Carbon;

class Configuration {

    public static function getConfigurations()
    {
        // Read File
        $jsonString = file_get_contents(base_path('gh_config.json'));
        $data = json_decode($jsonString);

        return $data;
    }

    public static function setConfiguration($key, $value)
    {
        // Read File
        $jsonString = file_get_contents(base_path('gh_config.json'));
        $data = json_decode($jsonString, true);

        // Update Key
        $data[$key] = $value;

        // Write File
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(base_path('gh_config.json'), stripslashes($newJsonString));
    }
}