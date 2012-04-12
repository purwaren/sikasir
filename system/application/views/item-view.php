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
                <select name="opsi" id="opsi_search">
                    <option value="1">Kelompok Barang</option>
                    <option value="2">Kode Barang</option>                    
                </select>
                <span class="button">&nbsp;<input type="submit" name="submit_item_view" value="Display" class="button"/></span>            
            </td></tr>
            <tr id="row_id_barang" style="display:none">
            	<td>Kode Label</td>
            	<td>: <input type="text" name="id_barang" style="width:130px"/></td>
            </tr>          
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>        
        <?php if($this->input->post('opsi') == 1) { ?>        
        <p>Catatan : <br />
            1. Stok Barang = Jumlah barang yang terdapat di komputer. <br />
            2. Jumlah Terjual = Jumlah total barang yang telah berhasil terjual. <br />
            3. Total Barang = Jumlah total barang yang tercatat ditoko baik yang sudah terjual atau belum.<br />
            4. Mutasi Masuk = Jumlah barang yang masuk toko selama satu periode checking barang.<br />
            5. Mutasi Keluar = Jumlah barang yang terjual oleh toko selama satu periode checking barang.<br />
        </p>
        <table class="table-data" cellspacing="0" cellpadding="0" style="width:100%">
            <tr>
                <td class="head"> No </td><td class="head"> Kelompok Barang </td><td class="head"> Total Barang </td><td class="head"> Jumlah Terjual </td>
                <td class="head"> Mutasi Masuk </td><td class="head">Mutasi Keluar</td><td class="head">Stok Barang </td>              
            </tr>
            <?php _e($row_data) ?>            
        </table>
        <?php } else if($this->input->post('opsi') == '2' || $this->session->userdata('opsi') == '2'){ ?>
        <table>
            <tr><td>Total Barang</td><td>: <?php echo $total_item ?> macam</td></tr>
            <tr><td>Total Jumlah</td><td>: <?php echo $total_qty ?> items</td></tr>
            <tr><td colspan="2"><?php if(isset($pages)) echo 'Page : '.$pages ?> </td></tr>
        </table>
        <table class="table-data" cellspacing="0" cellpadding="0" style="width:100%">
            <tr>
                <td class="head"> No </td><td class="head"> Kode Barang </td><td class="head"> Nama Barang </td><td class="head"> Harga (Rp) </td><td class="head">Disc (%) </td><td class="head"> Total Barang </td>
                <td class="head">Stok Barang </td> <td class="head"> Mutasi Masuk </td><td class="head">Mutasi Keluar</td>           
            </tr>
            <?php _e($row_data) ?>            
        </table>
        <p><?php if(isset($pages)) echo 'Page : '.$pages ?> </p>
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>