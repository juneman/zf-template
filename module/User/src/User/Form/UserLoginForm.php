<?php
namespace User\Form;

use Zend\Form\Form;

class UserLoginForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('user_login');

        $this->setAttribute('method', 'post');
				$this->setAttribute('class', 'form-signin');

				$this->add(array(
            'name' => 'login_email',
            'attributes' => array(
                'type'  => 'text',
								'class' =>  'input-block-level',
								'placeholder' => 'Email address',
            ),
        ));

        $this->add(array(
            'name' => 'login_passwd',
            'attributes' => array(
                'type'  => 'password',
								'class' =>  'input-block-level',
								'placeholder' => 'Password',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Log In',
								'class' => 'btn btn-large btn-primary btn-block',
            ),
        ));

    }
}
