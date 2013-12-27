<?php

/**
 * This is the model class for table "media_details".
 *
 * The followings are the available columns in table 'media_details':
 * @property string $id
 * @property integer $tag_id
 * @property string $media_url
 * @property string $display_url
 * @property string $text
 * @property string $profile_image_url
 * @property string $username
 * @property integer $media_id
 * @property integer $media_type_id
 * @property string $unique_identifier
 */
class MediaDetails extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MediaDetails the static model class
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
		return 'media_details';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('tag_id, media_id', 'required'),
			array('tag_id, media_id, media_type_id', 'numerical', 'integerOnly'=>true),
			array('media_url', 'length', 'max'=>140),
			array('display_url, unique_identifier', 'length', 'max'=>160),
			array('text', 'length', 'max'=>10000),
			array('profile_image_url', 'length', 'max'=>150),
			array('username', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, tag_id, media_url, display_url, text, profile_image_url, username, media_id, media_type_id, unique_identifier, date_created', 'safe', 'on'=>'search'),
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
		   'tag' => array(self::BELONGS_TO, 'Tags', 'tag_id'),
		   'media' => array(self::BELONGS_TO, 'Media', 'media_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'tag_id' => 'Tag',
			'media_url' => 'Media Url',
			'display_url' => 'Display Url',
			'text' => 'Text',
			'profile_image_url' => 'Profile Image Url',
			'username' => 'Username',
			'media_id' => 'Media',
			'media_type_id' => 'Media Type',
			'unique_identifier' => 'Unique Identifier',
			'date_created'=>'Date Created'
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
		$criteria->compare('tag_id',$this->tag_id);
		$criteria->compare('media_url',$this->media_url,true);
		$criteria->compare('display_url',$this->display_url,true);
		$criteria->compare('text',$this->text,true);
		$criteria->compare('profile_image_url',$this->profile_image_url,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('media_id',$this->media_id);
		$criteria->compare('media_type_id',$this->media_type_id);
		$criteria->compare('unique_identifier',$this->unique_identifier,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	//check if a media_detail is already on the database
	public function isExist($tag_id,$unique_identifier){
	  if(!isset($tag_id) || strlen((string)$tag_id) == 0){
	     return false;
	  }
	  else{
		$sql = 'SELECT id FROM media_details WHERE tag_id ='.$tag_id.' AND unique_identifier = "'.$unique_identifier.'"';
		$result = Yii::app()->db->createCommand($sql)->queryScalar();
		if($result > 0){
		      return true;
		}
		else
		  return false;
	  }
	}
	
	//delete all media details older than # minutes
	public function cleanUp($minutes_old){
	  $unix_current_time = strtotime(date("Y-m-d H:i:s"));
	  $time_back = $unix_current_time - ($minutes_old*60);//older than # minutes
	  $sql = 'DELETE FROM media_details WHERE date_created < '.$time_back;
	  $result = Yii::app()->db->createCommand($sql)->execute();
	  return $result;
	}
	
	//@param api_result -> result array from API query 
	public function saveMediaDetails($api_result){
	   
	   $count = count($api_result['media_id'])-1;
	   
	   for($i = 0; $i <= $count ; $i++){
		
		if($this->isExist($api_result['tag_id'][$i],$api_result['unique_identifier'][$i]))
		  continue;
		
		$media_detail = new self;
		$media_detail->tag_id = $api_result['tag_id'][$i];
		$media_detail->media_url = $api_result['media_url'][$i];
		$media_detail->display_url = $api_result['display_url'][$i];
		$media_detail->text = $api_result['text'][$i];
		$media_detail->profile_image_url = $api_result['profile_image_url'][$i];
		$media_detail->username = $api_result['username'][$i];
		$media_detail->media_id = $api_result['media_id'][$i];
		$media_detail->media_type_id = $api_result['media_type_id'][$i];
		$media_detail->unique_identifier = $api_result['unique_identifier'][$i];
		$media_detail->date_created = $api_result['date_created'][$i];
		
		if(!$media_detail->validate()){
		   if($i == $count){
			return false;
		   }
		   else
		     continue;
		}
		
		if(!$media_detail->save()){
		   if($i == $count){
			return false;
		   }
		   else
		     continue;
		}
		
	   }
	   
	   
	}
	
	
    public function displayByMediaId($tag_id,$media_id,$max_age = null){
	   
		$details = array();
		
		if(!isset($tag_id))
		   return $details;
		
		$sql = 'SELECT * FROM media_details WHERE tag_id = '.$tag_id.' AND media_id='.$media_id.' ORDER BY date_created DESC LIMIT 3';
		if(isset($max_age)){
		 $sql = 'SELECT * FROM media_details WHERE tag_id = '.$tag_id.' AND media_id='.$media_id.' AND date_created > '.$max_age.' ORDER BY date_created DESC LIMIT 1';
		}
		
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		if($result != 0){
		   foreach($result as $row){
		     $details[]=$row;	
		   }
		}
		
		return $details;
    }
    
     public function displaySlideShow($tag_id,$media_id,$max_age){
       
       $details = array();
       
       if(!isset($tag_id))
         return $details;
    
       $sql = 'SELECT * FROM media_details WHERE tag_id = '.$tag_id.' AND media_id='.$media_id.' AND date_created <= '.$max_age.' ORDER BY date_created DESC LIMIT 30';
    
       $result = Yii::app()->db->createCommand($sql)->queryAll();
       if($result != 0){
        foreach($result as $row){
          $details[]=$row;    
        }
       }
       
       return $details;
    }
    
    //returns a single Instagram feed that is older than the min_age
    public function displayByMediaIdOld($tag_id,$media_id,$min_age){
       
       $details = array();
       if(!isset($tag_id))
         return $details;
    
       $sql = 'SELECT * FROM media_details WHERE tag_id = '.$tag_id.' AND media_id='.$media_id.' AND date_created < '.$min_age.' ORDER BY date_created DESC LIMIT 1';
       $result = Yii::app()->db->createCommand($sql)->queryAll();
       if($result != 0){
        foreach($result as $row){
          $details[]=$row;    
        }
       }
       return $details;
    }
	
	
	
}