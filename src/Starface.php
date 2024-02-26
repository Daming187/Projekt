<?php

namespace Dleschner\Slim;

use Dleschner\Slim\Starface\Groups;
use Dleschner\Slim\Starface\Group;
use Dleschner\Slim\Starface\Login;

/**
 * Finaleklasse erzeugen
 */
final class Starface {
    /** Einen construktor erzeugen */
    private function __construct() { }

    /** Die URL's in konstante Variabeln packen */
    private const SERVER = 'https://pbx.screwerk.com';
    private const LOGIN_URL = self::SERVER . '/rest/login';
    private const USERSME_URL = self::SERVER . '/rest/users/me';
    private const GROUPS_URL = self::SERVER . '/rest/groups';
    private const GROUP_URL = self::SERVER . '/rest/groups';
    private const UPGROUP_URL = self::SERVER . '/rest/groups';

    /** 
     * Eine GET Anfrage um den Wert $nonce zu bekommmen.
     * Dieser wird gebraucht um mit den Login Daten das $secret zu formen,
     * welches für das Einloggen gebraucht wird.
     * Der Wert kommt als JSON zurück. 
     * Mit Hilfe von unserer Parser-Klasse wandeln wir diesen Wert in einen Array um. 
    */
    private static function getNonce(): Login {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', self::LOGIN_URL);
        return Login::parse(json_decode($response->getBody()->getContents(), true));
    }

    /**
     * Nach dem der $secret Wert berechnet wurde,
     * wird dieser mit einer POST Anfrage zurück an den STARFACE Server geschickt.
     */
    private static function postSecret(Login $secret): ?string {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('POST', self::LOGIN_URL, [
                'headers' => [
                    'X-Version' => '2',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($secret->toMixed()),
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

    /**
     * Wenn der $secret Wert erfolgreich berechnet und zurück geschickt wurde,
     * bekommt man einen $authToken. Diesen braucht man um weitere Daten abrufen zu können.
     */
    public static function getAuthToken(string $loginId, string $password): ?string {
        $nonce = self::getNonce();
        return self::postSecret($nonce->updateSecret($loginId, $password));
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

    public static function getGroups(string $authToken): Groups {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', self::GROUPS_URL, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' => $authToken,
            ]
        ]);
        return Groups::parse(json_decode($response->getBody()->getContents(), true));
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

    public static function putGroup(string $authToken, Group $group): void {
        $client = new \GuzzleHttp\Client();
        $client->request('PUT', self::UPGROUP_URL, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' =>  $authToken,
            ],
            'json' => $group->toMixed(),
        ]);
    }
}


