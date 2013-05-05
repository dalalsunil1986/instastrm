 <?php

class SocialMedia{
   
    private $tags;//array->an array of tags to search
    private $count;//integer->number of items to be returned from the query   
    
    public function __construct($tags, $count=200)
    {
       $this->tags = $tags;
       $this->count = $count; 
    }  
    
    
    private function doCurl($url){
            
            $clean_url = str_replace(" ","%20",$url);
            // Set up cURL
            $ch = curl_init();
            // Set the URL
            curl_setopt($ch, CURLOPT_URL, $clean_url);
            // don't verify SSL certificate
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // Return the contents of the response as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // Follow redirects
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            
            return $response; 
    } 
    
    
    private function getTags(){
        $tags = array();
        if(!is_array($this->tags)){
            $tags[]=$this->tags;
        }else{
            $tags = $this->tags;
        }
        return $tags;
    }
    
    
    private function instagramUrl($tag){
      $tag = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $tag);//strip out special characters since instagram does not allow them
      $url = "https://api.instagram.com/v1/tags/$tag/media/recent?access_token=175159176.f59def8.431c3ffc12e64e7bbd814cf7db8f58a6&count=".$this->count; 
      return $url; 
    }
    
    private function twitterUrl($tag){
        $url = "http://search.twitter.com/search.json?q=$tag&include_entities=1&count=".$this->count;
        return $url;
    }
    
    
    
    public function queryInstagram(){
        $tags = $this->getTags();
        $responses=array();//store all responses for each tag
        foreach($tags as $tag){
           $result = $this->doCurl($this->instagramUrl($tag));
           if(!empty($result))
             $responses[$tag] = $result; 
        }
        return $responses;
    }
    
    
    public function queryTwitter(){
        $tags = $this->getTags();
        $responses = array();
        foreach($tags as $tag){
            $result = $this->doCurl($this->twitterUrl($tag));
            if(!empty($result))
             $responses[$tag] = $result; 
        }
        return $responses;
    }
    
    public static function tagId($tag){
      $sql = 'SELECT id FROM tags WHERE name ="'.$tag.'" LIMIT 1';
      return Yii::app()->db->createCommand($sql)->queryScalar();  
    }
    
    public static function mediaId($name){
      $sql = 'SELECT id FROM media WHERE name ="'.$name.'"';
      return Yii::app()->db->createCommand($sql)->queryScalar();
    }
    
    public static function mediaTypeId($type){
       if($type=='image')
          $type = 'photo';
       
       $sql = 'SELECT id FROM media_type WHERE name ="'.$type.'"';
       return Yii::app()->db->createCommand($sql)->queryScalar();
    }
    
   
    //combine instagram and twitter queries
    public function queryAPI($includeTwitter=false){
        
        $return_array = array();
        
        $instagram_media_id = self::mediaId('instagram');
        $instagram = $this->queryInstagram();
        
        if($includeTwitter){
            
            $twitter_media_id = self::mediaId('twitter');
            $twitter = $this->queryTwitter();
            
            foreach($twitter as $tag=>$tweet){ //loop through each twitter requested tags
             
                $data = json_decode($tweet,true);
                
                if(!isset($data['results']))//if no data then just continue to next tag
                  continue;
                
                $tag_id =self::tagId($tag);
                
                foreach($data['results'] as $datum){
                  
                  $entities = $datum['entities'];
                  $return_array['tag_id'][] = $tag_id;
                  $return_array['date_created'][] = strtotime($datum['created_at']);
                  $return_array['unique_identifier'][] = $datum['id_str'];
                  $return_array['username'][]=$datum['from_user'];
                  $return_array['profile_image_url'][]=$datum['profile_image_url'];
                  $return_array['media_id'][]=$twitter_media_id;
                  $return_array['text'][] = ( count($entities['hashtags']) > 0 ? $entities['hashtags'][0]['text']:$datum['text'] );
                  
                  if(!isset($entities['media'])){
                      $return_array['display_url'][] = ( isset($entities['urls'][0]) ? $entities['urls'][0]['display_url']:null );
                      $return_array['media_url'][] = null;
                      $return_array['media_type_id'][] = null; 
                  }
                  else{
                    $return_array['display_url'][]=$entities['media'][0]['url'];
                    $return_array['media_url'][] = $entities['media'][0]['media_url'];
                    $return_array['media_type_id'][]=self::mediaTypeId($entities['media'][0]['type']);
                  }
                  
                }
            }
        }
        
        foreach($instagram as $tag=>$data){ //loop through each instagram requested tags
            
            $data = json_decode($data,true);
            if(!isset($data['data']))
              continue;
            
            $tag_id=self::tagId($tag);
            
            foreach($data['data'] as $datum){
              $return_array['media_id'][] = $instagram_media_id;
              $return_array['tag_id'][] = $tag_id;
              $return_array['unique_identifier'][] = $datum['caption']['id'];
              $return_array['media_url'][] = $datum['images']['standard_resolution']['url'];
              $return_array['username'][] = $datum['caption']['from']['username'];
              $return_array['profile_image_url'][] = $datum['caption']['from']['profile_picture'];
              $return_array['date_created'][] = $datum['created_time'];
              $return_array['text'][]=$datum['caption']['text'];
              $return_array['media_type_id'][]=self::mediaTypeId($datum['type']);
              $return_array['display_url'][]= $datum['link'];
            }
            
        }
        //print_r($return_array['text']);die();
        if(!empty($return_array))
           MediaDetails::model()->saveMediaDetails($return_array);//save the response
    }
    
    
    //@param maxAge -> unix time for the most recent feed
    //@param tagId -> index for the tag
    public static function getFreshInstagrams($tag_id,$maxAge){
        
        $fresh_instagrams = MediaDetails::model()->displayByMediaId($tag_id,self::mediaId('instagram'),$maxAge);
        
        if(count($fresh_instagrams) > 0){
            return self::instagramFeed($tag_id,$fresh_instagrams);
        }
        else{
            return 'none';
        }
        
    }
    
    //@param maxAge -> unix time for the most recent feed
    //@param tagId -> index for the tag
    //ajax call to get the information for fullsize slide show
    public static function getSlideShow($tag_id,$maxAge){
        
        $social_media = new self(Tags::model()->findByPk($tag_id)->name,35);//replenish the database
        $social_media->queryAPI();//query instagram API 
        
        $fresh_instagram = MediaDetails::model()->displaySlideShow($tag_id,self::mediaId('instagram'),$maxAge);
        if(count($fresh_instagram) > 0){
            $slideshow = self::slideShowFeed($tag_id,$fresh_instagram);
            if($slideshow != ''){ 
                return $slideshow;
            }
            else{
              return 'none';
            }
        }
        else{
            return 'none';
        }
    }
    
     
     //@param minAge -> unix time for the most oldest feed
    //@param tagId -> index for the tag
    public static function getOldInstagram($tag_id,$minAge){
        $old_instagram = MediaDetails::model()->displayByMediaIdOld($tag_id,self::mediaId('instagram'),$minAge);//returns a max of 10 slides
        if(count($old_instagram) > 0){
            return self::instagramFeed($tag_id,$old_instagram);
        }
        else{
            return '';
        }
    }
    
   
    //instagram social feed
    public static function instagramFeed($tag_id,$fresh_instagrams = null){
        
      
      $counter = 0;
      $models = (!isset($fresh_instagrams) ?  MediaDetails::model()->displayByMediaId($tag_id,self::mediaId('instagram')) : $fresh_instagrams );
      $str = '';
      
      $tag = Tags::model()->findByPk($tag_id)->name;
      
      foreach($models as $model){
            
          if(strlen(trim($model['username'])) == 0 || strlen(trim($model['unique_identifier'])) == 0)
               continue;
               
            $current_time = strtotime(date("Y-m-d H:i:s"));
            $time_posted = $model['date_created'];
            $time_diff = $current_time - $time_posted;
            $timeposted = self::computeDate($time_diff);
            //$onPhotoLoad = '';
            
            $isTagOccurred=false;
            $text = '';
            
            $text_array = self::specialText($model['text'],$tag);
            if(!is_array($text_array))
               continue;
            
            foreach($text_array as $k=>$v){
                $isTagOccurred=$k;
                $text = $v;
            }
            
            if(!$isTagOccurred)
               continue;
           
           $str .= '<div class="instagram_stream_container" id="'.$model['unique_identifier'].'">';
                
                $str .= '<div class="image_holder img-polaroid"><img src="'.$model['media_url'].'" id="img_'.$model['unique_identifier'].'" /></div>';
                
                $str .= '<div class="detail_holder">';
                  
                  $str .= '<div>';
                    $str .= '<div class="profile_photo_holder"><img src="'.$model['profile_image_url'].'"/></div>';
                    $str .= '<div class="username_holder">'.ucfirst($model['username']).'</div>';
                    $str .= '<div style="clear:both"></div>';
                  $str .= '</div>';
                  
                  $str .= ' <input type="hidden" class="ages" value="'.$model['date_created'].'"/><div class="tweet_holder">';
                    $str .= $text.' '.'<a href="'.$model['display_url'].'" target="_blank">'.$model['display_url'].'</a><br /><br />Posted: '.$timeposted;
                  $str .= '</div>';
                  
                $description = ''; 
                //$tagname = '';
                if(isset($model['text']) || strlen($model['text']) > 0){
                       $description = '&description='.urlencode($model['text']);
                       //$tagname = urlencode($model['text']);
                }
                  
                //start social likes buttons
                $str .= self::socialPlugIn($model['display_url'],$model['unique_identifier'],$model['media_url'],$description,$text);
                //end social likes button
                  
                $str .= '</div>';

                $str .= '<div style="clear:both"></div>';
            
            $str .= '</div>';
            
            $counter++;
            if($counter == 1 && isset($fresh_instagrams)){
               return $str.'||'.$model['unique_identifier'];  
            }
             
            
      }
      
      return $str;   
    }
    
    //returns the string to create the slide show
    public static function slideShowFeed($tag_id,$fresh_instagram){
      $str = '';
      $image_json = '';
      $age = '';
      $return_string = '';
      $tag = Tags::model()->findByPk($tag_id)->name;
      
      $counter = 0;
      
      foreach($fresh_instagram as $model){
           
            if(strlen($model['media_url']) > 4){//just check the media url if there is
            
                $current_time = strtotime(date("Y-m-d H:i:s"));
                $time_posted = $model['date_created'];
                $time_diff = $current_time - $time_posted;
                $timeposted = self::computeDate($time_diff);
                $text = '';
                
                foreach(self::specialText($model['text'],$tag) as $k=>$v){
                   $text = $v;
                }
                
                if($counter > 0){
                  $str .= '$';
                  $image_json .=  '$'; 
                  $age .= '$'; 
                }
                  
                $str .= '<li class="siteTitle">';
                   $str .= '<a href="'.Yii::app()->createUrl('/site/index').'">'.Yii::app()->name.'</a>';
                $str .= '</li>';
                $str .= '<li class="username">';
                   $str .= $model['username'];
                $str .= '</li>';
                $str .= '<li class="text">';
                   $str .= $text;
                $str .= '</li>';
                $str .= '<li>';
                   $str .= '<a href="'.$model['display_url'].'" target="_blank">'.$model['display_url'].'</a>';
                $str .= '</li>';
                $str .= '<li>';
                   $str .= 'Posted: '.$timeposted;
                $str .= '</li>';
	            
                $image_json .= '{image:"'.$model['media_url'].'"}';//form the string
                
                $age .= $model['date_created'];
                
                $counter++;
                
                
            }
            
       }
      
      if(strlen($image_json) > 4){
          $return_string = $image_json.'||'.$str.'||'.$age;  
      }
     
      return $return_string;
    }
    
    
    private static function socialPlugIn($url,$unique_identifier,$media_url,$description,$text){
        
        $buttonDiv = '<div><a href="#" class="popUpBtn btn btn-primary" onclick="bindClickFunction(\''.$unique_identifier.'\',\''.$media_url.'\',\''.$url.'\');return false;">Share It</a></div>';
        $startDiv = '<div class="modal hide fade" id="myModal_'.$unique_identifier.'">';
        $startDiv .= '<div class="modal-header">
                        <div class="title_holder"><b>Share it</b></div><div class="curvedarrow"></div><button type="button" class="close" data-dismiss="modal">x</button>
                      </div>';
        $imageDiv = '<div class="modal-body">';
            $str = '<div class="social_plugin_holder"><div class="googleplus_plugin_holder" id="googleplus_plugin_holder_'.$unique_identifier.'">
                    <g:plusone size = "medium" href="'.$url.'"></g:plusone></div>';
                    $str .= '<div class="facebook_plugin_holder" id="facebook_plugin_holder_'.$unique_identifier.'">
                             </div>'; 
                    $str .= '<div class="twitter_plugin_holder" id="twitter_plugin_holder_'.$unique_identifier.'">
                             <a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$url.'" data-via="'.Yii::app()->params['viaName'].'">Tweet</a>
                             </div>';
                    $str .=  '<div class="pinterest_plugin_holder" id="pinterest_plugin_holder_'.$unique_identifier.'">
                                  <iframe class="pin-it-button" scrolling="no" frameborder="0" style="border:none;width:90px;height:20px;" 
                                  src="http://pinit-cdn.pinterest.com/pinit.html?url='.urlencode($url).'&media='.urlencode($media_url).
                                  '&count-layout=horizontal'.$description.'&ref='.urlencode(self::createDocumentUri()).'" >
                                  </iframe>
                              </div>';
                    $str .= '<div style="clear:both"></div></div><div class="bigImageHolder"><img id="bigImage_'.$unique_identifier.'" src=""/></div></div>';//closing div tag
          $closeStartDiv = '</div>';
      
        $modalComponent = $buttonDiv.$startDiv.$imageDiv.$str.$closeStartDiv;
        return $modalComponent;
        
    }
    
     //return the url
    private static function createDocumentUri(){
      return 'http://'.Yii::app()->getRequest()->serverName.Yii::app()->createUrl("/site/index");    
   }

    //highlight words that starts with @ or # or url-like
    public static function specialText($text,$tag){
        
        $return_array = array();
        
        if(strlen(trim($text)) == 0){
            $return_array[false]=$text;
            return $return_array;
        }
        
        $strings = explode(' ',$text);
        
        $new_text = '';
        $occurrence_counter = 0;
        $counter = 0;
        $spacer = '';
        
        foreach($strings as $word) {
             if($counter > 0)
               $spacer = ' ';
             $lowerword = strtolower($word);
             $lowertag = strtolower($tag);
             if( $lowerword == $lowertag || $lowerword == '#'.$lowertag ||  $lowerword == '@'.$lowertag ){
                 $new_text .=  $spacer.'<span class="highlight">'.$word.'</span>';
                 $occurrence_counter++;
             }
             else{
                $new_text .= $spacer.$word; 
             }
             
             $counter++;
        }
       
        if($occurrence_counter > 0){
            $return_array[true]=$new_text;
        }else{
            $return_array[false]=$new_text;
        }
        
        return $return_array;
         
    }
    
   
    
    public static function isValidURL($url)
    {
      return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }
    
    public static function cleanUrl($url){
        if(!self::isValidURL($url))
        {
           $url = 'http://'.$url;
        }
        return $url;
    }
    
   
    /**
    *compute a date
    *@param $arg(bigint)- date in unix format(milliseconds)
    */
    private static function computeDate($arg)
    {
        $arg = $arg * 1000;
        $time = 0;
        $s = 's';
        
        if($arg < 0):
            return "less than a minute ago";
        elseif($arg >= 0 && $arg <= 60000):
            $time = round($arg/1000,0);
            if ($time == 1)
            {$s = '';}
            return (string)$time . " second".$s." ago";
        elseif ($arg>=60000 && $arg < 3600000):
            $time = round($arg/60000,0);
            if ($time == 1)
            {$s = '';}
            return (string)$time . " minute".$s." ago";
        elseif ($arg>=3600000 && $arg < 86400000):
            $time = round($arg/3600000,0);
            if ($time == 1)
            {$s = '';}
            return (string)$time . " hour".$s." ago";
        else:
            $time = round($arg/86400000,0);
            if ($time == 1)
            {$s = '';}
            if($time > 30){
              $months = round($time/30,0);
              if($months == 1)
              {$s = '';}
              return (string)$months . " month".$s." ago";  
            }
            else
             return (string)$time . " day".$s." ago";
        endif;
        
    } 
}

?>