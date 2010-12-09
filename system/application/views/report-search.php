<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>        
        <h2><a href="#">Search Sales</a></h2>
        <p class="description">This is designed for searching sales on a spesific day</p>        
        <?php _e(form_open(base_url().'report/search'))?>
        <br />
        <table>
            <tr>
                <td>Tanggal</td>                
                <td> :<input type="text" name="date-report" id="date-report" readonly="readonly"/>                           
                </td>
            </tr>   
            <tr>
                <td>Berdasarkan</td><td> :  
                    <select name="based_on" style="width:157px;" onfocus="displayForm()" onkeyup="displayForm()" onchange="displayForm()" id="based_on">
                        <option value="">--Pilih--</option>
                        <option value="1">Kelompok Barang</option>
                        <option value="2">Kode Barang</option>
                    </select>
                </td>
            </tr>
            <tr id="kb" style="display:none">
                <td>Kelompok Barang</td><td> :  
                    <input type="text" name="kb_low" style="width:50px;" maxlength="2"/> s.d. <input type="text" name="kb_high" style="width:50px" maxlength="2"/>
                </td>
            </tr>
            <tr id="kl" style="display:none">
                <td>Kode Barang</td><td> :  
                    <input type="text" name="ib_low" style="width:75px;" maxlength="10"/> s.d. <input type="text" name="ib_high" style="width:75px" maxlength="10"/>
                </td>
            </tr>
            <tr>
                <td colspan="2"><span class="button">&nbsp;<input type="submit" name="submit_search" value="Display" class="button"/></span> </td>
            </tr>
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?> 
        <?php if(isset($_POST['submit_search'])) { ?>
        <table class="table-data" cellspacing="0" cellpadding="0">
            <tr>
                <td class="head"> No </td><td class="head"> Kelompok Barang </td> <td class="head"> Kode Barang </td><td class="head"> Nama Barang </td>
                <td class="head">Harga Barang<br /> (Rupiah)</td><td class="head">Jumlah Terjual</td>                
            </tr>
            <?php _e($result) ?>
        </table>
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>