<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>        
        <h2><a href="#">Search Items</a></h2>
        <p class="description">This is designed for items searching</p>        
        <?php _e(form_open(base_url().'item/cari'))?>
        <br />
        <table>
            <tr>
                <td>Keywords</td>
                <td>: <input type="text" name="keywords" />            
                <span class="button">&nbsp;<input type="submit" name="submit_cari" value="Cari" class="button"/></span>            
            </td></tr>          
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($row_data)) { ?>
        <table>
            <tr>
                <td>Total Barang</td><td>: <?php echo $total_item ?> macam</td>
            </tr>
            <tr>
                <td>Total Jumlah</td><td>: <?php echo $total_qty ?> item</td>
            </tr>
            <tr><td colspan="2"><?php if(!empty($pagination)) _e('Page : '.$pagination) ?></td></tr>
        </table>
        <table class="table-data" cellspacing="0" cellpadding="0" style="width:950px">
            <tr>
                <td class="head"> No </td><td class="head"> Kode Barang </td><td class="head"> Nama Barang </td><td class="head">Harga Barang</td>
                <td class="head">Disc %</td><td class="head">Total Barang</td><td class="head">Mutasi Masuk</td><td class="head">Mutasi Keluar</td><td class="head">Stok</td>               
            </tr>
            <?php _e($row_data) ?>            
        </table>  
        <?php if(!empty($pagination)) _e('Page : '.$pagination) ?>
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>