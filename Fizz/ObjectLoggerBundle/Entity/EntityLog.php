<?php

namespace Fizz\ObjectLoggerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a single entity log message.
 *
 * @author Richard Snijders <richard@fizz.nl>
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Fizz\ObjectLoggerBundle\Entity\EntityLogRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class EntityLog
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $objectClass;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $objectIdentifier;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isTranslated = true;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $translationParameters = array();

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $translationDomain;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $extra = array();

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createDate;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * @param string $objectClass
     * @return EntityLog
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;
        return $this;
    }

    /**
     * @return int
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @param int $objectIdentifier
     * @return EntityLog
     */
    public function setObjectIdentifier($objectIdentifier)
    {
        $this->objectIdentifier = $objectIdentifier;
        return $this;
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
     * @return EntityLog
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsTranslated()
    {
        return $this->isTranslated;
    }

    /**
     * @param boolean $isTranslated
     * @return EntityLog
     */
    public function setIsTranslated($isTranslated)
    {
        $this->isTranslated = $isTranslated;
        return $this;
    }

    /**
     * @return array
     */
    public function getTranslationParameters()
    {
        return $this->translationParameters;
    }

    /**
     * @param array $translationParameters
     * @return EntityLog
     */
    public function setTranslationParameters($translationParameters)
    {
        $this->translationParameters = $translationParameters;
        return $this;
    }

    /**
     * @return string
     */
    public function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    /**
     * @param string $translationDomain
     * @return EntityLog
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     * @return EntityLog
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }


    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createDate = new \DateTime();
    }

}