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

    public static function hasAuthToken(): bool {
        return isset($_SESSION[self::USER_TOKEN_KEY]);
    }

    public static function getAuthToken(): string {
        if ( !self::hasAuthToken()) {
            throw new \RuntimeException();
        }
        if ( !is_string($_SESSION[self::USER_TOKEN_KEY])) {
            throw new LogicException();
        }
        return $_SESSION[self::USER_TOKEN_KEY];
    }

    public static function setAuthToken(string $token): void {
        $_SESSION[self::USER_TOKEN_KEY] = $token;
    }

    public static function delAuthToken(): void {
        unset($_SESSION[self::USER_TOKEN_KEY]);
    }

}
