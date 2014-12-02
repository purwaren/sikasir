<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
           <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Manajemen Pengguna</a></h2>
        <p class="description">Manajemen data pengguna sistem, membuat pengguna baru, edit pengguna dan hapus pengguna</p>
        <?php if(isset($row_data)) { ?>
        <table class="table-data" cellspacing="0" cellpadding="0">
            <tr>
                <td class="head"> NIK</td><td class="head"> Nama Karyawan </td><td class="head"> Jabatan </td><td class="head"> Status </td><td class="head">Action</td>
            </tr>
            <?php _e($row_data) ?>
        </table>
        <?php } ?>
        <div id="dialog-confirm" title="Konfirmasi" style="display:none">
            <p><span id="msg"></span> <span id="nama" style="font-weight:bold"></span> ?</p>
        </div>
        <div id="dialog-msg" title="Message" style="display:none">
            <span id="err_msg"></span>
        </div>
        <p style="text-align:center;color:green;" id="success_msg"></p>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>