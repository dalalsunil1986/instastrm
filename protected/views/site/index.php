<?php
$this->pageTitle = 'Real-time streaming of Instagram photos by photo tags.';
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jquery.blockUI.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/scroll-startstop.events.jquery.js?y=5');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/script.js?y=6');
?>

<div class="hero-unit">

<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
	echo '<div class="alert alert-' . $key . '"><button type="button" class="close" data-dismiss="alert">x</button>' . $message . "</div>\n";
  }
  
  Yii::app()->clientScript->registerScript('exitscript',"
       if(isThereAnotherOpenBrowser('instastrmr')){
         window.location.href = '/stream/site/OverLimit'; 
       }
       $(window).on('beforeunload', function(){}).unload(function(){ deleteCookie('instastrmr'); });
     "
   );
?>

<?php if(strlen(trim($str)) == 0): ?>
   <br />
<?php endif; ?>

<?php if(strlen(trim($tag)) == 0): ?>
   <br />
<?php endif; ?>

<div class="top_tags">
  <?php if(strlen(trim($str)) > 5): ?>
    <div class="controller_holder"><a href="#" class="btn btn-danger" id="streamController">Pause</a><div id="loader"></div><div style="clear:both"></div></div>
  <?php endif; ?>
	<div class="title_holder"><b>Select Popular Tags</b></div><div class="curvedarrow"></div><br />
	<?php echo $this->topTenTags(); ?>
</div>
 <hr>
<div class="row">
	<div class="content-box" id="content-box">
	  <?php if(strlen(trim($str)) > 5): ?>
	    <div id="top_holder"></div>
	    <?php echo $str; ?>
	  <?php else: ?>
	   <h3>No results found.</h3>
	  <?php endif; ?>
	</div>
	<div style="display:none;" class="nav_up" id="nav_up"></div>
	<div style="display:none;" class="nav_down" id="nav_down"></div>
   </div>
   

    <div class="ajaxUrl" style="display:none"><?php echo $this->createUrl('/site/InstagramRefresh'); ?></div>
    <div class="ajaxDownUrl" style="display:none"><?php echo $this->createUrl('/site/OldInstagram'); ?></div>
    <input type="hidden" id="tagId" value="<?php echo $tag_id; ?>" />
    <input type="hidden" id="tagName" value="<?php echo $tag; ?>" />
</div>