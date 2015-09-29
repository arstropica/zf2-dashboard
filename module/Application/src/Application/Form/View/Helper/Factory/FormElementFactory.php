<?php

namespace Application\Form\View\Helper\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Form\View\Helper\FormElement;

/**
 * Factory to inject the ModuleOptions hard dependency
 *
 * @license MIT
 */
class FormElementFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->getServiceLocator()->get('Application\Options\ModuleOptions');
        return new FormElement($options);
    }
}