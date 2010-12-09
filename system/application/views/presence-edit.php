<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Edit Absensi Karyawan</a></h2>
        <p class="description">Untuk mengubah absensi karyawan, hanya supervisor yang boleh melakukan</p>     
        <br />
        <?php if(isset($detail)) { ?>
        <?php _e(form_open('presence/edit'))?>
        <table cellspacing="3" cellpadding="2">
            <tr>
                <td>N I K</td><td>: <?php _e($detail->NIK) ?><input type="hidden" name="nik" value="<?php _e($detail->NIK) ?>"/></td>
            </tr>
            <tr>
                <td>Nama Karyawan</td><td>: <?php _e($detail->nama) ?></td>
            </tr>           
            <tr>
                <td>Tanggal</td><td>: <?php _e($detail->tanggal) ?> <input type="hidden" name="tanggal" value="<?php _e($detail->tanggal) ?>"/></td>
            </tr>
            <tr>
                <td>Status Absensi</td>
                <td>: 
                    <select name="status">
                        <option value="1" <?php if($detail->status == 'masuk') _e('selected="selected"') ?>>Masuk</option>
                        <option value="2" <?php if($detail->status == 'izin') _e('selected="selected"') ?>>Sakit/Izin</option>
                        <option value="3" <?php if($detail->status == 'alpha') _e('selected="selected"') ?>>Alpha</option>
                        <option value="4" <?php if($detail->status == 'libur/off') _e('selected="selected"') ?>>Libur/Off</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><span class="button"><input type="submit" name="submit_edit_absensi" value="Simpan" class="button"/></span></td>
            </tr>
        </table>
        <p><?php if(isset($msg)) _e($msg) ?></p>
        <?php _e(form_close()) ?>
        <?php } else { ?>
        <p style="color:red"><?php _e($err_msg)?></p>
        <?php } ?>        
    </div>
        
</div>
<?php include 'layout/footer.php'; ?>