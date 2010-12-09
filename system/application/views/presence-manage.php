<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Data Kehadiran</a></h2>
        <p class="description">Melihat data kehadiran karyawan</p>     
        <br /> 
        <?php _e(form_open('presence/manage'))?>
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td>Tanggal</td><td class="head"> : <input type="text" name="date_absensi" id="date_absensi" style="width:100px"> <span class="button"><input type="submit" name="submit_absensi" value="Display" class="button"/></span></td>
            </tr>                              
        </table> 
        <?php _e(form_close()) ?>
        <p id="err_msg" style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>
        <br/>            
        <?php _e($result) ?>
        <p style="text-align:center"><span id="msg" style="color:green"></span></p>
        <div id="dialog-confirm-absensi" title="Konfirmasi" style="display:none">
            <p>Hapus data absensi dari <span id="nama" style="font-weight:bold"></span> ?</p>
        </div> 
    </div>    
</div>
<?php include 'layout/footer.php'; ?>