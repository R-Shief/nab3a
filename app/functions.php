<?php

namespace app {

    use Symfony\Component\Debug\Debug;
    use Symfony\Component\Dotenv\Dotenv;
    use Symfony\Component\Dotenv\Exception\PathException;

	/**
	 * Enables debugging during bootstrap.
	 */
    function debug()
    {
        Debug::enable();
    }

    function dotenv_load()
    {
        try {
            (new Dotenv())->load(__DIR__.'/../.env');
        } catch (PathException $e) {
            //
        }
    }
}
