<?php

namespace Fizz\ObjectLoggerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for EntityLog.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class EntityLogRepository extends EntityRepository
{

    /**
     * Find all logs by entity
     *
     * @param $entity
     * @return array
     */
    public function findByEntity($entity)
    {
        $em = $this->_em;
        $md = $em->getClassMetadata(get_class($entity));
        if($md->isIdentifierComposite) {
            throw new \RuntimeException('Entity log does not support composite identifiers.');
        }
        return $this->findBy(array(
            'objectClass' => $md->getName(),
            'objectIdentifier' => current($md->getIdentifierValues($entity))
        ));
    }

}