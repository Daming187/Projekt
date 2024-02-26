<?php

namespace Dleschner\Slim\Client;

use Dleschner\Slim\Parsers;
use Dleschner\Slim\Parsers\FieldNotFound;
use RuntimeException;

/**
 * Enthält Daten die zum Login vom Browser gesendet werden.
 */
class GroupEdit {

    /** @param array<int, bool> $users */
    private function __construct(
        public readonly array $users,
    ) { }

    /** @param mixed $value */
    public static function parse($value): self {
        $value = Parsers::parseArray($value);

        if ( !array_key_exists('assigned', $value)) throw new FieldNotFound('assigned');
        $assigned = Parsers::parseArray($value['assigned']);

        $users = [];
        array_walk($assigned, function(mixed $elem, int|string $key) use (&$users) {
            if ( !is_numeric($key)) throw new RuntimeException('int expected');
            if ( (int)$key != $key) throw new RuntimeException('int expected');

            $users[(int)$key] = $elem === '1';
        });
        
        return new self($users);
    }
}
