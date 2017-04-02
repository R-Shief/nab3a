<?php

namespace app {

    use Symfony\Component\Dotenv\Dotenv;
    use Symfony\Component\Dotenv\Exception\PathException;

    function dotenv_load()
    {
        try {
            (new Dotenv())->load(__DIR__.'/../.env');
        } catch (PathException $e) {
            //
        }
    }
}
