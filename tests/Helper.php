<?php

namespace App\Tests;

class Helper
{
    public static function uuidV4Matcher(): callable
    {
        return function ($id) {
            return is_string($id) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $id) === 1;
        };
    }
}
