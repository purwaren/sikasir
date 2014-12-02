<?php include 'layout/header.php'; ?>
<?php include 'layout/menu.php'; ?>
<div class="left">
    <div class="left_articles">        
        <div class="calendar">
            <p><?php _e($now) ?></p>
        </div>        
        <h2><a href="#">Check Data</a></h2>
        <p class="description">This tools was design to check the consistent of data</p>        
        <?php _e(form_open('tools/check'))?>
        <br />
        <table>              
            <tr>
                <td>Berdasarkan</td><td> :  
                    <select name="based_on" style="width:157px;" onfocus="displayForm()" onkeyup="displayForm()" onchange="displayForm()" id="based_on">
                        <option value="">--Pilih--</option>
                        <!--<option value="1">Kelompok Barang</option>-->
                        <option value="2">Kode Barang</option>
                    </select>
                </td>
            </tr>
            <tr id="kb" style="display:none">
                <td>Kelompok Barang</td><td> :  
                    <input type="text" name="kb" style="width:150px;" maxlength="2"/>
                </td>
            </tr>
            <tr id="kl" style="display:none">
                <td>Kode Barang</td><td> :  
                    <input type="text" name="ib" style="width:150px;" maxlength="10"/>
                </td>
            </tr>
            <tr>
                <td colspan="2"><span class="button">&nbsp;<input type="submit" name="submit_search" value="Display" class="button"/></span> </td>
            </tr>
        </table>
        <p style="color:red"><?php if(isset($err_msg)) _e($err_msg) ?></p>        
        <?php _e(form_close()) ?> 
        <?php if(isset($_POST['submit_search'])) { ?>
        <div style="width:940px;overflow:auto;"><?php if(isset($check_result)) _e($check_result) ?></div>
        <?php } ?>
    </div>    
</div>
<?php include 'layout/footer.php'; ?>