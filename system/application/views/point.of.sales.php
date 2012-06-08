<?php include('pos/header.php') ?>
<!--dialog untuk notifikasi -->
<div id="dialog-message" title="Notifikasi" style="display:none">        
</div>
<div id="dialog-msg" title="Notifikasi" style="display:none">
    <span class="ui-icon ui-icon-alert" style="float:left; margin:2px 7px 50px 0;"></span>
    <span style="color:red;"></span>
</div>
<!--dialog untuk konfirmasi -->
<div id="dialog-confirm-exit" title="Konfirmasi" style="display:none">
    Anda yakin ingin keluar ?
</div>
<!--dialog untuk prompt transaksi ke-n yang akan dibatalkan-->
<div id="dialog-prompt-trans"title="Batalkan transaksi ?" style="display:none">
    Nomor : <input type="text" id="trans-nth" style="width:50px;font-size:2em;"/><br />
    <span id="err-msg" style="color:red"></span>
</div>
<!--dialog untuk prompt jumlah bayar pembayaran -->
<div id="dialog-prompt-cash" title="Pembayaran Tunai" style="display:none">
    <input type="text" id="trans-cash" style="width:100px;" onkeyup="viewCash(1)"/>
    <span id="err-msg-cash" style="color:red"></span><br />
    <p style="padding:0;margin:0;text-align:right;font-size:3em" id="cash-view"></p>
</div>
<!--dialog untuk prompt no kartu kredit dan namanya-->
<div id="dialog-prompt-credit" title="No Kartu Kredit?" style="display:none">
    <select id="cc_bank" style="width:140px;font-size:2em;">
        <option value="1">BCA</option>
        <option value="2">BNI</option>
        <option value="3">BRI</option>
        <option value="4">Danamon</option>
        <option value="5">Mandiri</option>
    </select><br />
    <input type="text" id="cc_num_1" style="width:60px;font-size:2em;" maxlength="4" onkeyup="moveCursor(1)"/>-
    <input type="text" id="cc_num_2" style="width:60px;font-size:2em;" maxlength="4" onkeyup="moveCursor(2)"/>-
    <input type="text" id="cc_num_3" style="width:60px;font-size:2em;" maxlength="4" onkeyup="moveCursor(3)"/>-
    <input type="text" id="cc_num_4" style="width:60px;font-size:2em;" maxlength="4" onkeyup="moveCursor(4)"/>
    <br />
    <span id="err-msg-credit" style="color:red"></span>
</div>
<!--dialog untuk prompt jumlah bayar refund -->
<div id="dialog-prompt-refund" title="Pembayaran Tukar Barang" style="display:none">
    <input type="text" id="refund-cash" style="width:100px" onkeyup="viewCash(2)"/><br />
    <span id="err-msg-refund" style="color:red"></span>
    <p style="padding:0;margin:0;text-align:right;font-size:3em" id="refund-view"></p>
</div>
<!--dialog jumlah kembalian-->
<div id="dialog-cashback" title="Jumlah Kembalian" style="display:none;text-align:right">
    <span id="cashback" style="font-size: 2em;text-align:right;"></span><br />
    <input type="text" style="font-size:2em;text-align:right;width:120px;" id="infaq"/>
</div>
<!--dialog jumlah total penjualan -->
<div id="dialog-sales" title="Total Sales">
    <span id="sales" style="font-size: 2em"></span>
</div>
<!--dialog search barang -->
<div id="dialog-search" title="Cari Barang" style="display:none">
    Keyword: <input type="text" id="key" onfocus="setFocus()" onblur="removeFocus()"/><input type="button" value="GO" onclick="searchItem()"/> <span id="err-key" style="color:red"></span>
    <hr style="border:1px solid"/>
    <table id="search-result" cellspacing="0" cellpadding="0"></table>
</div>
<!--dialog refund -->
<div id="dialog-refund" title="Tukar Barang (Refund)" style="display:none">    
    Barang ditukar &nbsp;: <input type="text" id="barang-tukar" onfocus="setFocus()" onblur="removeFocus()"/> 
    <input type="button" value="GO" onclick="getItem(1)"/><span id="err-tukar" style="color:red"></span>    
    <hr style="border:1px solid"/>
    <table id="detail-tukar"></table><br />
    Ditukar dengan : <input type="text" id="barang-pengganti" onfocus="setFocus()" onblur="removeFocus()"/> 
    <input type="button" value="GO" onclick="getItem(2)"/><span id="err-pengganti" style="color:red"></span>
    <hr style="border:1px solid"/>
    <table id="detail-pengganti"></table>
    <table style="border:none">
        <tr>
            <td style="width:85px">Diskon</td><td> : <select name="disc" id="disc_refund" style="width:138px;" onchange="countRefund();countPengganti();"><option value="0">Tidak</option><option value="10">10%</option></select> </td>
        </tr>
        <tr>
            <td>Pramuniaga</td><td> : <input type="text" id="ref_pramu" /><input type="hidden" id="refpramu_list" value="" /></td>
        </tr>
    </table>
    <br /><span id="err-refund" style="color:red"></span>
    <p id="kurang-bayar" style="font-size:2em; color:red;text-align:right;width: 720px; display:none"></p>
    <input type="hidden" id="total_tukar" /><input type="hidden" id="total_pengganti" />
    <button id="button-refund" style="display:none" onclick="trigerButtonRefund()">REFUND</button>
</div>
<!--dialog untuk window utama POS -->
<div id="dialog-form" title="Point of Sales :. Transaction Ready ">
<div id="container">
    <div id="identifier">
        <div style="display:inline-block;width:90px;"></div>
        <div id="toko-identifier">
            <p style="text-align:left;font-weight:bold;"><?php _e(config_item('shop_name')) ?><br /><?php _e(config_item('shop_address')) ?><br /> Telp. <?php _e(config_item('shop_phone')) ?></p>
        </div>
        <div id="spacer"></div>
        <div id="kassa-identifier">
            <p style="text-align:center; font-weight: bold; text-transform:uppercase;"><span id="trigger"></span> No. KASSA : <?php _e($this->session->userdata('no_kassa')) ?> <br /> Tanggal : <span id="digiDate"><?php _e($current_date) ?> </span><br /> Waktu : <span id="digiClock"> </span></p>
        </div>
    </div>        
    <p style=";margin:3px 0 3px 0;">No. Tunggu : <input type="text" id="no_tunggu" readonly="readonly" value="<?php _e($no_tunggu)?>"/></p>
    <!-- <applet codebase="<?php _e(base_url().'js') ?>" code="EchoClient.class" id="appletPrinter" height="0" width="20"></applet> -->
    <table cellspacing="0" cellpadding="0" class="data">
        <tr class="head">
            <td width="5%">NO</td>
            <td width="15%">KODE LABEL</td>
            <td width="25%">NAMA BARANG</td>
            <td width="15%">HARGA SATUAN (Rp)</td>
            <td width="10%">DISKON (%)</td>
            <td width="10%">QTY</td>
            <td width="20%">JUMLAH (Rp)</td>
        </tr>
    </table>		
    <div class="table-container">
    <table cellspacing="0" cellpadding="0" class="data" id="row-data">
    </table>
    </div><br />
    <div id="input-scanner">
    <table>
        <tr>        
            <td class="pramu">PRAMUNIAGA </td><td>: <input type="text" id="id_pramu" /><input type="hidden" id="pramu_list" value=""/></td>
            <td rowspan="2" colspan="2" class="disc_total">
                <span >Diskon All Item (%): <input id="disc_all" type="text" style="width:40px" value="0" onkeyup="countAllWithDisc()" /></span>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Rp. <input type="text" id="total" name="total" value ="0" readonly="readonly"/><input type="hidden" id="total_val" value="0"/></td>
        </tr>
        <tr>
            <td style="width: 150px; text-align: right;">KODE LABEL </td><td>: <input type="text" name="barcode" id="barcode" maxlength="10"/></td>				
        </tr>
        <tr>
            <td class="kasir" colspan="2">
            KASIR : <?php _e(strtoupper($userinfo)) ?>
            </td>
            <td style="text-align:center;font-size: 0.7em;">&copy; 2010 Mode Fashion Group</td>
            <td style="text-align: right">
                <img src="<?php _e(base_url()) ?>css/images/creditcard_cirrus.png" alt="cirrus" title="Credit Card Cirrus" />
                <img src="<?php _e(base_url()) ?>css/images/creditcard_mastercard.png" alt="cirrus" title="Credit Card Mastercard" />
            </td>	
        </tr>
    </table>    
    </div>
    </div>
</div>
<?php include 'pos/footer.php' ?>