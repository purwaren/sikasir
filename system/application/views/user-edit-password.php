<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Edit User's Password</a></h2>
        <p class="description">Editing password user</p>  <br />
        <?php if(isset($pengguna)) { ?>
        <?php _e(form_open('user/editpasswd/'.$pengguna->NIK)) ?>
        <h3>Informasi Akun Pengguna</h3>
        <table cellspacing="0" cellpadding="2">
            <tr>
                <td>Username</td><td class="head"> : <input type="text" name="username" readonly="readonly" value="<?php _e($pengguna->username) ?>"/><input type="hidden" name="nik" value="<?php $pengguna->userid?>"/></td>
            </tr>            
            <tr>
                <td>Password Baru</td><td class="head"> : <input type="password" name="new_passwd" value=""/></td>
            </tr>
            <tr>
                <td>Konfirmasi Password</td><td class="head"> : <input type="password" name="new_passwd_confirm"  value=""/></td>
            </tr>
            <tr>
                <td style="text-align:right" colspan="2"><span class="button"><input type="submit" name="submit_user_editpasswd" value="Simpan" class="button"/></span></td>
            </tr>             
        </table>
        <?php if(isset($err_msg)) _e($err_msg) ?>
        <?php _e(form_close()) ?>
        <?php } ?>
        
    </div>    
</div>
<?php include 'layout/footer.php'; ?>