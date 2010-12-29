<div id="tabs">
    <ul>
      <li><a class="<?php if($page=='presence') echo 'current' ?>" href="<?php _e(base_url().'presence/check') ?>" accesskey="m"><span class="key">P</span>resence</a></li>
      <?php if($jabatan=='admin') {?>
      <li><a class="<?php if($page=='user') echo 'current' ?>" href="<?php _e(base_url().'user') ?>" accesskey="m"><span class="key">U</span>ser</a></li>
      <li><a class="<?php if($page=='tools') echo 'current' ?>" href="<?php _e(base_url().'tools') ?>" accesskey="m"><span class="key">T</span>ools</a></li>
      <?php } if($jabatan=='kasir') { ?>
      <li><a class="<?php if($page=='pos') echo 'current' ?>" href="<?php _e(base_url().'pointofsales') ?>" accesskey="v"><span class="key">L</span>aunch POS</a></li>
      <?php } if($jabatan=='supervisor') { ?>
      <li><a class="<?php if($page=='checking') echo 'current' ?>" href="<?php _e(base_url().'checking/add') ?>" accesskey="c"><span class="key">C</span>hecking</a></li>
      <li><a class="<?php if($page=='item') echo 'current' ?>" href="<?php _e(base_url().'item/add') ?>" accesskey="d"><span class="key">D</span>ata</a></li>
      <li><a class="<?php if($page=='report') echo 'current' ?>" href="<?php _e(base_url().'report/sales')?>" accesskey="i"><span class="key">R</span>eporting</a></li>
      <li><a class="<?php if($page=='graph') echo 'current' ?>" href="<?php _e(base_url().'graph/sales')?>" href="#" accesskey="r"><span class="key">G</span>raph</a></li>
      <?php } ?>
      <li><a href="#" accesskey="h"><span class="key">H</span>elp</a></li>
    </ul>    
    <div id="search">
      <?php _e(form_open('item/cari')) ?>
        <p>
          <input type="text" id="search_input" name="keywords" class="search" value="Cek persediaan barang" onfocus="clearText()" onblur="fillText()"/>
          <span class="button"><input type="submit" name="submit_cari" value="Cari" class="button" /></span>
        </p>
      <?php _e(form_close()) ?>
    </div>
</div>
  <div class="gboxtop"></div>
  <div class="gbox" style="height: 35px;">
    <?php if($page =='report') { ?>
    <p style="float:left;text-align: left; padding-right: 20px;">
        <a href="<?php _e(base_url().'report/search')?>">Cari Penjualan</a> .:.
        <a href="<?php _e(base_url().'report/sales')?>">Laporan Penjualan</a> .:. 
        <a href="<?php _e(base_url().'report/stok')?>">Laporan Stok Barang</a> .:. 
        <a href="<?php _e(base_url().'report/retur')?>">Laporan Retur Barang</a> .:. 
        <a href="<?php _e(base_url().'report/checking')?>">Laporan Penggantian Barang</a>
    </p>
    <? } if($page == 'pos') { ?>
    
    <?php } if($page == 'item') {?>
    <p style="float:left;text-align: left; padding-right: 20px;">
        <a href="<?php _e(base_url().'item/add')?>">Mutasi Masuk</a> .:. 
        <a href="<?php _e(base_url().'item/retur')?>">Retur Barang</a> .:. 
        <a href="<?php _e(base_url().'item/import')?>">Import Data</a> .:.         
        <a href="<?php _e(base_url().'item/manage')?>">Manajemen Data</a> 
    </p>
    <?php } if($page=='user') { ?>
    <p style="float:left;text-align: left; padding-right: 20px;">
        <a href="<?php _e(base_url().'user/add')?>">Tambah User</a> .:.        
        <a href="<?php _e(base_url().'user/manage')?>">Manajemen User</a> .:.            
    </p>
    <?php } if($page == 'checking') { ?>
    <p style="float:left;text-align: left; padding-right: 20px;">
        <a href="<?php _e(base_url().'checking/add')?>">Input Opname</a> .:.        
        <a href="<?php _e(base_url().'checking/manage')?>">Lihat Opname</a> .:.        
        <a href="<?php _e(base_url().'checking/confirm')?>">Penggantian Barang</a> .:.        
    </p>
    <?php } if($page == 'graph') { ?>
    <p style="float:left;text-align: left; padding-right: 20px;">
        <a href="<?php _e(base_url().'graph/sales')?>">Penjualan</a> .:.      
        <a href="<?php _e(base_url().'graph/performance')?>">Prestasi Karyawan</a> .:.      
    </p>
    <?php } if($page == 'tools') { ?>
    <p style="float:left;text-align: left; padding-right: 20px;">
        <a href="<?php _e(base_url().'tools/check')?>">Check Data</a> .:.   
    </p>
    <?php } if($page == 'presence') { ?>
    <p style="float:left;text-align: left; padding-right: 20px;">
        <a href="<?php _e(base_url().'presence/check')?>">Check Absensi</a> .:.   
        <?php if($jabatan=='supervisor') { ?><a href="<?php _e(base_url().'presence/manage')?>">Data Absensi</a> .:.<?php } ?>   
    </p>
    <?php } ?>
    
    <p style="float:right;text-align:right;"><a href="<?php _e(base_url().'profile') ?>"><?php _e($userinfo.' ('.$jabatan.')')?></a> .:. <a href="<?php _e(base_url().'home/logout')?>">Log Out</a></p>
  </div>