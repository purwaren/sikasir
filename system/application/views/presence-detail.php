<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Detail Kehadiran Karyawan</a></h2>
        <p class="description">Data kehadiran karyawan yang lebih lengkap</p>     
        <br />
        <?php if(isset($detail)) { ?>
        <table cellspacing="3" cellpadding="2">
            <tr>
                <td>N I K</td><td>: <?php _e($detail->NIK) ?></td>
            </tr>
            <tr>
                <td>Nama Karyawan</td><td>: <?php _e($detail->nama) ?></td>
            </tr>
            <tr>
                <td>Alamat</td><td>: <?php _e($detail->alamat) ?></td>
            </tr>
            <tr>
                <td>Telepon</td><td>: <?php _e($detail->telepon) ?></td>
            </tr>
            <tr>
                <td>Tanggal</td><td>: <?php _e($detail->tanggal) ?></td>
            </tr>
            <tr>
                <td>Status Absensi</td><td>: <?php _e($detail->status) ?></td>
            </tr>            
        </table>
        <?php } else { ?>
        <p style="color:red"><?php _e($err_msg)?></p>
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>