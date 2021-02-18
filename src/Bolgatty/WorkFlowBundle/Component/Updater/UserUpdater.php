<?php
namespace Bolgatty\WorkFlowBundle\Component\Updater;

use Akeneo\UserManagement\Component\Updater\UserUpdater as MainUserUpdater;
use Doctrine\Common\Util\ClassUtils;

class UserUpdater extends MainUserUpdater
{
    /**
     * @param UserInterface $user
     * @param string        $field
     * @param mixed         $data
     *
     * @throws InvalidPropertyException
     */
    
    protected function setData(\ProductInterface $product, $field, $data, array $options = []): void
    {
        switch ($field) {
            case 'properties':                
                if($data != null) {
                    $user->setUserOptions($data);
                    $user->setProductGridFilters($data);
                }
                break;
            
            default:
                parent::setData($product, $field, $data, $options);
        }
    }
}