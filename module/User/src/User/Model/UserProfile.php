<?php

namespace User\Model;

use Zend\Db\Adapter\Adapter;

class UserProfile 
{
	public $user_id;

	protected $_keyField = 'key';
	protected $_valueField = 'value';
	private $_properties = array();

	private $_keys = array('nick_name', 'ts_last_login', 'ts_create', 'wizarded', 'vocabulary');

	private $user_profile_table = null;
	
	public function __construct(Adapter $adapter)
	{
		$this->user_profile_table = new UserProfileTable($adapter);
	}

	public function load($user_id)
	{
		$uid = (int) $user_id;
		if ($uid <= 0) 
		{
			throw new \Exception("UserProfile load user: $user_id Error.");
		}

		$profiles = $this->user_profile_table->getProfilesByUserID($user_id);
		
		foreach ($profiles as $item)
		{
			$this->_properties[$item->key] = $item->value;
		}
	}

	public function getProfile($key)
	{
		if (isset( $this->_properties[$key])) 
		{	
			return $this->_properties[$key];
		}
			
		return null;
	}

	public function updateLastLoginTimestamp($user_id)
	{
		$uid = (int) $user_id;
		if ($uid <= 0) 
		{
			throw new \Exception("UserProfile load user: $user_id Error.");
		}
		
		$this->user_profile_table->saveProfile($user_id, 'ts_last_login', date('Y-m-d H:i:s'));	
	}

}
