<?php

namespace Dleschner\Slim;

use LogicException;

/**
 * Eine finale Klasse erzeugen
 */
final class Session {

    const USER_TOKEN_KEY = 'userToken';
    const ADMIN_TOKEN_KEY = 'adminToken';

    /** 
     * Leerer Konstruktor
     */
    private function __construct() {}

    public static function hasUserToken(): bool {
        return isset($_SESSION[self::USER_TOKEN_KEY]);
    }

    public static function getUserToken(): string {
        if ( !self::hasUserToken()) {
            throw new \RuntimeException();
        }
        if ( !is_string($_SESSION[self::USER_TOKEN_KEY])) {
            throw new LogicException();
        }
        return $_SESSION[self::USER_TOKEN_KEY];
    }

    public static function setUserToken(string $token): void {
        $_SESSION[self::USER_TOKEN_KEY] = $token;
    }

    public static function delUserToken(): void {
        unset($_SESSION[self::USER_TOKEN_KEY]);
    }

}
