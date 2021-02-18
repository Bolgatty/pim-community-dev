<?php
namespace Bolgatty\WorkFlowBundle\Component\Authorization;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class PermissionChecker
{

    /** @var  */
    protected $aclManager;

    /** @var  */
    protected $privilegeConfig;
  
    public function __construct(
        $aclManager,
        $privilegeConfig
    ) {
        $this->privilegeConfig = $privilegeConfig;
        $this->aclManager      = $aclManager;
    }

    /**
     * @param ArrayCollection $privileges
     * @param array           $rootIds
     *
     * @return ArrayCollection
     */
    protected function filterPrivileges(ArrayCollection $privileges, array $rootIds)
    {
        return $privileges->filter(
            function ($entry) use ($rootIds) {
                return in_array($entry->getExtensionKey(), $rootIds);
            }
        );
    }

    private function isAuthorizeToSendforApproval(array $roles)
    {
        $flag = false;
        $key_permi = "action:bolgatty_product_or_product_model_send_for_approval";


        return $flage;

    }

    public function isAuthorizeforProposals(array $roles)
    {
        $acl_extension = "action:bolgatty_product_or_product_model_proposals";
        $data = [];
        foreach($roles as $role) {
            $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges($this->aclManager->getSid($role));          
            foreach ($this->privilegeConfig as $fieldName => $config) {
                $sortedPrivileges = $this->filterPrivileges($privileges, $config['types']);
                    foreach ($sortedPrivileges as $sortedPrivilege) {
                        $accessLevel = $sortedPrivilege->getPermissions()->get('EXECUTE')->getAccessLevel();
                        if($acl_extension == $sortedPrivilege->getIdentity()->getId() && 0 != $accessLevel) {
                           $data[] = 1;
                        }
                    }
            }
        }

        return !empty($data) ? true : false;

    }
}
