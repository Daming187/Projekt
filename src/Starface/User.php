<?php

namespace Dleschner\Slim\Starface;

use Dleschner\Slim\Parsers;
/** 
 * Eine Klasse mit dem Namen User
*/
class User {

    private function __construct(
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $familyName,
        public readonly int    $id,
        public readonly string $language,
        public readonly string $login,
        public readonly string $personId,
    ) { }

    /** @return mixed */
    public function toMixed() {
        return [
            'email' => $this->email,
            'firstName' => $this->firstName,
            'familyName' => $this->familyName,
            'id' => $this->id,
            'language' => $this->language,
            'login' => $this->login,
            'personId' => $this->personId,
        ];
    }

    /** @param mixed $value */
    public static function parse($value): self {
        $args = [];

        $value = Parsers::parseArray($value);

        $args['email'] = Parsers::parseStringField('email', $value);
        $args['firstName'] = Parsers::parseStringField('firstName', $value);
        $args['familyName'] = Parsers::parseStringField('familyName', $value);
        $args['id'] = Parsers::parseIntField('id', $value);
        $args['language'] = Parsers::parseStringField('language', $value);
        $args['login'] = Parsers::parseStringField('login', $value);
        $args['personId'] = Parsers::parseStringField('personId', $value);
        
        return new self(...$args);
    }



}