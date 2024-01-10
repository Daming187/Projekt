<?php

namespace Dleschner\Slim;

use Dleschner\Slim\Starface\Group;
use Dleschner\Slim\Starface\Login;
use Dleschner\Slim\Starface\ContactsTag;

/**
 * Finaleklasse erzeugen
 */
final class Starface {
    private function __construct() { }

    private const SERVER = 'https://pbx.screwerk.com';
    private const LOGIN_URL = self::SERVER . '/rest/login';
    private const USERSME_URL = self::SERVER . '/rest/users/me';
    private const GROUPS_URL = self::SERVER . '/rest/groups';
    private const GROUP_URL = self::SERVER . '/rest/groups';

    private static function getNonce(): ?Login {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', self::LOGIN_URL);
        /** @psalm-suppress ImplicitToStringCast */
        return Login::fromJson($response->getBody());
    }

    private static function postSecret(Login $secret): ?string {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('POST', self::LOGIN_URL, [
                'headers' => [
                    'X-Version' => '2',
                    'Content-Type' => 'application/json',
                ],
                'body' => $secret->toJson(),
            ]);
        } catch (\Exception $e) {
            return null;
        }
        $json = $response->getBody()->getContents();
        /** @psalm-suppress MixedAssignment */
        $json = json_decode($json, true);
        if ( !is_array($json)) return null;

        if ( !isset($json['token'])) return null;
        if ( !is_string($json['token'])) return null;

        return $json['token'];
    }

    public static function getAuthToken(string $loginId, string $password): ?string {
        if ($nonce = self::getNonce()) {
            return self::postSecret($nonce->updateSecret($loginId, $password));
        }
        return null;
    }

    /** @psalm-suppress MixedInferredReturnType */
    public static function getUsersMe(string $authToken): ?array {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', self::USERSME_URL, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' => $authToken,
            ]
        ]);
        /** @psalm-suppress MixedReturnStatement */
        return json_decode($response->getBody()->getContents(), true);
    }

    /** @psalm-suppress MixedInferredReturnType */
    public static function getGroups(string $authToken): ?array {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', self::GROUPS_URL, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' => $authToken,
            ]
        ]);
        /** @psalm-suppress MixedReturnStatement */
        return json_decode($response->getBody()->getContents(), true);
    }

    public static function getGroup(string $authToken, int $id): Group {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', self::GROUP_URL.'/'.$id, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' => $authToken,
            ]
        ]);
        return Group::parse(json_decode($response->getBody()->getContents(), true));
    }
}


