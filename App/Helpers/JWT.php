<?php
namespace App\Helpers;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class JWT {
    private static $key = 'secreta123';

    public static function encode($data) {
        return FirebaseJWT::encode($data, self::$key, 'HS256');
    }

    public static function decode($token) {
        return (array) FirebaseJWT::decode($token, new Key(self::$key, 'HS256'));
    }
}
