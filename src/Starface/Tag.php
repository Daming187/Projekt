<?php

namespace Dleschner\Slim\Starface;

use RuntimeException;

use Dleschner\Slim\Parsers;

class Tag {

    private function __construct(
        public readonly string $alias,
        public readonly string $id,
        public readonly string $name,
        public readonly string $owner,
    ) { }

    /** @return mixed */
    public function toMixed() {
        return [
            'alias' => $this->alias,
            'id' => $this->id,
            'name' => $this->name,
            'owner' => $this->owner,
        ];
    }

    /** @param mixed $value */
    public static function parse($value): self {
        $args = [];

        $value = Parsers::parseArray($value);

        $args['alias'] = Parsers::parseStringField('alias', $value);
        $args['id'] = Parsers::parseStringField('id', $value);
        $args['name'] = Parsers::parseStringField('name', $value);
        $args['owner'] = Parsers::parseStringField('owner', $value); 

        return new self(...$args);
    }
}