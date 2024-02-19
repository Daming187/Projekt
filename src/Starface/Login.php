<?php

namespace Dleschner\Slim\Starface;
use Dleschner\Slim\Parsers;

/**
 * Enthält Daten die zum Login von und zur Starface gesendet werden.
 */
class Login {

    private function __construct(
        public readonly string $nonce,
        public readonly ?string $secret,
    ) { }

    public function updateSecret(string $loginId, string $password): Login {
        $secret = $loginId.':'.hash('sha512',$loginId.$this->nonce.hash('sha512', $password));
        return new Login($this->nonce, $secret);
    }

        /**
     * Diese Funktionen gibt dem Rückgabewert die folgende Struktur
     * 
     * @return mixed
     */
    public function toMixed() {
        return [
            'loginType' => 'Internal',
            'nonce' => $this->nonce,
            'secret' => $this->secret,
        ];
    }

    /** @param mixed $value */
    public static function parse($value): self {
        $args = [];

        $value = Parsers::parseArray($value);

        $args['nonce'] = Parsers::parseStringField('nonce', $value);
        $args['secret'] = Parsers::parseOptional(
            function(array $value) {
                return Parsers::parseStringField('secret', $value);
            },
            $value
        );
        
        return new self(...$args);
    }
}