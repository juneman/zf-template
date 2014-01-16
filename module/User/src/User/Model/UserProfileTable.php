<?php

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

class UserProfileTable extends AbstractTableGateway
{
    protected $table = 'user_profile_tb';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;

        $this->resultSetPrototype = new HydratingResultSet();
				$this->resultSetPrototype->setObjectPrototype(new UserProfileItem());
				$this->resultSetPrototype->setHydrator(new ReflectionHydrator());

        $this->initialize();
    }
		
		public function getProfilesByUserID($user_id)
		{
			$uid = (int) $user_id;
			$rowset = $this->select(array('user_id' => $uid));

			if ($rowset == null || $rowset->count() < 1)
			{
				return null;
			}
			return $rowset;
		}
		
		public function getProfileByKey($user_id, $key)
		{
			$uid = (int) $user_id;
			$rowset = $this->select(array('user_id' => $uid, 'key' => $key));
			if ($rowset == null || $rowset->count() < 1)
			{
				return null;
			}
			return $rowset;
		}
		
		public function getProfile($id)
		{
			$id = (int) $id;
			$rowset = $this->select(array('id' => $id));
			if ($rowset == null || $rowset->count() < 1)
			{
				return null;
			}
			return $rowset;
		}

		public function saveProfile($user_id, $key, $value)
		{
			$rowset= $this->getProfileByKey($user_id, $key);

			$item =  $rowset == null ? null :  $rowset->current();

			$id = $item ?(int) $item->id : 0;

			if ($id == 0) {
				$data = array(
						'user_id' => $user_id,
						'key' => $key,
						'value' => $value,
						);
				$this->insert($data);
			} else {
				if ($this->getProfile($id)) {
					$this->update(array('value' => $value), 
												array('id' => $id));
				} else {
					throw new \Exception("Could save profile id:$id, user id:$user_id");
				}
			}
		}

		public function clearProfilesByUserID($user_id)
		{
				$this->delete(array('user_id' => $user_id));
		}
		
		public function deleteProfile($uid, $key) 
		{
			$row = $this->getProfileByKey($uid, $key);
			if ($row != null)
			{
				$this->delete(array('user_id' => $uid, 'key' => $key));
			}
		}
}

