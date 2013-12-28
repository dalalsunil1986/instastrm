<?php

/**
 * This is the model class for table "tags".
 *
 * The followings are the available columns in table 'tags':
 * @property string $id
 * @property string $name
 * @property string $unix_time
 *
 * @author Joel Capillo <hunyoboy@gmail.com>
 * 
 */
class Tags extends CActiveRecord
{
	const expiretime = 20; 
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Tags the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tags';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, unix_time', 'required'),
			array('name, unix_time', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, unix_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		   'details' => array(self::HAS_MANY, 'MediaDetails', 'tag_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'unix_time' => 'Unix Time',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('unix_time',$this->unix_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	 * Checks if a given tag exist on the database
	 *
	 * @param string $tag the tag to check
	 *
	 * @return boolean
	 */
	public function isExist($tag){
	  $sql = 'SELECT id FROM tags WHERE name ="'.$tag.'" LIMIT 1';
          $tag_id = Yii::app()->db->createCommand($sql)->queryScalar();
	  if($tag_id > 0){
		return true;
	  }
	  else
	   return false;
	}
	
	/**
	 * Saves a tag
	 *
	 * @param string the tag to save
	 */
	public function saveTag($tag){
	 $unix_current_time = strtotime(date("Y-m-d H:i:s"));//grab the current time in unix format
	 $command = 'INSERT INTO tags VALUES(null,"'.$tag.'",'.$unix_current_time.')';
	 return Yii::app()->db->createCommand($command)->execute();
	}
	
	
	/**
	 * To be called by a cronjob deleting unused tags and its respective media_details every minute
	 *
	 */
	public function deleteUnusedTags(){
           $unix_current_time = strtotime(date("Y-m-d H:i:s"));
	   foreach(self::model()->findAll() as $tag){
		$diff = $unix_current_time - $tag->unix_time;
		if($diff >= self::expiretime ){//check if a tag is old
		  foreach($tag->details as $detail){
		    $detail->delete();
		  }
		  $tag->delete();
		}
		echo $diff."\n";
	   }
	}
	
	
	/**
	 * Called through ajax every second to signify that this tag is being used and shouldn't be deleted
	 *
	 * @param integer $tag_id the id of the tag to update
	 */
	public function updateTagTime($tag_id){
	  $command = 'UPDATE tags SET unix_time = '.strtotime(date("Y-m-d H:i:s")).' WHERE id ='.$tag_id;
	  $result = Yii::app()->db->createCommand($command)->execute();
	  if($result > 0){
		return true;
	  }
	  else
	   return false;
	}
	
	
	/**
	 * Returns all active tags
	 *
	 */
	public function allTags(){
	   $tags=array();
	   $sql = 'SELECT name FROM tags';
	   $result = Yii::app()->db->createCommand($sql)->queryAll();
	   if($result != 0){
		foreach($result as $row){
		  $tags[]=$row['name'];	
		}
	   }
	   return $tags;
	}
}