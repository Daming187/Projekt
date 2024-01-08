<?php

namespace Dleschner\Slim\Starface;

class Login {

    private readonly string $nonce;
    private readonly ?string $secret;

    private function __construct(string $nonce, ?string $secret) {
        $this->nonce = $nonce;
        $this->secret = $secret;
    }

    public function updateSecret(string $loginId, string $password): Login {
        $secret = $loginId.':'.hash('sha512',$loginId.$this->nonce.hash('sha512', $password));
        return new Login($this->nonce, $secret);
    }

    public function toJson(): string {
        return json_encode([
            'loginType' => 'Internal',
            'nonce' => $this->nonce,
            'secret' => $this->secret,
        ]);
    }

    public static function fromJson(string $json): ?Login {
        $json = json_decode($json, true);
        if ( !is_array($json)) return null;

        if ( !isset($json['nonce'])) return null;
        if ( !is_string($json['nonce'])) return null;

        if (isset($json['secret']) && !is_string($json['secret'])) {
            return null;
        }

        return new Login($json['nonce'], $json['secret']);
    }
}