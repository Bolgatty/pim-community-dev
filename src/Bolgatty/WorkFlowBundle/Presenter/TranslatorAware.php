<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provide translation capability default implementation
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
trait TranslatorAware
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Set the translator
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
