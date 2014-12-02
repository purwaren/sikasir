<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">View User's Detail</a></h2>
        <p class="description">Viewing datas detail for users.</p>  <br /> 
        <?php if(isset($pengguna)) { ?>
        <h3>Informasi Akun Pengguna</h3>
        <table cellspacing="3" cellpadding="3">
            <tr>
                <td>Username</td><td class="head"> : <?php _e($pengguna->username) ?></td>
            </tr>
            <tr>
                <td>Password</td><td class="head"> : (tidak ditampilkan untuk alasan keamanan)</td>
            </tr>            
        </table>
        <h3>Data Pengguna / Karyawan</h3>
        <table cellspacing="3" cellpadding="3">
            <tr>
                <td>N I K</td><td class="head"> : <?php _e($pengguna->NIK) ?></td>
            </tr>
            <tr>
                <td>Nama</td><td class="head"> : <?php _e($pengguna->nama) ?></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td class="head"> : <?php _e($pengguna->jabatan) ?> </td>
            </tr>
            <tr>
                <td>Alamat</td><td class="head"> : <?php _e($pengguna->alamat) ?></td>
            </tr>
            <tr>
                <td>No. Telp.</td><td class="head"> : <?php _e($pengguna->telepon) ?></td>
            </tr>
            <tr><td colspan="2"><span class="button"><a href="<?php _e(base_url().'user/manage')?>" ><input type="button" value="Kembali" class="button"/></a></span></td></tr>
        </table>
        <?php } else { ?>
            <p style="color:red"><?php _e($err_msg) ?></p>
        <?php } ?>        
    </div>    
</div>
<?php include 'layout/footer.php'; ?>