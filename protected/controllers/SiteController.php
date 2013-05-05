<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}
	
	//action where users got redirected if they opened another browser
	public function actionOverLimit(){
	   $this->render('limit');
	}
    
        //load the main page
	public function actionIndex()
	{
		$str = '';
		$tag = '';
		$tag_id = 0;
		
		if(isset($_POST['tag'])){
		   
		   if(strlen($_POST['tag']) == 0){
		      Yii::app()->user->setFlash('error', "Please enter a tag.");
		   }
		   else{
			
			$tag = $_POST['tag'];
			if(Tags::model()->isExist($tag)){
			     $tag_id = SocialMedia::tagId($tag);
			}
			else{
			   if(Tags::model()->saveTag($tag) != 0)//save tag first
			      $tag_id = SocialMedia::tagId($tag);
			      
			   $social_media = new SocialMedia($tag,120);
			   $social_media->queryAPI();//query instagram API
			 }
			
			$str = SocialMedia::instagramFeed($tag_id);
		   }
		}
		else{
			
			$tag_array = array('#love','#cute','#instagood','#instahub','#tweegram','#photooftheday','#igers','#me','#phoneonly'
					   ,'#picoftheday','#girl','#summer','#tbt','#sky','#jj','#instadaily','#bestoftheday','#beautiful','#picoftheday','#instamood','#food');
			
			if(isset($_GET['toptag']) && strlen(trim($_GET['toptag'])) > 0 && in_array('#'.$_GET['toptag'],$tag_array) ){
			   $tag = '#'.$_GET['toptag'];
			}else{
			   $tag = $this->array_random($tag_array);	
			}
			
			if(Tags::model()->isExist($tag)){
			     $tag_id = SocialMedia::tagId($tag);
			}
			else{
			   if(Tags::model()->saveTag($tag) != 0)//save tag first
			      $tag_id = SocialMedia::tagId($tag);
			      
			   $social_media = new SocialMedia($tag,120);
			  
			   $social_media->queryAPI();//query instagram API
			 }
			
			$str = SocialMedia::instagramFeed($tag_id);
		}
        
		$this->tag = $tag;
		$this->str = $str;
		
		$this->render('index',array('str'=>$str,'tag'=>$tag,'tag_id'=>$tag_id));
	}
	
	
	private function array_random($arr, $num = 1) {
		shuffle($arr);
		$r = array();
		for ($i = 0; $i < $num; $i++) {
		$r[] = $arr[$i];
		}
		return $num == 1 ? $r[0] : $r;
	}

	public function topTenTags(){
	    
	    $temp_arr = array();
	    $tag_array = array('#love','#cute','#instagood','#instahub','#tweegram','#photooftheday','#igers','#me','#phoneonly'
				   ,'#picoftheday','#girl','#summer','#tbt','#sky','#jj','#instadaily','#bestoftheday','#beautiful','#picoftheday','#instamood','#food');
	    $i = 1;
	    
	    while($i < 14){
		
		$tag = $this->array_random($tag_array);
		if(!in_array($tag,$temp_arr))
		   $temp_arr[]=$tag;
		   
		$i = count($temp_arr);
	    }
	    
	    $str = '';
	    foreach($temp_arr as $tag){
		$clean_tag = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $tag);
		$str .= '<span class="label label-success"><a href="'.$this->createUrl('site/index/?toptag='.$clean_tag).'" style="color:#fff">'.$tag.'</a></span> ';
	    }
	    return $str;
	}
	
	
	
	public function actionInstagramRefresh(){
		
		if(!Yii::app()->request->isAjaxRequest){ //only allow ajax request
		      echo 'none';
		      die();
		}
		  
		if(isset($_POST['age'],$_POST['tagId'])){
		     $maxAge = intval($_POST['age']);
		     $tag_id = intval($_POST['tagId']);
		     
			if(isset($_POST['tagName'])){   
			   
			   $isExist = Tags::model()->isExist($_POST['tagName']);//we cannot find the tag on the database must got deleted
			   $tag = Tags::model()->findByPk($tag_id);
			   
			   if($isExist == false && isset($tag) && trim(strtolower($tag->name)) != trim(strtolower($_POST['tagName']))){//the tag ID got changed
			      echo 'refresh';
			      die();
			   }
			   elseif($isExist == true && !isset($tag)){//tag still exist but id got changed
			     echo 'refresh';
			      die();
			   }
			}
			
			Tags::model()->updateTagTime($tag_id);//update the time for this tag inorder not to be deleted
			echo SocialMedia::getFreshInstagrams($tag_id,$maxAge);
		  }
		  elseif(isset($_POST['tagId']) && !isset($_POST['age'])){ //user stop the stream but continue update the tagId timestamp to not be deleted
		     $tag_id = intval($_POST['tagId']);
		     Tags::model()->updateTagTime($tag_id);
		  }
		  else{
		    echo 'none';   
		  }
	      
		  $this->cleanMemory();//clean up memory
	      
	}
    
    
	public function actionOldInstagram(){
	  
	   if(!Yii::app()->request->isAjaxRequest){ //only allow ajax request
		 echo 'none';
		 die();
	   }
	  
	  if(isset($_GET['age'],$_GET['tagId'])){
	     echo SocialMedia::getOldInstagram(intval($_GET['tagId']),intval($_GET['age'])); //make sure it is an integer
	     die();
	  }
	  else{
	      echo 'none';
	  }  
	   
        }
       
	//action that renders the supersize version of the images
	public function actionSuperSize(){
		$age = '';
		if(isset($_GET['id']))
		   $age = $_GET['id'];
		   
		$tag = '';
		$tag_id = 0;
		$tag_array = array('#love','#cute','#instagood','#instahub','#tweegram','#photooftheday','#igers','#me','#phoneonly'
			,'#picoftheday','#girl','#summer','#tbt','#sky','#jj','#instadaily','#bestoftheday','#beautiful','#picoftheday','#instamood','#food');
		
		if(isset($_GET['tag']) && strlen(trim($_GET['tag'])) > 0){
		   $tag = '#'.$_GET['tag'];
		}else{
		  $tag = $this->array_random($tag_array);	
		}
		
		if(Tags::model()->isExist($tag)){
		  $tag_id = SocialMedia::tagId($tag);
		}
		else{
			if(Tags::model()->saveTag($tag) != 0)//save tag first
			  $tag_id = SocialMedia::tagId($tag);
			
			$social_media = new SocialMedia($tag,120);
			$social_media->queryAPI();//query instagram API
		}
		
		$this->layout = '//layouts/supersizecolumn';
		$this->render('supersize',array('tag'=>preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $tag),'tag_id'=>$tag_id,'age'=>$age));
	}
	
    
	//ajax action that retrieves the data for the slideshow
	public function actionSlideInstagram(){
	   
	    if(!Yii::app()->request->isAjaxRequest){ //only allow ajax request
	      echo 'none';
	      die();
	   }
	   
	   if(isset($_POST['age'],$_POST['tagId'])){
		  $slides = '';
		      Tags::model()->updateTagTime(intval($_POST['tagId']));//update the time for this tag inorder not to be deleted
		  $slides = SocialMedia::getSlideShow(intval($_POST['tagId']),intval($_POST['age']));
		  echo $slides;
	       }
	   else{
	       echo 'none';
	   }  
	       $this->cleanMemory();//clean up memory
	}

	
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	
////////////////////////////////////actions called by cronjobs///////////////////////////////////////////////////
	
	//queried every minute by cronjob
	public function actionApi(){
		set_time_limit(0);
		for($i=1;$i<=3;$i++){
			$tags = Tags::model()->allTags();
			if(count($tags) == 0){
				 sleep(20);
				 $this->cleanMemory();
				 continue;
			 }
			 
			 $social_media = new SocialMedia($tags,40); 
			 $social_media->queryAPI();
			 sleep(10);
			 $this->cleanMemory();
		}
	}
	
	//queried every minute by cronjob
	public function actionTags(){
	   set_time_limit(0);
	   for($i=1;$i<=2;$i++){
		$tags = Tags::model()->allTags();
		if(count($tags) == 0){
			sleep(30);
			$this->cleanMemory();
			continue;
		}
		Tags::model()->deleteUnusedTags();//delete unused tags and related media details
		sleep(20);
		$this->cleanMemory();
	    }	
	}
	
	//queried every 3 minutes by cron to clean up database for media details older than the specified time
	public function actionClean(){
	  set_time_limit(0);
          $minutes = 60*5; //delete all media details older than 5 minutes
	  MediaDetails::model()->cleanUp($minutes);
	  $this->cleanMemory();
	}
	
	public function cleanMemory(){
		gc_enable(); // Enable Garbage Collector
		gc_enabled(); // true
		gc_collect_cycles(); // # of elements cleaned up
		gc_disable(); // Disable Garbage Collector 
	}
    
}