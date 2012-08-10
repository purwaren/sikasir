<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Ekspor Data</a></h2>
        <p class="description">Untuk mengekspor data penjualan, digunakan ketika finalisasi proses checking</p>
        <br />
        <?php _e(form_open('checking/export'))?>
        <table id="report">
            <tr>
                <td>Pilihan</td>
                <td>: <select name="tabel" style="width:155px">
                    <option value="1">Transaksi Penjualan</option>                    
                </select></td>
            </tr>
            <tr>
                <td>Tanggal Awal</td><td> : <input type="text" name="tgl_awal" class="date_sales" style="width: 150px" readonly="readonly"/>
                </td>
            </tr>
            <tr>
                <td>Tanggal Akhir</td><td> : <input type="text" name="tgl_akhir"  class="date_sales" style="width: 150px" readonly="readonly" />
                <span class="button"> <input type="submit" value="Export" name="submit_export" class="button" /></span></td>
            </tr>            
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>   
</div>

<?php include 'layout/footer.php'; ?>