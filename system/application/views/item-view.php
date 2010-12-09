<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>        
        <h2><a href="#">Laporan Stok Barang</a></h2>
        <p class="description">Laporan stok barang per kode kelompok barang dan per kode barang</p>        
        <?php _e(form_open(base_url().'report/stok'))?>
        <br />
        <table>
            <tr>
                <td>Pilihan</td>
                <td>: 
                <select name="opsi">
                    <option value="1">Tampilkan Per Kelompok Barang</option>
                    <option value="2">Tampilkan Per Kode Barang</option>                    
                </select>
                <span class="button">&nbsp;<input type="submit" name="submit_item_view" value="Display" class="button"/></span>            
            </td></tr>          
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($_POST['submit_item_view'])) { ?> 
        <?php if($_POST['opsi'] == 1) { ?>        
        <p>Catatan : <br />
            1. Stok Barang = Jumlah barang yang terdapat di komputer. <br />
            2. Jumlah Terjual = Jumlah total barang yang telah berhasil terjual. <br />
            3. Total Stok = Jumlah total barang yang tercatat ditoko baik yang sudah terjual atau belum.<br />
        </p>
        <table class="table-data" cellspacing="0" cellpadding="0">
            <tr>
                <td class="head"> No </td><td class="head"> Kelompok Barang </td><td class="head"> Total Barang </td>
                <td class="head"> Mutasi Masuk </td><td class="head"> Jumlah Terjual </td><td class="head">Stok Barang </td>              
            </tr>
            <?php _e($row_data) ?>            
        </table>
        <?php } if($_POST['opsi'] == 2) { ?>
        Opsi dua
        <?php }} ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>