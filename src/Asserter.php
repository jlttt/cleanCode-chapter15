<?php

namespace Jlttt\cleanCode15;

class Asserter
{
    public static function format(?string $message, ?string $expected, ?string $actual): string
    {
        if (is_null($expected)) {
            $expected = "null";
        }
        if (is_null($actual)) {
            $actual = "null";
        }
        if (!empty($message)) {
            return sprintf("%s expected:<%s> but was:<%s>", $message, $expected, $actual);
        }
        return sprintf("expected:<%s> but was:<%s>", $expected, $actual);
    }
}
