<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Laporan Penjualan Toko </a></h2>
        <p class="description">Laporan penjualan barang disajikan dalam bentuk laporan harian dan bulanan, masing - masing dengan pilihan per kode barang ataupun per kode kelompok barang</p>
        <br />
        <?php _e(form_open(base_url().'report/sales'))?>
        <table id="report">
            <tr><td>Pilih jenis laporan </td><td>: 
            <select name="report-type" id="report-type" onchange="showMonth()" onclick="showMonth()">
                <option value="">--Pilih--</option>
                <option value="1">Laporan Penjualan Harian</option><!-- terurut waktu -->                
                <option value="2">Laporan Akumulasi Penjualan Harian</option><!--per kode barang -->                      
                <option value="3">Laporan Akumulasi Penjualan Bulanan</option><!--per kode kel barang -->
                                
            </select>
            <span class="button">&nbsp;<input type="submit" name="submit_report_sales" value="Preview" class="button"/></span>
            <span class="button">&nbsp;<input type="submit" name="submit_report_sales_pdf" value="Cetak" class="button"/></span>
            </td></tr> 
            <tr id="harian">
                <td>Tanggal</td><td> : <input type="text" name="date-report" id="date-report" readonly="readonly"/></td>
            </tr>
            <tr id="bulanan" style="display:none">
                <td>Bulan</td><td> : <?php _e($month.'--'.$year)?></td>
            </tr>
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php 
            if(isset($_POST['submit_report_sales'])) 
            {        
                _e($report_sales);
            }
        ?>
</div>

<?php include 'layout/footer.php'; ?>