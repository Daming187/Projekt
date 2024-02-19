<?php

namespace Dleschner\Slim\Client;

use Dleschner\Slim\Parsers;

/**
 * Enthält Daten die zum Login vom Browser gesendet werden.
 */
class Login {

    private function __construct(
        public readonly string $loginId,
        public readonly string $password,
    ) { }

    /** @param mixed $value */
    public static function parse($value): self {
        $args = [];

        $value = Parsers::parseArray($value);

        $args['loginId'] = Parsers::parseStringField('loginId', $value);
        $args['password'] = Parsers::parseStringField('password', $value);
        
        return new self(...$args);
    }
}
