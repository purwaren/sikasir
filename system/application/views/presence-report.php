<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Rekap Absensi </a></h2>
        <p class="description">Rekapitulasi absensi karyawan per hari / per bulan</p>
        <br />
        <?php _e(form_open('presence/report'))?>
        <table id="report">
            <tr>
                <td>Pilihan</td><td>: 
                <select name="opsi" id="opsi" style="width:170px;">
                    <option value="1">Rekapitulasi Kehadiran</option>
                    <option value="2">Rekapitulasi Jam Kerja</option>                    
                </select></td>
            </tr>
            <tr id="bulanan">
                <td>Bulan</td><td> : <?php _e($month)._e('--')._e($year)?> </td><td>            
            <span class="button">&nbsp;<input type="submit" name="submit_presence_report" value="Display" class="button"/></span>            
            <span class="button">&nbsp;<input type="submit" name="submit_print_report" value="Cetak" class="button"/></span>            
            </td></tr>             
            
            </tr>
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($report)) _e($report) ?>
</div>

<?php include 'layout/footer.php'; ?>