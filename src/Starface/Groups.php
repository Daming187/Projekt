<?php

namespace Dleschner\Slim\Starface;

use Dleschner\Slim\Parsers;
/** 
 * Eine Klasse mit dem Namen Groups
*/
class Groups {

    /**
     * Ein privater Konstruktor erzeugt einen öffentlichen Array
     * $items, dieser ist schreibgeschützt
     *  @param list<GroupListItem> $items  
     */
    private function __construct(
        public readonly array $items,
    ){ }

    /**
     * Diese Funktionen gibt dem Rückgabewert die folgende Struktur
     */
    public function toMixed(): array {
        return array_map(function($item): array {
            return $item->toMixed();
        }, $this->items);
    }

    /** @param mixed $value */
    public static function parse($value): self {
        $args = [];

        $args['items'] = Parsers::parseListOf(GroupListItem::parse(...), $value);

        return new self(...$args);
    }
}