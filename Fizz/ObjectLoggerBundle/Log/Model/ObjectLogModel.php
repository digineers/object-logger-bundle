<?php

namespace Fizz\ObjectLoggerBundle\Log\Model;

/**
 * Model the log subscriber sends to a processor.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class ObjectLogModel
{

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $message
     */
    private $message;

    /**
     * @var object $object
     */
    private $object;


    /**
     * Constructor.
     *
     * @param object $object
     * @param string $type
     */
    public function __construct($object, $type)
    {
        $this->object = $object;
        $this->type = $type;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get object.
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return ObjectLogModel
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

}