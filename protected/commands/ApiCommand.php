<?php

/* 
** runs every 30secs to send query Twitter and Instagram
*
*/

class ApiCommand extends CConsoleCommand
{
    
    
    public function run($args)
    {
       $this->startQuery();
    }
    
    public function startQuery(){
      
      $counter = 0;
      
      for(;;){
        
        $tags = Tags::model()->allTags();
        //$tags = array('#haiku','cool','@fly');
        if(count($tags) == 0){
            echo 'No tags....'."\n";
            sleep(2);
            continue;
        }
        
        $social_media = new SocialMedia($tags,60); 
        $social_media->queryAPI();
        $counter++;
        echo $counter.' successful querying API.'."\n";
       
        sleep(20);
       }
    }
}
?>