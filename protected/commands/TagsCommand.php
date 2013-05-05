<?php

/* 
** runs 1 minute to check if tags are being used
*
*/

class TagsCommand extends CConsoleCommand
{
    
    
    public function run($args)
    {
      $this->startCheck();
    }
    
    public function startCheck(){
      for(;;){
        
        $tags = Tags::model()->allTags();
        if(count($tags) == 0){
            echo 'No tags....'."\n";
            sleep(2);
            continue;
        }
        Tags::model()->deleteUnusedTags();//delete unused tags and related media details
        sleep(30);
        
      }
    }
}
?>