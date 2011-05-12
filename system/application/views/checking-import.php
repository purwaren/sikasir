<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Import Data</a></h2>
        <p class="description">Untuk mengimport penjualan dari mesin yang lama, pada saat checking</p>
        <br />
        <?php _e(form_open_multipart(base_url().'checking/import'))?>
        <table id="report">
            <tr>
                <td>Pilihan</td>
                <td>: <select name="tabel" style="width:155px">
                    <option value="1">Transaksi Penjualan</option>                    
                </select></td>
            </tr>           
            <tr>
                <td>Pilih File</td><td> : <input type="file" name="csv_file"  style="width: 150px" />
                <span class="button"> <input type="submit" value="Import" name="submit_import" class="button" /></span></td>
            </tr>            
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($row_data)) { ?>
        <form>        
        <table class="table-data" cellspacing="0" cellpadding="0" style="width:950px;">
            <tr>
                <td class="head">No</td>
                <td class="head">Tanggal</td>
                <td class="head">ID Transaksi</td>
                <td class="head">Kode Barang</td>
                <td class="head">Nama Barang</td>
                <td class="head">Qty</td>
                <td class="head">No CC</td>
                <td class="head">Diskon <br /> Item</td>
                <td class="head">Diskon <br /> All</td>
                <td class="head">Kasir</td>
                <td class="head">Pramuniaga</td>
                <td class="head">Jumlah</td>
                <td class="head">Action</td>
            </tr>
            <?php echo $row_data ?>            
        </table>
        </form>
        <?php } ?>
</div>

<?php include 'layout/footer.php'; ?>