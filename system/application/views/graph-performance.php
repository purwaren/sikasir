<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Grafik Prestasi Karyawan </a></h2>
        <p class="description">Grafik prestasi karyawan disajikan per bulan operasional</p>
        <br />
        <?php _e(form_open(base_url().'graph/performance'))?>
        <table id="report">
           <tr id="bulanan">
                <td>Bulan</td><td> : <?php _e($month)._e('--')._e($year)?> </td><td>            
                <span class="button">&nbsp;<input type="submit" name="submit_graph_performance" value="Display" class="button"/></span>            
            </td></tr>             
            <tr>
                <td>Karyawan</td><td> : <?php _e($karyawan) ?></td>
            </tr>
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        
        <?php if(isset($nik) && $nik == 'all' && isset($table)) { ?>
            <h3 style="text-align:center">BULAN : <?php _e($bulan.' '.$tahun)?>
            <br />
            DALAM RIBUAN RUPIAH (Rp 1.000,-)</h3>
            <p style="text-align: center"><img src="<?php _e(base_url())?>css/chart/performance-all.png" alt="Grafik Omset" style="border:none" /></p>
            <h3 style="text-align:center">TABEL OMSET <?php _e($bulan)?> <br /> (Rupiah)</h3>        
            <div style="width: 100%; overflow:auto;">
            <?php if(isset($table)) _e($table) ?>
            </div>
        <?php } if(isset($nik) && $nik != 'all' && isset($row_data)) { ?>
        <h3 style="text-align:center">BULAN : <?php _e($bulan.' '.$tahun)?><br /> 
        <?php if(isset($pramuniaga))  ?>NAMA KARYAWAN : <?php _e(strtoupper($pramuniaga->nama)) ?><br /><br />
        DALAM RIBUAN RUPIAH (Rp 1.000,-)</h3>
        <p style="text-align: center"><img src="<?php _e(base_url())?>css/chart/performance.png" alt="Grafik Omset"/></p>
        <h3 style="text-align:center">TABEL OMSET <?php _e($bulan)?></h3>
        <table class="table-data" cellspacing="0" cellpadding="0" style="width:300px; text-align:center">
            <tr>
                <td class="head">No</td>
                <td class="head">Tanggal</td>
                <td class="head">Omset (Rp)</td>
                <td class="head">Total Item</td>
                <td class="head">Total Customer</td>
            </tr>
            <?php if(isset($row_data)) _e($row_data) ?>
        </table>
        <?php } ?>
</div>

<?php include 'layout/footer.php'; ?>