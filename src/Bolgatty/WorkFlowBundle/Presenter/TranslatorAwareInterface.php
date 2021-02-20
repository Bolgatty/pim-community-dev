<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provide translation capability
 */
interface TranslatorAwareInterface
{
    /**
     * Set the translator
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator);
}
