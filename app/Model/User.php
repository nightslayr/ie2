<?php
App::uses('AppModel', 'Model');
App::uses('Security', 'Utility');

/**
 * User Model
 *
 */
class User extends AppModel {
	public $belongsTo = ['Group'];
	public $recursive = 1;
	
	public $validate = [
		'username' => [
			'required' => [
				'rule' => 'notBlank',
				'message' => 'A username is required.',
			],
		],
		'password' => [
			'required' => [
				'rule' => 'notBlank',
				'message' => 'A password is required.',
			],
		],
	];

	public function beforeSave($options = array()) {
		if ( !empty($this->data['User']['password']) ) {
			$this->data['User']['password'] = Security::hash($this->data['User']['password'], 'blowfish');
		}

		return true;
	}
}
