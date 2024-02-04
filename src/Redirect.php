<?php

namespace Webservis;

class Redirect
{
    /**
     * @param string $toUrl
     * @param int $status
     * @return void
     */
    public static function to(string $toUrl, int $status=301)
    {
        header('Location:'.BASE_PATH.$toUrl, true, $status);
        exit();
    }
}
