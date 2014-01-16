<?php

namespace User\Controller;

use Zend\Authentication;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use User\Model\UserProfile;
use User\Model\UserProfileTable;

class UserController extends AbstractActionController
{
	protected $user_profile_table;
	
	public function profileAction()
	{
		$this->layout("layout/personal");
		$auth = new \Zend\Authentication\AuthenticationService();
		if ($auth->hasIdentity())
		{
			$identity = $auth->getIdentity();
		
			return new ViewModel(array('identity' => $identity ));
		}
		
		return $this->redirect()->toRoute("account");
	}
		
	private function createJsonModel($var_array)
	{
		$jm = new JsonModel(array('method' => 'post', 'code' => $var_array));
		$jm->setTerminal(true);
		return $jm;
	}

	public function getUserProfileTable()
	{
		if (!$this->user_profile_table) {
			$sm = $this->getServiceLocator();
			$this->user_profile_table = $sm->get('User\Model\UserProfileTable');
		}
		return $this->user_profile_table;
	}

}
