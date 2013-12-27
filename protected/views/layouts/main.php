<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta property="og:title" content="Instastrm" />
	<meta property="fb:app_id" content="120311904787444" />
	<meta property="og:type" content="website" />
	<meta name="description" content="Stream photos in real-time from Instagram.">
	<meta name="author" content="photos,images,pictures,instagram,tags">
	
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->
	<link href="http://fonts.googleapis.com/css?family=Finger+Paint" rel="stylesheet" />
	<link href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" rel="stylesheet" />
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
	<!-- Le fav and touch icons -->
	<!--<link rel="shortcut icon" href="<?php //echo Yii::app()->request->baseUrl; ?>/images/ico/favicon.ico" />-->
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo Yii::app()->request->baseUrl; ?>/images/ico/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo Yii::app()->request->baseUrl; ?>/images/ico/apple-touch-icon-114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo Yii::app()->request->baseUrl; ?>/images/ico/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="<?php echo Yii::app()->request->baseUrl; ?>/images/ico/apple-touch-icon-57-precomposed.png">
	
	<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
	
	<!--twitter script-->
	<script>
		!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);
		js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
	</script>      
	<!--twitter script-->
	
	<!--google plus one script-->
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
	 {parsetags: 'explicit'}
	</script>
	<!--google plus one script-->
</head>

<body>
   
  
   
   <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?php echo $this->createUrl('/site') ?>"><?php echo Yii::app()->name; ?></a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a title="View photos in full screen slide show." id="fullSize" href="#">Supersize</a></li>
	      <li><a href="#myModal" data-toggle="modal">About</a></li>
            </ul>
	   
	    <ul style="float:right" class="nav">
		<!--search box-->
		    <li>
			    <form class="navbar-search pull-right" method="post" action="<?php echo $this->createUrl('/site/index'); ?>">
			       <input type="text" class="search-query span3" placeholder="Enter a photo tag then hit enter........" name="tag" />
              </form>
		    </li>
		<!--search box-->
	    </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="container" id="page">
        <?php echo $content; ?>
	<hr>
	<footer class="container">
     <div class="footer-holder">
         <div id="social-content" class="clearfix">
              <div class="heading">connect with us</div>
              <div class="container-widgets"> 
                <a href="http://www.facebook.com/pages/Instastrm-Community/472751912765781" class="widget" target="_blank"><img src="<?php echo Yii::app()->request->baseUrl; ?>/images/widget-fb.png"></a> 
                <a href="http://twitter.com/#!/instastrm" class="widget" target="_blank"><img src="<?php echo Yii::app()->request->baseUrl; ?>/images/widget-tw.png"></a> 
                <a href="https://plus.google.com/u/0/112563655525656129487/posts" class="widget" target="_blank"><img src="<?php echo Yii::app()->request->baseUrl; ?>/images/widget-gp.png"></a>
              </div>
              <div class="likes">
                 <fb:like href="http://instantstrm.com" send="true" width="450" show_faces="true" font="arial"></fb:like>
              </div>
        </div>
	<div class="site_info">
         Built and coded by: <a href="http://www.linkedin.com/pub/joel-capillo/26/8b4/ba3" target="_blank">Joel Capillo</a><br/>
        Powered by: <a href="http://Instagram.com" target="_blank">Instagram API</a>
       </div>
        <div style="clear:both"></div>
      </div>  
	</footer><!-- footer -->
    </div>
    
   <?php if(strlen(trim($this->tag)) > 0 && strlen(trim($this->str)) > 5): ?>
     <div class="current_tag_holder" id="tagHolder">
        <div class="title_holder" id="titleholder">Streaming tag...</div>
        <div class="currentTag">
            <div class="curvedarrow"></div>
            <div class="span_holder">
                <span class="label label-warning">
                   <?php echo $this->tag; ?>
                </span>
            </div>
            
        </div>
    </div>
  <?php endif; ?>

	<!--pop-up modal-->    
	<div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none">
	  <div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
	    <h3 id="myModalLabel">About</h3>
	  </div>
	  <div class="modal-body">
	    <p><b><?php echo Yii::app()->name; ?></b> is a web application where you can stream real-time feeds from <a href="http://instagram.com" target="_blank">Instagram</a> by photo tags. Instastrm will create
	    a real-time photo stream for you and will continuously query Instagram server for latest images that matches the tag you entered or selected.</p>
	  </div>
	</div>
	<!--pop-up modal-->
	
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.min.js"></script>
	   
	<!--Pinterest script-->
	<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
	<!--Pinterest script-->
	
	<!--facebook script-->
	<!--recommended by Facebook Dev to put after body tag opening-->
	<div id="fb-root"></div>
	<script type="text/javascript">
	/*<![CDATA[*/
	window.fbAsyncInit = function(){FB.init({'appId':'120311904787444','status':true,'cookie':true,'xfbml':true,'oauth':true,'frictionlessRequests':false});asyncCallback();};
	(function(d){
	var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
	js = d.createElement('script'); js.id = id; js.async = true;
	js.src = '//connect.facebook.net/en_US/all.js';
	d.getElementsByTagName('head')[0].appendChild(js);
	}(document));
	function asyncCallback() {}
	/*]]>*/
	</script>
	<!--facebook script-->
	
	<!--Google Analytics-->
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-17403473-4']);
		_gaq.push(['_trackPageview']);
		
		(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
	<!--Google Analytics-->
   </body>
</html>

