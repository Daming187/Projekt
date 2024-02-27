<?php

namespace Dleschner\Slim;
use Dleschner\Slim\Starface\User;
use Dleschner\Slim\Starface\Groups;
use Dleschner\Slim\Starface\Group;
use Dleschner\Slim\Starface\Login;

/**
 * Finaleklasse erzeugen
 */
final class Starface {

    private const LOGIN_URL   = '/rest/login';
    private const USERSME_URL = '/rest/users/me';
    private const GROUPS_URL  = '/rest/groups';
    private const GROUP_URL   = '/rest/groups';
    private const UPGROUP_URL = '/rest/groups';

    /** Einen construktor erzeugen */
    public function __construct(
        public readonly string $server,
    ) { }

    /** Die URL's in konstante Variabeln packen */
    private const SERVER = 'https://pbx.screwerk.com';
    

    /** 
     * Eine GET Anfrage um den Wert $nonce zu bekommmen.
     * Dieser wird gebraucht um mit den Login Daten das $secret zu formen,
     * welches für das Einloggen gebraucht wird.
     * Der Wert kommt als JSON zurück. 
     * Mit Hilfe von unserer Parser-Klasse wandeln wir diesen Wert in einen Array um. 
    */
    private function getNonce(): Login {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $this->server.self::LOGIN_URL);
        return Login::parse(json_decode($response->getBody()->getContents(), true));
    }

    /**
     * Nach dem der $secret Wert berechnet wurde,
     * wird dieser mit einer POST Anfrage zurück an den STARFACE Server geschickt.
     */
    private function postSecret(Login $secret): string {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $this->server.self::LOGIN_URL, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($secret->toMixed()),
        ]);
        
        $json = $response->getBody()->getContents();
        return Parsers::parseTokenField(json_decode($json, true));
    }

    /**
     * Wenn der $secret Wert erfolgreich berechnet und zurück geschickt wurde,
     * bekommt man einen $authToken. Diesen braucht man um weitere Daten abrufen zu können.
     */
    public function getAuthToken(string $loginId, string $password): ?string {
        $nonce = $this->getNonce();
        return $this->postSecret($nonce->updateSecret($loginId, $password));
    }

    public function getUsersMe(string $authToken): User {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $this->server.self::USERSME_URL, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' => $authToken,
            ]
        ]);
        return User::parse(json_decode($response->getBody()->getContents(), true));
    }

    public function getGroups(string $authToken): Groups {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $this->server.self::GROUPS_URL, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' => $authToken,
            ]
        ]);
        return Groups::parse(json_decode($response->getBody()->getContents(), true));
    }

    public function getGroup(string $authToken, int $id): Group {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $this->server.self::GROUP_URL.'/'.$id, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' => $authToken,
            ]
        ]);
        return Group::parse(json_decode($response->getBody()->getContents(), true));
    }

    public function putGroup(string $authToken, Group $group): void {
        $client = new \GuzzleHttp\Client();
        $client->request('PUT', $this->server.self::UPGROUP_URL, [
            'headers' => [
                'X-Version' => '2',
                'Content-Type' => 'application/json',
                'authToken' =>  $authToken,
            ],
            'json' => $group->toMixed(),
        ]);
    }
}


