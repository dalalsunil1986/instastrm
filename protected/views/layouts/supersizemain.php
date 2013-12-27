<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        
        <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/supersized.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/supersized.shutter.css?y=4" type="text/css" media="screen" />
        <link href="http://fonts.googleapis.com/css?family=Finger+Paint" rel="stylesheet" />
	
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.easing.min.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/supersized.3.2.7.min.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/supersize.js?y=11"></script>
    </head>
    
    <body>
        <?php echo $content; ?>
    </body>
</html>