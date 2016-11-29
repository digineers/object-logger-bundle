<?php

namespace Fizz\ObjectLoggerBundle\Log\Processor\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Allows adding extra data to an EntityLog
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class EntityLogExtraEvent extends Event
{

    /**
     * @var array $extra
     */
    private $extra = array();


    public function __construct($extra = array())
    {
        $this->extra = $extra;
    }

    /**
     * Add extra data.
     *
     * @param $key
     * @param $value
     */
    public function addExtra($key, $value)
    {
        if(isset($this->extra[$key])) {
            throw new \RuntimeException('Key already exists.');
        }
        $this->extra[$key] = $value;
    }

    /**
     * Check if extra data with given key already exists in event.
     *
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->extra[$key]);
    }

    /**
     * Get all extra data.
     *
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

}