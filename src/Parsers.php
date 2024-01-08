<?php

namespace Dleschner\Slim;

use RuntimeException;

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
     *  @param mixed $value 
     */ 
    public static function parseArray($value): array {
        if ( !is_array($value)) throw new RuntimeException();
        return $value;
    }

    /**
     * Diese Funktion prüft ob dieser Array ein Key hat
     * und ob  der Wert an dieser Stelle ein Integer ist     
     */   
    public static function parseIntField(string $key, array $array): int {
        if ( !array_key_exists($key, $array)) throw new RuntimeException();
        if ( !is_int($array[$key])) throw new RuntimeException();
        return $array[$key];
    }

    /**
     * Diese Funktion prüft ob dieser Array ein Key hat
     * und ob  der Wert an dieser Stelle ein Boolean ist   
     */  
    public static function parseBoolField(string $key, array $array): bool {
        if ( !array_key_exists($key, $array)) throw new RuntimeException();
        if ( !is_bool($array[$key])) throw new RuntimeException();
        return $array[$key];
    }
    
    /**
     * Diese Funktion prüft ob dieser Array ein Key hat
     * und ob  der Wert an dieser Stelle ein String ist   
     */
    public static function parseStringField(string $key, array $array): string {
        if ( !array_key_exists($key, $array)) throw new RuntimeException();
        if ( !is_string($array[$key])) throw new RuntimeException();
        return $array[$key];
    }

    /**
     * Diese Funktion prüft ob der Wert ein Array ist und ob dieser eine Liste ist.
     * Dann wirft es den Wert in die Funktion array_map rein, 
     * welche an jedem der elemente einen Parser anwendet.
     *
     * @template T
     * 
     * @param callable(mixed):T $parser* 
     * @param mixed             $value        
     * 
     * @return list<T>
     */
    public static function parseListOf(callable $parser, $value): array {
        if ( !is_array($value)) throw new RuntimeException();
        if ( !array_is_list($value)) throw new RuntimeException();
        
        return array_map($parser, $value);
    }
}