<?php

namespace Dleschner\Slim\Starface;

use Dleschner\Slim\Parsers;

class AssignableNumber {

    /**  
     * Ein private Konstruktor der Werte entgegen nimmt
     * die sind Schreibgeschützt und können nach der Instanziierung nicht geändert werden
     */
    private function __construct(
        private readonly int    $id,
        private readonly bool   $intern,
        private readonly bool   $assigned,
        private readonly string $exitCode,
        private readonly string $extension,
        private readonly string $countryCode,
        private readonly string $localAreaCode,
    ) { }

    /**
     * Diese Funktionen gibt dem Rückgabewert die folgende Struktur
     * 
     * @return mixed
     */
    public function toMixed() {
        return [
            'id' => $this->id,

            'intern' => $this->intern,
            'assigned' => $this->assigned,

            'exitCode' => $this->exitCode,
            'extension' => $this->extension,
            'countryCode' => $this->countryCode,
            'localAreaCode' => $this->localAreaCode,
        ];
    }

    /**
     * Diese Funktion nimmt ein Wert entgegen und wandeld dieses in ein Objekt
     * der Klasse Parsers um. Dabei werden die einzelnen Funktionen der Klasse
     * Parsers auf die Werte angewendet
     * 
     * @param mixed $value
     */
    public static function parse($value): self {
        $args = [];

        $value = Parsers::parseArray($value);

        $args['id'] = Parsers::parseIntField('id', $value);

        $args['intern'] = Parsers::parseBoolField('intern', $value);
        $args['assigned'] = Parsers::parseBoolField('assigned', $value);

        $args['exitCode'] = Parsers::parseStringField('exitCode', $value);
        $args['extension'] = Parsers::parseStringField('extension', $value);
        $args['countryCode'] = Parsers::parseStringField('countryCode', $value);
        $args['localAreaCode'] = Parsers::parseStringField('localAreaCode', $value);

        return new self(...$args);
    }
}