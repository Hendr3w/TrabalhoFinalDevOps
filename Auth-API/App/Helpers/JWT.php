<?php
namespace App\Helpers;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JWT {
    private static $key = 'test_key';

    public static function encode(array $data): string {
        return FirebaseJWT::encode($data, self::$key, 'HS256');
    }

    public static function decode(string $token): ?array {
        try {
            $decoded = FirebaseJWT::decode($token, new Key(self::$key, 'HS256'));
            return (array) $decoded;
        } catch (ExpiredException | SignatureInvalidException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
