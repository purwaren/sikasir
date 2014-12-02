<?php include 'layout/header.php'; ?>
<div id="tabs">
    <ul>
        <li><a  href="" accesskey="m">(*)##(*)</a></li> 
        <li><a  href="" accesskey="m">--------</a></li> 
        <li><a class="current" href="" accesskey="m">Please</a></li> 
        <li><a class="current" href="" accesskey="m">Log In</a></li> 
        <li><a  href="" accesskey="m">--------</a></li> 
        <li><a  href="" accesskey="m">(*)##(*)</a></li>                
    </ul>    
  </div>
    <br />  
  <div id="middle">
    <div class="boxtop"></div>    
    <div class="box">
        <p><img src="<?php _e(base_url())?>css/images/padlock.jpg" alt="Image" title="Image" class="image" id="icon-login" /></p>
        <?php _e(form_open('home/login')) ?>
            <label> Username : </label><input type="text" name="username"/> <br />
            <label> Password : </label><input type="password" name="passwd"/>     
            <p style="margin: 10px 0 0 0px; padding-left: 111px;"><input type="submit" name="submit_login" value="Log In" class="button-submit"><input type="button" value="Reset" class="button-nosubmit"/>
        <?php _e(form_close()) ?>
    </div> 
    <p style="color:red;text-align:center;"><?php if(isset($login_error)) _e($login_error) ?></p>    
  </div>
<?php include 'layout/footer.php'; ?>