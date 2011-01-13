<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Absensi Datang</a></h2>
        <p class="description">Absensi kehadiran pegawai sebagai data acuan untuk penyusunan laporan prestasi kerja karyawan</p>     
        <br />  
        Isikan NIK anda pada form berikut.  
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td>NIK</td><td class="head"> : <input type="text" id="id_karyawan" maxlength="10" style="width:80px"> </td>
            </tr>                              
        </table>           
        <p id="err_msg" style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>
        <br/>            
        <table class="table-data" cellspacing="0" cellpadding="0" style="display:none">
            <tr><td class="head">No</td><td class="head">NIK</td><td class="head">Nama Karyawan</td><td class="head">Status</td></tr>
        </table>
        <p id="button-simpan" style="display:none;">
            <span class="button"><input type="button" value="simpan" class="button" onclick="confirmPresence()"/></span>
        </p>
        <div id="dialog-confirm-absensi" title="Konfirmasi" style="display:none">
            <p>Simpan sekarang ?</p>
        </div> 
        <div id="dialog-msg" title="Notifikasi" style="display:none"><p></p></div>        
    </div>    
</div>
<?php include 'layout/footer.php'; ?>