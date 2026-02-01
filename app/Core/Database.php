<?php

namespace App\Core;

use PDO;

class Database
{
    public static function connect()
    {
        return new PDO(
            "mysql:host=localhost;dbname=fitness_club_db",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}
