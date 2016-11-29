<?php

namespace Fizz\ObjectLoggerBundle\Log\Processor;

use Doctrine\ORM\EntityManager;
use Fizz\ObjectLoggerBundle\Entity\EntityLog;
use Fizz\ObjectLoggerBundle\Log\Model\ObjectLogModel;
use Fizz\ObjectLoggerBundle\Log\Model\ObjectLogUpdateModel;
use Fizz\ObjectLoggerBundle\Log\Processor\Event\EntityLogExtraEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Default entity log processor.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class DefaultEntityLogProcessor extends AbstractDelegatingEntityLogProcessor
{

    /**
     * @var array $enabledEntities
     */
    private $enabledEntities;

    /**
     * @var array $discriminatorMapping
     */
    private $discriminatorMapping;

    /**
     * @var array $ignoredFields
     */
    private $ignoredFields;

    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    private $eventDispatcher;


    /**
     * Constructor.
     *
     * @param array $ignoredFields
     * @param array $enabledEntities
     * @param array $discriminatorMapping
     */
    public function __construct(array $ignoredFields, array $enabledEntities, array $discriminatorMapping)
    {
        $this->ignoredFields = $ignoredFields;
        $this->enabledEntities = $enabledEntities;
        $this->discriminatorMapping = $discriminatorMapping;
    }

    /**
     * Ugly workaround for preventing circular references.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container) {
        $this->em = $container->get('doctrine.orm.default_entity_manager');
        $this->eventDispatcher = $container->get('event_dispatcher');
    }

    /**
     * Convert a ObjectLogModel to a translation key.
     *
     * @param ObjectLogModel $model
     * @return string
     */
    private function translationKey(ObjectLogModel $model)
    {
        $class = get_class($model->getObject());
        $namespace = explode('\\', $class);
        $key = '';
        foreach($namespace as $i => $part) {
            preg_match_all('/[A-Z][a-z]+/', $part, $subparts);
            if(0 === $i && 'Bundle' == end($subparts[0])) {
                array_pop($subparts[0]);
            }
            $key .= strtolower(implode('_', $subparts[0])) . '.';
        }
        $key .= $model->getType();

        return $key;
    }

    /**
     * Get the referred object by following the discriminator path.
     *
     * @param ObjectLogModel $model
     * @return object
     */
    private function getReferredObject(ObjectLogModel $model)
    {
        $object = $model->getObject();
        $class = get_class($object);
        if(isset($this->discriminatorMapping[$class])) {
            foreach($this->discriminatorMapping[$class] as $field) {
                $reflection = (new \ReflectionClass($object))->getProperty($field);
                $reflection->setAccessible(true);
                $object = $reflection->getValue($object);
            }
        }
        if(!is_object($object)) {
            throw new \RuntimeException(sprintf('Encountered non-object in path \'%s\' on object \'%s\'', implode(' -> ', $this->discriminatorMapping[$class]), get_class($model->getObject())));
        }
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectLogUpdateModel $model)
    {
        $changedFields = array_keys($model->getChangedFields());
        foreach($changedFields as $i => $field) {
            if(in_array($field, $this->ignoredFields)) {
                array_splice($changedFields, $i, 1);
            }
        }
        if(!count($changedFields)) {
            return;
        }
        $referredObject = $this->getReferredObject($model);
        $referredObjectClass = get_class($referredObject);
        $referredObjectMetadata = $this->em->getClassMetadata($referredObjectClass);
        if($referredObjectMetadata->isIdentifierComposite) {
            throw new \RuntimeException('FizzObjectLogger does not support composite identifiers for referred objects.');
        }
        $this->eventDispatcher->dispatch('fizz.object_logger.extra', $event = new EntityLogExtraEvent(array('changed_fields' => $changedFields)));
        $entityLog = new EntityLog();
        $entityLog
            ->setMessage($this->translationKey($model))
            ->setIsTranslated(true)
            ->setTranslationDomain('FizzObjectLoggerBundle')
            ->setObjectClass($referredObjectClass)
            ->setObjectIdentifier(current($referredObjectMetadata->getIdentifierValues($referredObject)))
            ->setExtra($event->getExtra());

        $this->em->persist($entityLog);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(ObjectLogModel $model)
    {
        $referredObject = $this->getReferredObject($model);
        $referredObjectClass = get_class($referredObject);
        $referredObjectMetadata = $this->em->getClassMetadata($referredObjectClass);
        if($referredObjectMetadata->isIdentifierComposite) {
            throw new \RuntimeException('FizzObjectLogger does not support composite identifiers for referred objects.');
        }
        $this->eventDispatcher->dispatch('fizz.object_logger.extra', $event = new EntityLogExtraEvent());
        $entityLog = new EntityLog();
        $entityLog
            ->setMessage($this->translationKey($model))
            ->setIsTranslated(true)
            ->setTranslationDomain('FizzObjectLoggerBundle')
            ->setObjectClass($referredObjectClass)
            ->setObjectIdentifier(current($referredObjectMetadata->getIdentifierValues($referredObject)))
            ->setExtra($event->getExtra());

        $this->em->persist($entityLog);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectLogModel $model)
    {
        $referredObject = $this->getReferredObject($model);
        $referredObjectClass = get_class($referredObject);
        $referredObjectMetadata = $this->em->getClassMetadata($referredObjectClass);
        if($referredObjectMetadata->isIdentifierComposite) {
            throw new \RuntimeException('FizzObjectLogger does not support composite identifiers for referred objects.');
        }
        $this->eventDispatcher->dispatch('fizz.object_logger.extra', $event = new EntityLogExtraEvent());
        $entityLog = new EntityLog();
        $entityLog
            ->setMessage($this->translationKey($model))
            ->setIsTranslated(true)
            ->setTranslationDomain('FizzObjectLoggerBundle')
            ->setObjectClass($referredObjectClass)
            ->setObjectIdentifier(current($referredObjectMetadata->getIdentifierValues($referredObject)))
            ->setExtra($event->getExtra());

        $this->em->persist($entityLog);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ObjectLogModel $model)
    {
        foreach($this->enabledEntities as $entityFQN) {
            if(is_a($model->getObject(), $entityFQN)) {
                return true;
            }
        }
        return false;
    }

}