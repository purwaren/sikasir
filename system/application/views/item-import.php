<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Import Data</a></h2>
        <p class="description">Import data CSV yang dibawa dari gudang.</p>
        <br />
        <?php _e(form_open_multipart('item/import'))?>
        <table>            
            <tr>
                <td>Pilih Data</td><td> : <input type="file" name="csv_file" /></td> 
                <td><span class="button"><input class="button" type="submit" name="submit_import" value="Import"/></span></td>
            </tr>            
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>
        <br />
        <?php _e(form_close()) ?>
        <?php if(!isset($err_msg) && isset($row_data)) { ?>
        <form>
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td> Kode BON &nbsp;</td><td class="head"> : <input type="text" id="kode_bon" maxlength="10"> </td>
            </tr>
            <tr>
                <td> Tanggal </td><td class="head"> : <input type="text" name="date_bon" id="date_bon" readonly="readonly"/> </td>
            </tr>                    
        </table>
        <br />
        <table class="table-data" cellspacing="0" cellpadding="0" style="width:950px;">
            <tr>
                <td class="head">No</td>
                <td class="head">Kode Barang</td>
                <td class="head">Nama Barang</td>
                <td class="head">Kelompok Barang</td>
                <td class="head">Disc %</td>
                <td class="head">Harga Jual (Rp)</td>
                <td class="head">Quantity</td>
                <td class="head">Jumlah</td>
                <td class="head">Action</td>
            </tr>
            <?php echo $row_data ?>            
        </table>
        </form>
        <!--<p style="text-align:center"><span class="button"><input type="button" class="button" value="Simpan" onclick="saveAllImport()"/></span></p>-->
        <?php } ?>
        <div id="dialog-msg" title="Notifikasi" style="display:none"><p></p></div>
</div>

<?php include 'layout/footer.php'; ?>