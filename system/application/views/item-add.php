<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Input New Items </a></h2>
        <p class="description">This is designed for manualy input items data, per bon</p>        
        <br /><form class="form-input" method="post" action="<?php _e(base_url().'item/add') ?>" style="padding-left: 50px">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td>Kode BON </td><td class="head"> : <input type="text" name="id_bon" maxlength="10"> </td>
            </tr>
            <tr>
                <td>Tanggal </td><td class="head"> : <input type="text" name="date_bon" id="date_bon" readonly="readonly"/> </td>
            </tr>                    
        </table><br/>
        <?php if(isset($err_msg)) _e($err_msg) ?>
        <table cellspacing="0" cellpadding="0" class="table-data" style="width: 850px; margin: 0px;">
            <tr><td class="head">No</td><td class="head">Kode Barang</td><td class="head">Nama Barang</td><td class="head">Kelompok<br /> Barang</td><td class="head">Harga Barang</td><td class="head">Qty</td><td class="head">Diskon</td><td class="head">Jumlah</td></tr>
            <?php 
            for($i=0; $i<15;)
            echo '<tr class="row-data">
                <td style="width: 20px;">'.++$i.'</td>
                <td style="width: 80px;"><input type="text" name="id_barang[]" maxlength="10" class="item_code" onkeyup="setFocus('.$i.')" style="width:80px;"/></td>
                <td style="width: 150px;"><input type="text" name="nama[]" style="width:150px;" id="nama_'.$i.'"/></td>
                <td style="width: 30px;"><input type="text" name="kel_barang[]" style="width:30px;" id="kel_barang_'.$i.'"/></td>
                <td style="width: 120px;"><input type="text" name="harga[]" id="harga_'.$i.'" style="width:120px;" onkeyup="hitungJumlah('.$i.')" /></td>
                <td style="width: 30px;"><input type="text" name="qty[]" id="qty_'.$i.'" style="width:30px;" onkeyup="hitungJumlah('.$i.')" /></td>
                <td style="width: 50px;"><input type="text" name="disc[]" id="disc_'.$i.'" style="width:30px;" onkeyup="hitungJumlah('.$i.')" /></td>
                <td style="width: 120px;"><span id="jumlah_'.$i.'" ></span><input type="hidden" id="jml_'.$i.'" /></td>
            </tr>';
            ?>
            <tr><td colspan="5" style="text-align:right;height: 30px;">TOTAL</td><td><span id="total_qty"></span></td><td></td><td><span id="total_jumlah"></span></td></tr>
        </table>
        <br />
        <span class="button">&nbsp;<input type="submit" name="submit_mutasi_masuk" value="Simpan" class="button"/></span>
        <span class="button">&nbsp;<input type="button" class="button" onclick="appendRowMutasi()" value="Lagi"/></span><br />        
        </form>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>