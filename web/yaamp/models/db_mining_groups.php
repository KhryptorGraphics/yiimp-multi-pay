<?php

class db_mining_groups extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'mining_groups';
	}

	public function rules()
	{
		return array(
			array('slug, title, algo, mode', 'required'),
			array('slug', 'unique'),
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
