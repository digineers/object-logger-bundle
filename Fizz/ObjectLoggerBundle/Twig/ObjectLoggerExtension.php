<?php

namespace Fizz\ObjectLoggerBundle\Twig;

use Fizz\ObjectLoggerBundle\Entity\EntityLog;
use Symfony\Component\Translation\TranslatorInterface;

class ObjectLoggerExtension extends \Twig_Extension
{

    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('render_entity_log', array($this, 'renderEntityLog')),
        );
    }

    /**
     * Converts an entity log to a string.
     *
     * @param EntityLog $log
     * @return string
     */
    public function renderEntityLog(EntityLog $log)
    {
        if(!$log->getIsTranslated()) {
            return $log->getMessage();
        }

        return $this->translator->trans($log->getMessage(), $log->getTranslationParameters(), $log->getTranslationDomain());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'object_logger_extension';
    }
}