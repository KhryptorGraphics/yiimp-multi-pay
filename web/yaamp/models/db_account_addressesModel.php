<?php

class db_account_addresses extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'account_addresses';
	}

	public function rules()
	{
		return array(
			array('account_id, coinid, address', 'required'),
		);
	}

	public function relations()
	{
		return array(
		);
	}

	public function attributeLabels()
	{
		return array(
		);
	}
}
