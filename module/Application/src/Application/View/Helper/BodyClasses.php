<?php
/**
 * 
 * @author Remi THOMAS
 */
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Controller\Plugin\BodyClasses;

/**
 * Extended BodyClass helper class
 */
class BodyClasses extends AbstractHelper{
    
    protected $bodyClassesPlugin;


    /**
     * 
     * @return \Application\View\Helper\BodyClasses
     */
    public function __invoke()
    {
        return $this;
    }
    
    /**
     * 
     * @param \Application\Controller\Plugin\BodyClasses $plugin
     * @return \Application\View\Helper\BodyClasses
     */
    public function setBodyClassesPlugin(BodyClasses $plugin){
        $this->bodyClassesPlugin = $plugin;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function render(){
        $classes = $this->bodyClassesPlugin->getClasses();
        return implode(" ", $classes);
    }
    
}