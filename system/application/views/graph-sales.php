<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Grafik Penjualan Toko </a></h2>
        <p class="description">Grafik penjualan toko harian dan bulanan</p>
        <br />
        <?php _e(form_open(base_url().'graph/sales'))?>
        <table id="report">
           <tr id="bulanan">
                <td>Bulan</td><td> : <?php _e($month)._e('--')._e($year)?> </td><td>            
            <span class="button">&nbsp;<input type="submit" name="submit_graph_sales" value="Display" class="button"/></span>            
            </td></tr>             
            
            </tr>
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($row_data)) { ?>
        <h3 style="text-align:center">GRAFIK OMSET <?php _e($bulan)?><br /> DALAM RIBUAN RUPIAH (Rp 1.000,-)</h3>
        <p style="text-align: center"><img src="<?php _e(base_url())?>css/chart/sales.png" alt="Grafik Omset"/></p>
        <h3 style="text-align:center">TABEL OMSET <?php _e($bulan)?></h3>
        <table class="table-data" cellspacing="0" cellpadding="0" style="width:300px; text-align:center">
            <tr>
                <td class="head">No</td>
                <td class="head">Tanggal</td>
                <td class="head">Qty Terjual</td>
                <td class="head">Total Diskon(Rp)</td>
                <td class="head">Omset (Rp)</td>
            </tr>
            <?php if(isset($row_data)) _e($row_data) ?>
        </table>        
        <?php } ?>
</div>

<?php include 'layout/footer.php'; ?>