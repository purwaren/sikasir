<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
         <p><?php _e($now) ?></p>
        </div>        
        <h2><a href="#">Edit Items Data </a></h2>
        <p class="description">This is designed for editing items data</p>        
        <form class="form-input" method="post" action="<?php _e(base_url().'item/edit/') ?>" style="padding-left: 50px">
        <input type="hidden" name="id_mutasi_masuk" value="<?php if(isset($barang)) _e($barang->id_mutasi_masuk) ?>" />
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td>Kode Barang </td><td class="head"> : <input type="text" name="id_barang" maxlength="10" value="<?php if(isset($barang)) _e($barang->id_barang) ?>"> <input type="hidden" value="<?php if(isset($barang)) _e($barang->id_barang) ?>" name="id_barang_old" /></td>
            </tr>
            <tr>
                <td>Nama Barang </td><td> : <input type="text" name="nama" value="<?php if(isset($barang)) _e($barang->nama) ?>"></td>
            </tr> 
            <tr>
                <td>Harga Jual </td><td> : <input type="text" name="harga_jual" value="<?php if(isset($barang)) _e($barang->harga) ?>"></td>
            </tr>             
            <tr>
                <td>Quantity </td><td> : <input type="text" name="qty" value="<?php if(isset($barang)) _e($barang->qty) ?>"> <input type="hidden" value="<?php if(isset($barang)) _e($barang->qty) ?>" name="qty_old" /></td>
            </tr> 
            <tr>
                <td>Kelompok Barang </td><td> : <input type="text" name="kel_barang" value="<?php if(isset($barang)) _e($barang->kelompok_barang) ?>"></td>
            </tr> 
            <tr>
                <td>Diskon </td><td> : <input type="text" name="diskon" value="<?php if(isset($barang)) _e($barang->diskon) ?>"></td>
            </tr>
            <tr>
                <td style="text-align:right" colspan="2"><span class="button"><input type="submit" name="submit_item_edit" value="Simpan" class="button"/></span></td>
            </tr>             
        </table>
        <?php if(isset($err_msg)) _e($err_msg) ?>
        </form>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>