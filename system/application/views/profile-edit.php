<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Edit Profile</a></h2>
        <p class="description">This was design to edit profile.</p>  <br />
        <?php if(isset($pengguna)) { ?>
        <?php _e(form_open(base_url().'profile/ubah')) ?>        
        <table cellspacing="0" cellpadding="2">
            <tr>
            <td colspan="2"><h3>Informasi Akun Pengguna</h3></td>
            </tr>
            <tr>
                <td>Username</td><td class="head"> : <input type="text" name="username" readonly="username" value="<?php _e($pengguna->username) ?>"/></td>
            </tr>
            <tr>
                <td>Password Lama</td><td class="head"> : <input type="password" name="passwd" /> <i>*) Biarkan kosong jika tidak ingin mengganti password</i></td>
            </tr>
            <tr>
                <td>Password Baru</td><td class="head"> : <input type="password" name="new_passwd" /></td>
            </tr>
            <tr>
                <td>Konfirmasi Password</td><td class="head"> : <input type="password" name="new_passwd_confirm" /></td>
            </tr>
            <tr>
            <td colspan="2"><h3>Data Pengguna / Karyawan</h3></td>
            </tr>
            <tr>
                <td>N I K</td><td class="head"> : <input type="text" name="nik" maxlength="10" readonly="readonly" value="<?php _e($pengguna->NIK) ?>"/></td>
            </tr>
            <tr>
                <td>Nama</td><td class="head"> : <input type="text" name="nama" value="<?php _e($pengguna->nama) ?>" /></td>
            </tr>
            <tr style="display:none">
                <td>Jabatan</td>
                <td class="head"> : 
                    <select name="jabatan" style="width: 157px;" readonly="readonly">
                        <option value="1" <?php if($pengguna->jabatan == 'admin') _e('selected="selected"') ?>>Admin</option>
                        <option value="2" <?php if($pengguna->jabatan == 'supervisor') _e('selected="selected"') ?>>Supervisor</option>
                        <option value="3" <?php if($pengguna->jabatan == 'kasir') _e('selected="selected"') ?>>Kasir</option>
                        <option value="4" <?php if($pengguna->jabatan == 'pramuniaga') _e('selected="selected"') ?>>Pramuniaga</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Alamat</td><td class="head"> : <input type="text" name="alamat" value="<?php _e($pengguna->alamat) ?>"/></td>
            </tr>
            <tr>
                <td>No. Telp.</td><td class="head"> : <input type="text" name="telepon" value="<?php _e($pengguna->telepon) ?>"/></td>
            </tr>
            <tr>
                <td style="text-align:left" colspan="2"><br /><span class="button"><input type="submit" name="submit_ubah_profile" value="Simpan" class="button"/></span></td>
            </tr>             
        </table>       
        <p><?php if(isset($err_msg)) _e($err_msg) ?></p>
        <?php _e(form_close()) ?>
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>