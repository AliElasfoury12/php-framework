<?php

namespace app\resources;

class UserResource {
    public static function toArray($user) {
        return [
            'id' => $user->id ,
            'name' => $user->name,
            'email' => $user->email
        ];
    }
}