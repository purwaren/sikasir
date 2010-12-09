<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Lihat Opname</a></h2>
        <p class="description">Untuk melihat stok opname, data dengan stok barang tidak nol saja yang ditampilkan</p>
        <br />
        <?php _e(form_open(base_url().'checking/manage'))?>
        <table id="report">            
            <tr id="harian">
                <td>Kelompok Barang </td><td> : <input type="text" name="kel_barang"  style="width: 100px" maxlength="3"/></td>
                <td><span class="button"> <input type="submit" value="G O" name="submit_view_opname" class="button" /></span></td>
            </tr>            
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($search_result)) { ?>
        <?php _e(form_open(base_url().'checking/manage'))?>
        <table class="table-data" cellspacing="0" cellpadding="0">
            <tr>
                <td class="head">No</td><td class="head">Kode Barang</td><td class="head">Nama Barang</td>
                <td class="head">Mutasi Masuk </td><td class="head">Mutasi Keluar</td><td class="head"> Stok Awal </td><td class="head">Stok Barang</td>
                <td class="head">Stok Opname</td><td class="head">Beda Stok</td><td class="head">Harga Jual</td>
            </tr>           
            <?php _e($search_result) ?>            
        </table><br />
        <span class="button">
            <input type="submit" value="Cetak PDF" name="submit_cetak_opname" class="button" />
            <input type="button" value="Print" onclick="printOpname('<?php _e(base_url().'checking/manage/'.$kel_barang) ?>')" class="button" />
        </span>
        <?php _e(form_close()) ?>
        <?php } ?>
</div>

<?php include 'layout/footer.php'; ?>