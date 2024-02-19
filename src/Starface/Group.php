<?php

namespace Dleschner\Slim\Starface;

use RuntimeException;

use Dleschner\Slim\Parsers;

class Group {

    /**
     * Ein private Konstruktor der Werte entgegen nimmt
     * die sind Schreibgeschützt und können nach der Instanziierung nicht geändert werden
     * 
     * @param list<AssignableNumber> $assignableNumbers
     * @param list<AssignableUsers> $assignableUsers
     */
    private function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $groupId,
        public readonly bool   $chatGroup,
        public readonly bool   $voicemail,
        public readonly array  $assignableNumbers,
        public readonly array  $assignableUsers,
    ) { }

    public function setAssigned(int $assignableUserId, bool $assigned): self {
        return new self(
            $this->id, $this->name, $this->groupId, $this->chatGroup,
            $this->voicemail, $this->assignableNumbers,
            array_map(function(AssignableUsers $user) use ($assignableUserId, $assigned): AssignableUsers {
                if ($user->id == $assignableUserId) {
                    return $user->setAssigned($assigned);
                }
                return $user;
            }, $this->assignableUsers)
        );

        /*
        $newAssignableUsers = [];
        foreach ($this->assignableUsers as $user) {
            if ($user->id == $assignableUserId) {
                $newAssignableUsers[] = $user->setAssigned($assigned);
            } else {
                $newAssignableUsers[] = $user;
            }
        }
        return new self(
            $this->id, $this->name, $this->groupId, $this->chatGroup,
            $this->voicemail, $this->assignableNumbers,
            $newAssignableUsers
        );
        */
    }

    /**
     * Diese Funktionen gibt dem Rückgabewert die folgende Struktur
     * 
     * @return mixed
     */
    public function toMixed() {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'groupId'   => $this->groupId,
            'chatGroup' => $this->chatGroup,
            'voicemail' => $this->voicemail,
            'assignableNumbers' => array_map(
                /** @return mixed */
                function (AssignableNumber $assignableNumber) {
                    return $assignableNumber->toMixed();
                },
                $this->assignableNumbers
            ),
            'assignableUsers' => array_map(
                /** @return mixed */
                function (AssignableUsers $assignableUser) {
                    return $assignableUser->toMixed();
                },
                $this->assignableUsers
            ),
        ];
    }

    /** @param mixed $value */
    public static function parse($value): self {
        $args = [];

        $value = Parsers::parseArray($value);

        $args['id'] = Parsers::parseIntField('id', $value);

        $args['name'] = Parsers::parseStringField('name', $value, );
        $args['groupId'] = Parsers::parseStringField('groupId', $value);

        $args['chatGroup'] = Parsers::parseBoolField('chatGroup', $value);
        $args['voicemail'] = Parsers::parseBoolField('voicemail', $value);

        if ( !array_key_exists('assignableNumbers', $value)) throw new RuntimeException();
        $args['assignableNumbers'] = Parsers::parseListOf(AssignableNumber::parse(...), $value['assignableNumbers']);

        if ( !array_key_exists('assignableUsers', $value)) throw new RuntimeException();
        $args['assignableUsers'] = Parsers::parseListOf(AssignableUsers::parse(...), $value['assignableUsers']);
        
        return new self(...$args);
    }
}