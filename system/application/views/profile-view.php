<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Profil Pengguna</a></h2>
        <p class="description">Data diri pengguna sistem</p>     
        <br />
        <?php if(isset($karyawan) && isset($pengguna)) { ?>        
        <table cellspacing="3" cellpadding="2">
            <tr>
                <td colspan="2"><i>Informasi Pengguna</i></td>
            </tr>
            <tr>
                <td>Username</td><td>: <?php _e($pengguna->username) ?></td>
            </tr>
            <tr>
                <td>Password <br /><br /></td><td>: <?php _e('* * * * * *') ?><br /><br /></td>
            </tr>
            <tr>
                <td colspan="2"><i>Informasi Karyawan</i></td>
            </tr>
            <tr>
                <td>N I K</td><td>: <?php _e($karyawan->NIK) ?></td>
            </tr>
            <tr>
                <td>Nama Karyawan</td><td>: <?php _e($karyawan->nama) ?></td>
            </tr>
            <tr>
                <td>Jabatan</td><td>: <?php _e(ucwords($pengguna->jabatan)) ?></td>
            </tr>
            <tr>
                <td>Divisi</td><td>: <?php _e($karyawan->divisi) ?></td>
            </tr>
            <tr>
                <td>Alamat</td><td>: <?php _e($karyawan->alamat) ?></td>
            </tr>
            <tr>
                <td>Telepon</td><td>: <?php _e($karyawan->telepon) ?><br /><br /></td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php _e(form_open('profile/ubah')) ?>
                    <input type="hidden" name="nik" value="<?php _e($pengguna->NIK) ?>" />                    
                    <span class="button"><input type="submit" class="button" name="submit_ubah" value="Ubah"/></span>
                    <?php _e(form_close()) ?>
                </td>
            </tr>
        </table>        
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>