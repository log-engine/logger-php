<?php

namespace LogEngine\Tests;


class Utils
{
    public static function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}