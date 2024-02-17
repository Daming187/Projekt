<?php

namespace Dleschner\Slim\Starface;

use Dleschner\Slim\Parsers;

class AssignableUsers {

    /**  
     * Ein private Konstruktor der Werte entgegen nimmt
     * die sind Schreibgeschützt und können nach der Instanziierung nicht geändert werden
     */
    private function __construct(
        public readonly bool   $assigned,
        public readonly string $firstname,
        public readonly int    $id,
        public readonly string $lastname,
    ) { }

    public function setAssigned(bool $assigned): self {
        if ($assigned == $this->assigned) return $this;
        return new self(
            $assigned,
            $this->firstname,
            $this->id,
            $this->lastname
        );
    }

    /**
     * Diese Funktionen gibt dem Rückgabewert die folgende Struktur
     * 
     * @return mixed
     */
    public function toMixed() {
        return [
            'assigned' => $this->assigned,
            'firstname' => $this->firstname,
            'id' => $this->id,
            'lastname' => $this->lastname,
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

        $args['assigned'] = Parsers::parseBoolField('assigned', $value);
        $args['firstname'] = Parsers::parseStringField('firstname', $value);
        $args['id'] = Parsers::parseIntField('id', $value);
        $args['lastname'] = Parsers::parseStringField('lastname', $value);

        return new self(...$args);
    }
}