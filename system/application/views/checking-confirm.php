<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Konfirmasi Penggantian Barang</a></h2>
        <p class="description">Konfirmasi untuk penggantian barang, apabila sudah tetap maka supervisor tinggal menyetujui untuk dibuatkan laporannya</p>
        <br />        
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p> 
        <p style="text-align:center"><?php if(!empty($page))echo 'Page : '.$pages ?></p>
        <?php if(isset($search_result)) { ?>
        <?php _e(form_open(base_url().'checking/confirm'))?>                   
            <?php _e($search_result) ?>     
        <br />
        <div style="display:none" id="dialog-confirm-checking" Title="Konfirmasi Checking Barang">
            Silahkan melakukan otorisasi, <br />
            <table>
                <tr><td>Username</td><td><input type="text" id="username" /></td></tr>
                <tr><td>Password</td><td><input type="password" id="passwd"/></td></tr>
            </table>            
        </div>
        <div style="display:none" id="dialog-msg" title="Notifikasi">
            <p id="msg"></p>
        </div>
        <p style="text-align:center;color:#000;margin-bottom:10px"><?php if(!empty($page))echo 'Page : '.$pages ?></p>
        <p style="text-align:center;">        
        <span class="button"><input type="button" value="Konfirm" class="button" onclick="confirmChecking()" /></span>&nbsp;&nbsp;
        <!--<span class="button"><input type="submit" value="Cetak" name="submit_cetak_ganti" class="button" /></span>&nbsp;&nbsp;-->
        <span class="button"><input type="button" value="Batal"  class="button" onclick="batalChecking()" /></span>
        </p>
        <?php _e(form_close()) ?>
        <?php } ?>
</div>

<?php include 'layout/footer.php'; ?>