<?php
/**
 * (c) Nuna Akpaglo <princedorcis@gmail.com>.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Prinx\Dotenv;

/**
 * Singleton class keeping the Dotenv instance.
 */
class DotenvInstance
{
    protected static $envInstance = null;

    /**
     * Returns the Dotenv instance.
     *
     * It instanciate the Dotenv class if not yet instanciated.
     *
     * @return Dotenv
     */
    public static function get()
    {
        if (! self::$envInstance) {
            self::load();
        }

        return self::$envInstance;
    }

    /**
     * Initialise the Dotenv instance.
     *
     * @param  string $path Path to the .env file
     * @return void
     */
    public static function load($path = null)
    {
        self::$envInstance = new Dotenv($path);
    }
}
