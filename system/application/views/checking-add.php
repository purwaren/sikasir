<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Input Opname</a></h2>
        <p class="description">Prosedur awal pelaksanaan checking barang, memeriksa ketersediaan barang</p>
        <br />
        <?php _e(form_open(base_url().'checking/add'))?>
        <table id="report">            
            <tr id="harian">
                <td>Kode Barang </td><td> : <input type="text" name="id_barang"  style="width: 100px" maxlength="10"/></td>
                <td><span class="button"> <input type="submit" value="G O" name="submit_search_opname" class="button" /></span></td>
            </tr>            
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($search_result)) { ?>
        <script type="text/javascript">
        <!--//
        $(document).ready(function(){
            $('#stok_opname').focus();
        });
        //-->
       </script>
        <?php _e(form_open(base_url().'checking/add'))?>
        <table class="table-data" cellspacing="0" cellpadding="0" style="margin:10px">
            <tr>
                <td class="head">Kode Barang</td><td class="head">Nama Barang</td><td class="head"> Stok Barang </td><td class="head">Stok Opname </td><td class="head">Beda Stok</td>
            </tr>           
            <?php _e($search_result) ?>            
        </table>
        <p style="text-align:center;"><span class="button"> <input type="submit" value="Simpan" name="submit_save_opname" class="button" /></span></p>
        <?php _e(form_close()) ?>
        <?php } else { ?>
        <script type="text/javascript">
        <!--//
        $(document).ready(function(){
            $('input[name="id_barang"]').focus();
        });
        //-->
       </script>
        <?php } ?>
</div>

<?php include 'layout/footer.php'; ?>