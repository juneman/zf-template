<?php

namespace User\Controller;

use Zend\Authentication\Adapter, 
		Zend\Authentication,
		Zend\Authentication\Result;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use User\Model\UserProfile;
use User\Model\UserAccount;
use User\Model\UserAccountTable;
use User\Model\UserProfileTable;
use User\Form\UserLoginForm;

class AccountController extends AbstractActionController
{
	protected $user_account_table;
	protected $user_profile_table;

	public function auth_with(UserAccount $account)
	{
		$email = $account->login_email;
		$passwd = $account->login_passwd;

		$authAdapter = new \Zend\Authentication\Adapter\DbTable(
								$this->getUserAccountTable()->getAdapter(),
				'user_account_tb',        // 数据库中存放注册信息的表的名称
				'login_email',   // 表中存放用户名的字段
				'login_passwd');     // 表中存放密码的字段
		// 'md5(?)');     // 是否对密码进行MD5编码,此处未编码

		$authAdapter->setIdentity($email)
			->setCredential($passwd);

		$auth = new \Zend\Authentication\AuthenticationService();
		$result = $auth->authenticate($authAdapter);
		if ($result->isValid())
		{
			$uid = $authAdapter->getResultRowObject()->id;

			$profile = new UserProfile($this->getUserProfileTable()->getAdapter());
			$profile->load($uid);
			$profile->updateLastLoginTimestamp($uid);
			$auth->getStorage()->write((object)
					array('user_id' => $uid,
						'login_email' => $email,
						'nick_name' => $profile->getProfile('nick_name'),
						'ts_last_login' => $profile->getProfile('ts_last_login'),
						));
			
			if ($profile->getProfile('wizarded') == null)
			{
				return array('code' => 0, 'message' => "Login OK: $email.");
			}
			else if ($profile->getProfile('wizarded') == '0')
			{
				return array('code' => 1, 'message' => "Login OK: $email. Go to wizard");
			}
		}

		switch($result->getCode())
		{
			case Result::FAILURE_IDENTITY_NOT_FOUND:
				$errorMessage = "Admin name isn't exist!";
				break;
			case Result::FAILURE_CREDENTIAL_INVALID:
				$errorMessage = "Your password is wrong!";
				break;
			default:
				$errorMessage = "There are some mistakes when login!";
		}

		return array('code' => 2, 'message' => "Login Failed: $email." . $errorMessage );
	}
	
	public function authAction() 
	{
		
		$auth = new \Zend\Authentication\AuthenticationService();
		if ($auth->hasIdentity())
		{
			return $this->redirect()->toRoute('home');
		}
		
		$do = $this->params('do');
		
		$this->layout("layout/login");
		
		if ($do == 'login') {
			return $this->login();
		}
		else if ($do == 'signup') {
			return $this->signup();
		}
		
		return new ViewModel(
				array('section' => 'login' ,
					'login_error_msg' => null,
					));
	}

	private function login()
	{
		$form = new UserLoginForm();
		$request = $this->getRequest();

		if ($request->isPost()) {
			$user_account = new UserAccount();
			$form->setInputFilter($user_account->getInputFilter());
			$form->setData($request->getPost());
			if ($form->isValid()) {
				$user_account->exchangeArray($form->getData());
				$auth_rst = $this->auth_with($user_account);

				if ($auth_rst['code'] == 0) {
					return $this->redirect()->toRoute('home');
				}
				else if ($auth_rst['code'] == 1) {
					return $this->redirect()->toRoute('rsconfig');
				}

				return new ViewModel(
						array('section' => 'login' ,
							'login_error_msg' => 'The email or password you entered is incorrect.',
							));
			}
		}

		return new ViewModel(
				array('section' => 'login' ,
					'login_error_msg' => null,
					));
	}

	private function signup()
	{
		$error_msg = null;
		$request = $this->getRequest();
		if ($request->isPost()) {
			$email = $request->getPost()->get('sign_email');
			$passwd = $request->getPost()->get('sign_passwd');
			$confirm_passwd = $request->getPost()->get('confirm_passwd');

			$ret = $this->signup_with($email, $passwd, $confirm_passwd);
			if ($ret['code'] == 0)
			{
				return $this->redirect()->toRoute('rsconfig');
			}

			$error_msg = $ret['message'];
	}

	return new ViewModel(
			array('section' => 'signup' ,
				'signup_error_msg' => $error_msg,
				));
}

private function signup_with($email, $passwd, $confirm_passwd)
{
		if (strcmp($passwd, $confirm_passwd) != 0) 
		{
			return array(
					'code' => 1,
					'message' => 'The two passwords you typed do not match.',
					);
		}
		
		if (strlen($passwd) < 6 || strlen($passwd) > 32) 
		{
			return array(
					'code' => 2,
					'message' => 'The password is too short or too long, must be 6~32.',
					);
		}
		
		// check the email pattener is valid
		if ($this->checkEmail($email) == false)
		{
			return array(
					'code' => 2,
					'message' => 'Please typed an valid email address.',
					);
		}


		$flag = $this->getUserAccountTable()->isEmailExist($email);
		if ($flag == true) {
			return array(
					'code' => 3,
					'message' => 'The email you typed is exist.',
					);
		}

		$ret1 = $this->getUserAccountTable()->addAccount($email, $passwd);
		if ($ret1 == 'exist')
		{
			return array(
					'code' => 3,
					'message' => 'The email you typed is exist.',
					);
		}

		$row = $this->getUserAccountTable()->getAccount($email);
		if ($row == null) 
		{
			return array(
					'code' => 4,
					'message' => 'Create account failed, please try again later.',
					);
		}
		
		// write profile
		$uid = $row->id;
		$pos = strpos($email, "@");
		$nick_name = $pos == false ? $email : substr($email, 0, $pos) ;
		$ret2 = $this->getUserProfileTable()->saveProfile($uid, 'nick_name', $nick_name);
		$ret2 = $this->getUserProfileTable()->saveProfile($uid, 'ts_create', date('Y-m-d H:i:s'));
		$ret2 = $this->getUserProfileTable()->saveProfile($uid, 'ts_last_login', date('Y-m-d H:i:s'));
		$ret2 = $this->getUserProfileTable()->saveProfile($uid, 'wizarded', '0');
  	$ret2 = $this->getUserProfileTable()->saveProfile($uid, 'vocabulary', '1');

		// storage
		$auth = new \Zend\Authentication\AuthenticationService();
		$profile = new UserProfile($this->getUserProfileTable()->getAdapter());
		$profile->load($uid);
		$profile->updateLastLoginTimestamp($uid);
		$auth->getStorage()->write((object)
				array('user_id' => $uid,
					'login_email' => $email,
					'nick_name' => $profile->getProfile('nick_name'),
					'ts_last_login' => $profile->getProfile('ts_last_login'),
					'wizarded' => '0',
					));

		return array('code' => 0);
	}

	public function logoutAction()
	{
		$this->layout("layout/logout");

		$auth = new \Zend\Authentication\AuthenticationService();
		if ($auth->hasIdentity())
		{
			$auth->clearIdentity();
		}
		
		return new ViewModel();
	}
	
	private function checkEmail($email)
	{
		// Todo 
		return true;
	}

	public function getUserAccountTable()
	{
		if (!$this->user_account_table) {
			$sm = $this->getServiceLocator();
			$this->user_account_table = $sm->get('User\Model\UserAccountTable');
		}
		return $this->user_account_table;
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
