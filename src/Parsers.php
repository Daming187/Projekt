<?php

namespace Dleschner\Slim;

use RuntimeException;

use Dleschner\Slim\Parsers\IsNull;
use Dleschner\Slim\Parsers\FieldNotFound;

/**
 * Eine finale Klasse erzeugen
 */
final class Parsers {

    /** 
     * Leerer Konstruktor
     */
    private function __construct() {}

    /**
     * Diese Funktion prüft ob der Wert in Value ein Array ist
     */ 
    public static function parseArray(mixed $value): array {
        if ( !is_array($value)) throw new RuntimeException();
        return $value;
    }

    /**
     * Diese Funktion prüft ob dieser Array ein Key hat
     * und ob  der Wert an dieser Stelle ein Integer ist     
     */   
    public static function parseIntField(string $key, array $array): int {
        if ( !array_key_exists($key, $array)) throw new FieldNotFound($key);
        if (is_null($array[$key])) throw new IsNull();
        if ( !is_int($array[$key])) throw new RuntimeException();
        return $array[$key];
    }

    /**
     * Diese Funktion prüft ob dieser Array ein Key hat
     * und ob  der Wert an dieser Stelle ein Boolean ist   
     */  
    public static function parseBoolField(string $key, array $array): bool {
        if ( !array_key_exists($key, $array)) throw new FieldNotFound($key);
        if (is_null($array[$key])) throw new IsNull();
        if ( !is_bool($array[$key])) throw new RuntimeException();
        return $array[$key];
    }
    
    /**
     * Diese Funktion prüft ob dieser Array ein Key hat
     * und ob  der Wert an dieser Stelle ein String ist   
     */
    public static function parseStringField(string $key, array $array): string {
        if ( !array_key_exists($key, $array)) throw new FieldNotFound($key);
        if (is_null($array[$key])) throw new IsNull();
        if ( !is_string($array[$key])) throw new RuntimeException();
        return $array[$key];
    }

    public static function parseOptionalStringField(string $key, array $array): ?string {
        return self::parseOptional(
            function(array $value) use ($key) {
                return Parsers::parseStringField($key, $value);
            },
            $array
        );

        //return self::getOptionalStringFieldParser($key)($array);
        //return self::getOptionalParser(self::getFieldParser($key, self::getStringParser()))($value);
    }
    
    /**
     * Diese Funktion prüft ob der Wert ein Array ist und ob dieser eine Liste ist.
     * Dann wirft es den Wert in die Funktion array_map rein, 
     * welche an jedem der elemente einen Parser anwendet.
     *
     * @template T
     * 
     * @param callable(mixed):T $parser
     * 
     * @return list<T>
     */
    public static function parseListOf(callable $parser, mixed $value): array {
        if ( !is_array($value)) throw new RuntimeException();
        if ( !array_is_list($value)) throw new RuntimeException();
        
        return array_map($parser, $value);
    }

    /**
     * Diese Funktion prüft führt den gegebenen Parser für den gegebenen Wert aus und
     * gibt dessen Rückgabewert zurück. Wird eine FieldNotFound oder IsNull Exception
     * geworfen, so wird null zurück gegeben.
     *
     * @template T
     * 
     * @param callable(mixed):T $parser
     * 
     * @return ?T
     */
    public static function parseOptional(callable $parser, mixed $value) {
        try {
            return $parser($value);
        } catch (FieldNotFound|IsNull $_oje) {}

        return null;
    }

    public static function parseTokenField(mixed $value): string {
        $value = self::parseArray($value);
        return self::parseStringField('token', $value);
    }

    public static function parseUrlField(mixed $value): string {
        $value = self::parseArray($value);
        return self::parseStringField('url', $value);
    }

    public static function parseIdField(mixed $value): int {
        $value = self::parseArray($value);
        return (int)self::parseStringField('id', $value);
    }

}