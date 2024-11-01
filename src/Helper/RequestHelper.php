<?php

namespace WaHelp\Newsletter\Helper;

class RequestHelper
{
    public static function removeQueryParam(string $url, string $param): string
    {
        $url = preg_replace('/([?&])' . $param . '=[^&]*(&|$)/', '$1', $url);

        $url = rtrim($url, '&');
        $url = rtrim($url, '?');

        return str_replace('?&', '?', $url);
    }
}