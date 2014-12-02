<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>
        
        <h2><a href="#">Rekap Infaq</a></h2>
        <p class="description">Laporan pengumpulan dana infaq</p>
        <br />
        <?php _e(form_open('report/infaq'))?>
        <table id="report">            
            <tr id="harian">               
                <td>Bulan</td><td> : <?php _e($month.'--'.$year)?></td>
            	<td><span class="button"> <input type="submit" value="Preview" name="submit_preview" class="button" /></span>
            		<span class="button"> <input type="submit" value="Print" name="submit_print" class="button" /></span>
            	</td>
            </tr>            
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?>
        <?php if(isset($result)) echo $result; ?>
</div>

<?php include 'layout/footer.php'; ?>