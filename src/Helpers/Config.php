<?php


namespace App\Helpers;

use App\Exceptions\ConfigFileNotFoundException;

class Config
{
    public static function getFileContents(string $filename){

        $FilePath = realpath(__DIR__."/../configs/".$filename.".php");

        if(!$FilePath){
            throw new ConfigFileNotFoundException();
        }

        $FileContents = require $FilePath;
        return $FileContents;
    }

    public static function get(string $filename, string $key=null){
        $fileContents = self::getFileContents($filename);

        if(is_null($key)) return $fileContents;

        return $fileContents[$key] ?? null;
    }
}