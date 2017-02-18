<?php

namespace MrCat\SugarCrmOrmApi\Capsule;

use MrCat\SugarCrmWrapper\SugarCrmWrapper;

class ManagerSugarCrm
{
    /**
     * Options for instance class SugarCrmWrapper
     *
     * @var array
     */
    protected $options = [];

    /**
     * New instance class.
     *
     * @var $this
     */
    private static $instance = null;

    /**
     * Set Login in Class SugarCrmWrapper
     *
     * @param array $credentials
     * <pre>
     *      [
     *          'username' => 'xxxx',
     *          'password' => 'xxxxx',
     *      ]
     * </pre>
     *
     * @return \MrCat\SugarCrmWrapper\SugarCrmWrapper
     */
    public function login(array $credentials = [])
    {
        SugarCrmWrapper::config($this->options)->login($credentials);

        return SugarCrmWrapper::get();
    }

    /**
     * Set session in Class SugarCrmWrapper
     *
     * @param $session
     * @return \MrCat\SugarCrmWrapper\SugarCrmWrapper
     */
    public function setSession($session = '')
    {
        SugarCrmWrapper::config($this->options)->setSession($session);

        return SugarCrmWrapper::get();
    }

    /**
     * Get Class SugarCrmWrapper
     *
     * @return \MrCat\SugarCrmWrapper\SugarCrmWrapper
     */
    public static function get()
    {
        return SugarCrmWrapper::get();
    }

    /**
     * Gets the instance via lazy initialization (created on first usage).
     *
     * @return self
     */
    public static function make()
    {
        return self::$instance;
    }

    /**
     * Instance new Api with config.
     *
     * @param array $parameters
     *
     * @return static
     */
    public static function config(array $parameters)
    {
        if (null === static::$instance) {
            static::$instance = new static($parameters);
            SugarCrmWrapper::config($parameters);
        }

        return static::$instance;
    }

    /**
     * Config Class SugarCrmWrapper
     *
     * @param $options
     *      <pre>
     *          $options = [
     *              'base_uri' => '',
     *              'uri'   => '',
     *              'timeout' => 2.0,
     *          ];
     *      </pre>
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Prevent the instance from being cloned.
     *
     * @throws \Exception
     *
     * @return void
     */
    final public function __clone()
    {
        throw new \Exception('This is a Singleton. Clone is forbidden');
    }

    /**
     * Prevent from being unserialized.
     *
     * @throws \Exception
     *
     * @return void
     */
    final public function __wakeup()
    {
        throw new \Exception('This is a Singleton. __wakeup usage is forbidden');
    }
}
