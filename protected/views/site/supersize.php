<?php
 $this->pageTitle = 'Real-time slide show of Instagram photos by photo tags.';
?>

<ul id="demo-block">
</ul>


<!--Thumbnail Navigation-->
    <div id="prevthumb"></div>
    <div id="nextthumb"></div>
    
    <!--Arrow Navigation-->
    <a id="prevslide" class="load-item"></a>
    <a id="nextslide" class="load-item"></a>
    
    <div id="thumb-tray" class="load-item">
        <div id="thumb-back"></div>
        <div id="thumb-forward"></div>
    </div>
    
    <!--Time Bar-->
    <div id="progress-back" class="load-item">
        <div id="progress-bar"></div>
    </div>
    
    <!--Control Bar-->
    <div id="controls-wrapper" class="load-item">
        <div id="controls">
            
            <a id="play-button"><img id="pauseplay" src="<?php echo Yii::app()->request->baseUrl; ?>/images/pause.png"/></a>
        
            <!--Slide counter-->
            <div id="slidecounter">
                <span class="slidenumber"></span> / <span class="totalslides"></span>
            </div>
            
            <!--Slide captions displayed here-->
            <div id="slidecaption"></div>
            
            <!--Thumb Tray button-->
            <a id="tray-button"><img id="tray-arrow" src="<?php echo Yii::app()->request->baseUrl; ?>/images/button-tray-up.png"/></a>
           
          
        </div>
    </div>

<!--hidden info elements-->
<div class="ajaxSlideShowUrl" style="display:none"><?php echo $this->createUrl('/site/SlideInstagram'); ?></div>
<input type="hidden" id="tagId" value="<?php echo $tag_id; ?>" />
<input type="hidden" id="tagName" value="<?php echo $tag; ?>" />
<input type="hidden" id="Age" value="<?php echo $age; ?>" />