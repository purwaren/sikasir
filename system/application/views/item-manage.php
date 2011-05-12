<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>        
        <h2><a href="#">Manage Items</a></h2>
        <p class="description">This is designed for items management and will be renewed periodically</p>        
        <?php _e(form_open(base_url().'item/manage'))?>
        <br />
        <table>
            <tr>
                <td>Tanggal Penginputan</td>
                <td>: <input type="text" name="date_input" id="date-input" readonly="readonly"/>            
                <span class="button">&nbsp;<input type="submit" name="submit_item_manage" value="Display" class="button"/></span>            
            </td></tr>          
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($row_data)) { ?>
        <h3>Data Masuk Tanggal : <?php echo date_to_string($tgl_input) ?></h3>
        <table class="table-data" cellspacing="0" cellpadding="0" style="width:950px">
            <tr>
                <td class="head"> No </td><td class="head"> Kode BON </td><td class="head"> Kode Barang </td><td class="head"> Nama Barang </td>
                <td class="head"> Kelompok Barang </td><td class="head">Harga Barang</td><td class="head">Mutasi Masuk</td>
                <td class="head"> Stok Barang</td><td class="head">Mutasi Keluar</td>
                <td class="head">Action</td>
            </tr>
            <?php _e($row_data) ?>            
        </table>  
        <?php if(isset($pagination)) _e($pagination) ?>
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>