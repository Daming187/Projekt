<?php

namespace Dleschner\Slim\Starface;

use Dleschner\Slim\Parsers;

class GroupListItem {

    private function __construct(
        public readonly ?string $groupexternalnumber,
        public readonly ?string $groupinternalnumber,
        public readonly ?string $groupname,
        public readonly ?int $id,
    ){ }

    /**
     * Diese Funktionen gibt dem Rückgabewert die folgende Struktur
     */
    public function toMixed(): array {
        return [
            'groupexternalnumber' => $this->groupexternalnumber,
            'groupinternalnumber' => $this->groupinternalnumber,
            'groupname' => $this->groupname,
            'id' => $this->id,
        ];
    }

    /** @param mixed $value */
    public static function parse($value): self {
        $args = [];

        $value = Parsers::parseArray($value);

        $args['groupexternalnumber'] = Parsers::parseOptional(
        function(array $value) {
            return Parsers::parseStringField('groupexternalnumber', $value);
        },
        $value
        );

        $args['groupinternalnumber'] = Parsers::parseOptional(
            function(array $value) {
                return Parsers::parseStringField('groupinternalnumber', $value);
        },
        $value
        );

        $args['groupname'] = Parsers::parseOptional(
        function(array $value) {
            return Parsers::parseStringField('groupname', $value);
        },
        $value
        );

        $args['id'] = Parsers::parseOptional(
            function(array $value) {
                return Parsers::parseIntField('id', $value);
        },
        $value
        );

        return new self(...$args);

    }



}