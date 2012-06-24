<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $site_name ?></title>
    
    <!-- Theme -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>public/css/theme-01/style.css" />
    
    <!--[if lte IE 7]>
      <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>public/css/theme-01/ie.css" />
    <![endif]-->
    
    <!-- jquery UI css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>public/js/jquery-ui-1.8.20/css/redmond/jquery-ui-1.8.20.custom.css" />    
</head>

<body>	
    <noscript><div>Your browser must support javascript.</div></noscript>	
    
	<?php echo $this->template->message(); ?>
    
    <?php echo $this->template->yield(); ?>
    
    <!-- jQuery and jQuery UI -->
    <script type="text/javascript" src="<?php echo base_url() ?>public/js/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>public/js/jquery-ui-1.8.20/jquery-ui-1.8.20.min.js"></script>

    <!-- scrolling plugin -->
	<script type="text/javascript" src="<?php echo base_url() ?>public/js/jquery.scrollTo-min.js"></script>
    
    <!-- scrolling stop plugin -->
	<script type="text/javascript" src="<?php echo base_url() ?>public/js/jquery.scrollstop.js"></script>
    
    <!-- images lazy loading plugin -->
	<script type="text/javascript" src="<?php echo base_url() ?>public/js/jquery.lazyload.min.js"></script>
    
    <!-- application script -->
    <script type="text/javascript" src="<?php echo base_url() ?>public/js/script.js"></script>
    
    <input type="hidden" name="url-pdf" id="url-pdf"value="<?php echo base_url() ?>pdf">
    <input type="hidden" name="app-profile" value="render:{elapsed_time} seconds, mem: {memory_usage}">
</body>
</html>
