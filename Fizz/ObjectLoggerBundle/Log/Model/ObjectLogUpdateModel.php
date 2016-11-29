<?php

namespace Fizz\ObjectLoggerBundle\Log\Model;

/**
 * Basic object log model including the old and new values.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class ObjectLogUpdateModel extends ObjectLogModel
{

    /**
     * @var array $changedFields
     */
    private $changedFields;


    /**
     * Constructor.
     *
     * @param object $object
     * @param string $type
     * @param array $changedFields
     */
    public function __construct($object, $type, $changedFields)
    {
        parent::__construct($object, $type);

        $this->changedFields = $changedFields;
    }

    /**
     * Get changed fields.
     *
     * @return array
     */
    public function getChangedFields()
    {
        return $this->changedFields;
    }

}