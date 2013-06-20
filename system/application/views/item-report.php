<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>        
        <h2><a href="#">Report</a></h2>
        <p class="description">This is designed for stock management</p>
        <?php _e(form_open('item/report'))?>
        <br />
        <table>
            <tr>
                <td>Jenis Laporan</td>
                <td>: <select name="type" style="width: 180px" id="report_type">
                        <option>--Pilih--</option>
                        <option value="1">Laporan Data Barang</option>
                        <option value="2">Laporan Umur Barang</option>
                </select></td>
            </tr>
            <tr id="periode">
                <td>Periode</td>
                <td>: <input type="text" name="date_from" class="date" readonly="readonly" style="width: 75px"/> s.d.
                    <input type="text" name="date_to" class="date" readonly="readonly" style="width: 75px"/>
            </td></tr>
            <tr id="umur" style="display:none">
                <td>Umur (tahun)</td>
                <td>: <select name="umur">
                        <option>--Pilih--</option>
                        <option value="1"> &lt; 1 </option>
                        <option value="2">1 s.d. 2 </option>
                        <option value="3">2 s.d. 3 </option>
                        <option value="4"> &gt; 3 </option>
                </select></td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="button">&nbsp;<input type="submit" name="submit_report_display" value="Display" class="button"/></span>
                    <span class="button">&nbsp;<input type="submit" name="submit_report_print" value="Print" class="button"/></span>
                </td>
            </tr>
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($table)) {
            echo $table;
            echo '<a href="'.base_url().'item/report/preview" target="_new"><span class="button">&nbsp;<input type="button" value="Cetak" class="button"/></span></a>';
        }?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>