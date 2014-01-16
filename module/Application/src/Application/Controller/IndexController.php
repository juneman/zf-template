<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationService;

use User\Model\UserProfile;
use User\Model\UserProfileTable;

class IndexController extends AbstractActionController
{
	protected $profile_table;	

	public function indexAction()
	{
		$identity = null;

		$auth = new \Zend\Authentication\AuthenticationService();
		if ($auth->hasIdentity())
		{
			$identity = $auth->getIdentity();
		}
		else
		{
//			return $this->redirect()->toRoute('account');
		}

		$profile = new UserProfile($this->getProfileTable()->getAdapter());
		$profile->load($identity->user_id);
		
		// 已经完成了 wizard
		if ($profile->getProfile('wizarded') != null &&
				$profile->getProfile('wizarded') == "0")
		{
			return $this->redirect()->toRoute("rsconfig");
		}
		
		$view = new ViewModel(array(
					'identity' => $identity,
					));	
		$nav = new ViewModel(array('identity' => $identity, 'section' => 'home'));	
		$nav->setTemplate('layout/navigation');
		$view->addChild($nav,'navigation');

		return $view; 
	}

	public function getProfileTable()
	{
		if (!$this->profile_table) {
			$sm = $this->getServiceLocator();
			$this->profile_table = $sm->get('User\Model\UserProfileTable');
		}
		return $this->profile_table;
	}
	
	public function newJM($var_array)
	{
		$jm = new JsonModel(array('code' => $var_array));
		$jm->setTerminal(true);
		return $jm;
	}

}
