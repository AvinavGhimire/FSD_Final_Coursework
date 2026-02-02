<?php

namespace App\Core;

use PDO;

class Database
{
    public static function connect()
    {
        return new PDO(
            "mysql:host=localhost;dbname=np02cs4a240013",
            "np02cs4a240013",
            "KKjh9O5r2t",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}
