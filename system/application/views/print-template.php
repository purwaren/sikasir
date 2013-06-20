<html>
<head>
<title>Printing Documents</title>
<link rel="stylesheet" href="<?php _e(base_url()) ?>css/print.css" type="text/css" media="print"/>
<link rel="stylesheet" href="<?php _e(base_url()) ?>css/print.css" type="text/css" media="screen"/>
</head>
<body onload="window.print()">
<div id="container">
    <!--<div id="header">
        <img src="<?php _e(base_url()) ?>css/images/logo_mode.png" />   
        <h2>MODE FASHION GROUP</h2>
        <p>
            Kantor Pusat: <br />
            Jln. Laksana No. 68 ABC, Medan<br />            
            Telepon: (061) 372 592
        </p>                  
    </div>
    <div id="content">-->
        <?php echo isset($content)?$content:'' ?>
    <!--
    </div>-->
</div>
</body>
</html>