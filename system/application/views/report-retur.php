<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Laporan Retur Barang</a></h2>
        <p class="description">Laporan retur barang / mutasi keluar disajikan per bon pada hari tertentu</p>
        <br />
        <?php _e(form_open(base_url().'report/retur'))?>
        <table id="report">            
            <tr id="harian">
                <td>Tanggal</td><td> : <input type="text" name="date-report" id="date-report" style="width: 100px" readonly="readonly"/></td>
                <td><span class="button"> <input type="submit" value="Cari" name="submit_search_retur" class="button" /></span></td>
            </tr>            
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($search_result)) { ?>
        <table class="table-data" cellspacing="0" cellpadding="0">
            <tr>
                <td class="head">No</td><td class="head"> Kode BON </td><td class="head"> Jenis Item </td><td class="head"> Total Item </td><td class="head">Action</td>
            </tr>
            <?php _e($search_result) ?>
        </table>
        <?php } ?>
</div>

<?php include 'layout/footer.php'; ?>