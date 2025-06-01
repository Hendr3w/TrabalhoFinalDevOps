<?php

namespace App\Config;

use PDO;

class Database {
    public function connect(): PDO {
        return new PDO(
            'mysql:host=localhost;port=3306;dbname=auth_api',
            'auth_user',
            'auth_pass'
        );
    }
}
