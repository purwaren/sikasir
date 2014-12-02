<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Laporan Penggantian Barang</a></h2>
        <p class="description">Laporan penggantian barang untuk barang yang dinyatakan hilang pada saat pelaksanaan checking barang</p>
        <br />        
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>       
        <?php if(isset($row_data)) { ?>        
        <table class="table-data" cellspacing="0" cellpadding="0">
            <tr>
                <td class="head">No</td><td class="head">Tanggal Checking</td><td class="head"> Jenis Item </td><td class="head"> Total Item </td><td class="head">Action</td>
            </tr>
            <?php _e($row_data) ?>
        </table>
       <?php } ?>
</div>

<?php include 'layout/footer.php'; ?>