<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Launch POS</a></h2>
        <p class="description">Before launching POS Application, cashire must fill some information</p>
        <p>Please fill the form information bellow </p>
        <form action="<?php _e(base_url().'pointofsales/index')?>" method="post">
            <table>
                <tr><td>No Shift </td><td>: <input type="text" name="no_shift" /> *) Shift number : 1.Morning, 2. Evening</td></tr>
                <tr><td>No Kassa </td><td>: <input type="text" name="no_kassa" /> *) Kassa number (terminal pos number)</td></tr>
                <tr><td style="text-align:right"><span class="button"><input type="submit" name="submit_launch" value="Launch" class="button" /></span></td></tr> 
            </table>        
        <?php if(isset($notification)) _e($notification) ?>
        </form>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>