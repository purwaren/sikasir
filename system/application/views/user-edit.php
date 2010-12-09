<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Add New Users</a></h2>
        <p class="description">This was design to create new user for sikasir.</p>  <br />
        <?php if(isset($pengguna)) { ?>
        <?php _e(form_open(base_url().'user/edit/'.$pengguna->NIK)) ?>
        <h3>Informasi Akun Pengguna</h3>
        <table cellspacing="0" cellpadding="2">
            <tr>
                <td>Username</td><td class="head"> : <input type="text" name="username" value="<?php _e($pengguna->username) ?>"/></td>
            </tr>
            <tr>
                <td>Password</td><td class="head"> : <input type="password" name="passwd" readonly="yes" value="******"/> *)Gunakan menu ganti password untuk mengubah password</td>
            </tr>            
        </table>
        <h3>Data Pengguna / Karyawan</h3>
        <table cellspacing="0" cellpadding="2">
            <tr>
                <td>N I K</td><td class="head"> : <input type="text" name="nik" maxlength="10" readonly="yes" value="<?php _e($pengguna->NIK) ?>"/></td>
            </tr>
            <tr>
                <td>Nama</td><td class="head"> : <input type="text" name="nama" value="<?php _e($pengguna->nama) ?>" /></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td class="head"> : 
                    <select name="jabatan" style="width: 157px;">
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
                <td>No. Telp.</td><td class="head"> : <input type="text" name="telp" value="<?php _e($pengguna->telepon) ?>"/></td>
            </tr>
            <tr>
                <td style="text-align:right" colspan="2"><span class="button"><input type="submit" name="submit_user_edit" value="Simpan" class="button"/></span></td>
            </tr>             
        </table>       
        <?php if(isset($err_msg)) _e($err_msg) ?>
        <?php _e(form_close()) ?>
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>