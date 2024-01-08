<?php

namespace Dleschner\Slim\Starface;

use RuntimeException;

use Dleschner\Slim\Parsers;

class ContactsTag {

    /** @param list<Tag> $tag */
    private function __construct(
        public readonly array $tag,
    ) { }

    /**
     * Diese Funktionen gibt dem Rückgabewert die folgende Struktur
     * 
     * @return mixed
     */
    public function toMixed() {
        return [
            'tag' => array_map(
                /** @return mixed */
                function (Tag $tag) {
                    return $tag->toMixed();
                },
                $this->tag
            ),
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

        if ( !array_key_exists('tag', $value)) throw new RuntimeException();
        $args['tag'] = Parsers::parseListOf(Tag::parse(...), $value['tag']);

        return new self(...$args);
    }

}