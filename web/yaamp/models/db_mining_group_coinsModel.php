<?php

class db_mining_group_coins extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'mining_group_coins';
	}

	public function rules()
	{
		return array(
			array('group_id, coinid', 'required'),
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
