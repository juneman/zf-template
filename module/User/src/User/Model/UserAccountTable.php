<?php

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;


class UserAccountTable extends AbstractTableGateway
{
    protected $table = 'user_account_tb';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;

        $this->resultSetPrototype = new HydratingResultSet();
				$this->resultSetPrototype->setObjectPrototype(new UserAccount());
				$this->resultSetPrototype->setHydrator(new ReflectionHydrator());

        $this->initialize();
    }

		public function getAccount($email)
		{
			$rowset = $this->select(array('login_email' => $email));
			if ($rowset == null || $rowset->count() < 1)
			{ return null; }
			
			return $rowset->current();
		}
		
		public function isEmailExist($email)
		{
			$row = $this->getAccount($email);
			
			return ($row == null) ? false  :  true ;

		}

		public function addAccount($email, $passwd)
		{
			$row = $this->getAccount($email);
			if ($rowset != null)
			{ return 'exist'; }
			
			$this->insert(array('login_email' => $email, 'login_passwd' => $passwd));
			
			return 'ok';
		}
		
}
