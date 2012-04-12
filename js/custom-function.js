/**
*JQuery Stuff
*/
var focusStatus;
var ajaxStat;
$(function(){
    
    $('#date-report').datepicker({dateFormat: 'yy-mm-dd'});
    $('#date-input').datepicker({dateFormat: 'yy-mm-dd'});
    $('#date_bon').datepicker({dateFormat: 'yy-mm-dd'});
    $('#date_absensi').datepicker({dateFormat: 'yy-mm-dd'});
    $('.date_sales').datepicker({dateFormat: 'yy-mm-dd'});
    $(".item_code").autocomplete (
			baseUrl+"item/item_autocomplete",
			{
				delay:10,
				minChars:2,
				matchSubset:1,
				matchContains:1,
				cacheLength:10,	
				onItemSelect:selectItem,
				onFindValue:findValue,
				autoFill:false
			}
		);
    /**
    * absensi datang
    */
    $('#id_karyawan').keyup(function(event){
        if(event.keyCode == 13) {
            var id_karyawan = $('#id_karyawan').val();            
            checkPresence(id_karyawan,1);
        }
    });
    /**
    * absensi pulang
    */
    $('#id-karyawan').keyup(function(event){
        if(event.keyCode == 13) {
            var id_karyawan = $('#id-karyawan').val();            
            checkPresence(id_karyawan,2);
        }
    });
    
    /**
     * Opsi laporan
     */
    $('#opsi_search').change(function(){
    	var opsi = $('#opsi_search').val();
    	if(opsi == 1)
    		$('#row_id_barang').css('display','none');
    	else if(opsi == 2)
    		$('#row_id_barang').css('display','table-row');
    });
});
//autocomplete searching
function findValue(li) {
    if( li == null ) return alert("No match!");

    // if coming from an AJAX call, let's use the CityId as the value
    if( !!li.extra ) var sValue = li.extra[0];

    // otherwise, let's just display the value in the text box
    else var sValue = li.selectValue;

    //ketika select item, update row untuk nama, harga dll
    var check = $('.table-data tr:contains('+li.selectValue+') td:first-child').html();
    var line = checkFocus(); 
    if(check == null) {               
        //check dulu apakah kode item sudah ada di baris
        $('#id_barang_'+line).html(li.selectValue);    
        $('#nama_'+line).val(li.extra[0]);
        $('#kel_barang_'+line).val(li.extra[1]);
        $('#harga_'+line).val(li.extra[2]);    
        $('#disc_'+line).val(li.extra[3]);
        $('#stok_barang_'+line).val(li.extra[4]);
        $('#qty_'+line).focus();
    }
    else {
        $('#dialog-msg p').html('<span style="color:red">Anda sudah menambahkan kode barang ini pada baris ke-'+check+'</span>');
        $('#dialog-msg').dialog({
            autoOpen: true,
            modal: true,
            buttons: {            
                OK : function() {                    
                    $(this).dialog('close');
                    $('#qty_'+check).focus();
                    $('#id_barang_'+check).html('');
                    $('.table-data tr:nth-child('+(line+1)+') td input').val('');
                }
            }
        });        
    }
}
//check where is the focus
function checkFocus() {
    var i = $('.row-data').length;
    var focused='';
    for(j=1; j<=i; j++) {
        if(focusStatus[j]==true) {
            focused = j;            
        }        
    }
    return focused;
}
//set focus
function setFocus(line) {
    focusStatus = new Array();
    focusStatus[line] = true;
    //alert(focusStatus);
}

function selectItem(li) {
    findValue(li);
}

function formatItem(row) {
    return row[0] + " (id: " + row[1] + ")";
}

function lookupAjax(){
    var oSuggest = $("#sup_name")[0].autocompleter;

    oSuggest.findValue();

    return false;
}

//show date when choosing daily report
function showDate() {
    var rowdate = '<tr><td>Tanggal</td><td> : <input type="text" name="date-report" id="date-report" onfocus="showDatepicker()" onclick="showDatepicker()" readonly="readonly"/></td></tr>';
    if($('#report-type').val()=='1') {
        $('#report').append(rowdate);
    }
    else {
        $('#report tr:nth-child(2)').remove();
    }
   
}
//showing date picker
function showDatepicker() {
    //datepicker
    $('#date-report').datepicker({dateFormat: 'yy-mm-dd'});
}
//clear text field
function clearText() {
    $('#search_input').val('');
}
//fill text field
function fillText() {
    $('#search_input').val('Cek persediaan barang');
}
/*open popup windows*/
function startPOS(url) {
    params  = 'width='+screen.width;
     params += ', height='+screen.height;
     params += ', top=0, left=0'
     params += ', location=no';

     newwin=window.open(url,'POS Apps', params);
     if (window.focus) {newwin.focus()}
     return false;
}
//redirect ke menampilkan detail barang
function viewDetail(row,id_barang) {
    //window.location.replace('detail/'+id_barang);
}
//redirect ke edit barang
function editBarang(row,id_barang,kode_bon) {
    window.location.replace('edit/'+kode_bon+'/'+id_barang)
}
//redirect ke delete barang
function deleteBarang(row,id_barang) {
    //lakukan delete barang dari sini aja
}
//show month
function showMonth() {    
    if($('#report-type').val() == '3') {
        $('#bulanan').css('display','table-row');
        $('#harian').css('display','none');
    }
    else {
        $('#bulanan').css('display','none');
        $('#harian').css('display','table-row');
    }
}
//append row for mutasi masuk
function appendRowMutasi() {
    //ambil data jumlah baris
    var i = $('.row-data').length;
    //ambil row total
    var row_total = $('.table-data tr:last-child');
    //remove row total
    $('.table-data tr:last-child').remove();
    //tambah baris
    i++;
    var tr= '<tr class="row-data">';
        tr += '<td style="width: 20px;">'+i+'</td>';
        tr +=  '<td style="width: 80px;"><input type="text" name="id_barang[]" maxlength="10" style="width:80px;" class="item_code ac_input" onkeyup="setFocus('+i+')" autocomplete="off"/></td>';
        tr +=  '<td style="width: 150px;"><input type="text" name="nama[]" id="nama_'+i+'" style="width:150px;"/></td>';
        tr +=  '<td style="width: 30px;"><input type="text" name="kel_barang[]" id="kel_barang_'+i+'" style="width:30px;"/></td>';
        tr +=  '<td style="width: 120px;"><input type="text" name="harga[]" id="harga_'+i+'" style="width:120px;" onkeyup="hitungJumlah('+i+')"/></td>';
        tr +=  '<td style="width: 30px;"><input type="text" name="qty[]" id="qty_'+i+'" style="width:30px;" onkeyup="hitungJumlah('+i+');validateQtyRetur('+i+')"/></td>';
        tr +=  '<td style="width: 50px;"><input type="text" name="disc[]" id="disc_'+i+'" style="width:30px;" onkeyup="hitungJumlah('+i+')"/></td>';
        tr +=  '<td style="width: 120px;"><span id="jumlah_'+i+'"></span><input type="hidden" id="jml_'+i+'" /></td>';
        tr +=  '</tr>';
    var div = '<div style="display: none; position: absolute; " class="ac_results"></div>';
    $('.table-data').append(tr);
    //balikin row total ke table
    $('.table-data').append(row_total);
    $('body').append(div);
    //hitung row toal
    hitungTotal();
}
//hitung jumlah untuk mutasi masuk
function hitungJumlah(num) {
    //ambil data variable
    var harga = $('#harga_'+num).val();
    if(harga == "" ) {
        harga = 0;
    }
    else {
        harga = parseFloat(harga);
    }
    
    var qty = $('#qty_'+num).val();
    if(qty == "" ) {
        qty = 0;
    }
    else {
        qty = parseFloat(qty);
    }
    
    var disc = $('#disc_'+num).val();
    if(disc == "" ) {
        disc = 0;
    }
    else {
        disc = parseFloat(disc);
    }
    
    var jumlah = harga*(1 - disc/100)*qty;
    //tampilin jumlah
    $('#jml_'+num).val(jumlah);
    $('#jumlah_'+num).html($.currency(jumlah,{s:".",d:",",c:0}));
    hitungTotal();
}
//hitung total
function hitungTotal() {
    //hitung total qty & jumlah
    var jml_data = $('.row-data').length;
    var total_qty=0;
    var total_jumlah=0;
    for(i=0; i<jml_data; ) {
        ++i;
        //hitung qty
        var qty = $('#qty_'+i).val();
        if(qty == "") {
            qty = 0;
        }
        else {
            qty = parseFloat(qty);
        }
        total_qty += qty;
        //hitung total jumlah
        var jumlah = $('#jml_'+i).val();
        if(jumlah == "") {
            jumlah = 0;
        }
        else {
            jumlah = parseFloat(jumlah);
        }
        total_jumlah += jumlah;
    }    
    //tampilkan
    $('#total_qty').html(total_qty);
    $('#total_jumlah').html($.currency(total_jumlah,{s:".",d:",",c:0}));
}
/**
*validasi qty untuk retur barang
*/
function validateQtyRetur(num) {
    var stok = parseInt($('#stok_barang_'+num).val());
    var qty_retur = parseInt($('#qty_'+num).val());
    if(qty_retur > stok) {
        $('#dialog-msg p').html('<span style="color:red">Qty retur melebih jumlah barang yang ada di database</span>');
        $('#dialog-msg').dialog({
            autoOpen: true,
            modal: true,
            width: 320,
            buttons: {
                OK : function() {
                    $(this).dialog('close');
                    $('#qty_'+num).val(stok);
                    $('#qty_'+num).focus();
                    hitungJumlah(num);
                    hitungTotal();
                }
            }
        });        
    }
}
//fungsi untuk menampilkan pilihan kode barang / kode kelompok barang
function displayForm() {
    var opsi = $('#based_on').val();
    if(opsi == 1) {
        $('#kb').css('display','table-row');
        $('#kl').css('display','none');
    }
    else if(opsi == 2) {
        $('#kl').css('display','table-row');
        $('#kb').css('display','none');
    }
}
//redirect ke cetak bon
function cetakBon(kode_bon){
    window.location.replace('retur/'+kode_bon);
}
function countBedaStok() {
    //hitung beda stok
    var stok_barang = $('#stok_barang').val();    
    var stok_opname = $('#stok_opname').val();
    if(stok_opname == '') {
        stok_opname = 0;
    }
    else {
        stok_opname = parseFloat(stok_opname);
    }
    
    $('#beda_stok').val(stok_barang - stok_opname);
}
function confirmChecking() {    
    $('#dialog-confirm-checking').dialog({
        autoOpen: true,
        modal: true,
        height: 210,
        buttons: {
            Setuju : function() {
                $('.ui-dialog-buttonpane').hide();
                doConfirmChecking(1);                   
            },
            Batal : function() {
                $(this).dialog('close');
            }
        }
    }); 
}
//function untuk konfirmasi checking barang
function doConfirmChecking(i) {    
    //ambil username sama password
    $('#progressbar').css('display','block');
    var total_brg = $('#total_brg').val();
    var total_iterasi = Math.ceil(total_brg/50);
    var progress = Math.round(i/total_iterasi*1000);
    $('#progress').html(progress/10+'%');    
    var username = $('#username').val();
    var passwd = $('#passwd').val();        
    //initialize progress bar
    $('#dialog-confirm-checking p').html('Silahkan menunggu ....');
    $('#dialog-confirm-checking p').css('text-align','center');
    $('#dialog-confirm-checking table').hide();           
    //do check passwd and username        
    $.post(
        baseUrl+"checking/confirm_checking",
        {'username': username, 'passwd': passwd,'iterasi':i}, 
        function(data){               
            //update ke table row           
            if(data.status == 1) {                
                if(data.progress <= total_iterasi) {                    
                    doConfirmChecking(data.progress);                    
                }
                else {                    
                    $('#dialog-confirm-checking').dialog('close');
                    $('#msg').html('<span style="color:green">Proses checking telah selesai</span>');
                    $('#dialog-msg').dialog({
                        autoOpen: true,
                        modal: true,                    
                        buttons: {                        
                            OK : function() {                               
                                window.location.replace('confirm');
                            }
                        }
                    }); 
                    return false;
                }
            }
            else if(data == 0){                    
                $('#dialog-confirm-checking').dialog('close');
                $('#msg').html('<span style="color:red">Otorisasi gagal, coba lagi !</span>');
                $('#dialog-msg').dialog({
                    autoOpen: true,
                    modal: true,                    
                    buttons: {                        
                        OK : function() {
                            stop();
                            window.location.replace('confirm');
                        }
                    }
                });                    
            }                
        },
        'json'
    );        
}

//fungsi untuk cetak laporan penggantian barang
function cetakGantiBarang(tanggal){
    params='';
    url = baseUrl+'report/checking/'+tanggal;
    window.open(url,'Cetak Opname', params);
}
//fungsi untuk cetak laporan stok opname
function printOpname(url) {
    params='';
    window.open(url,'Cetak Opname', params);
}
/**
* ambil data karyawan dari server
* opsi => 1 datang, 2 pulang
*/
function checkPresence(id_karyawan,opsi) {
    //panggil method untuk retrieve data karyawan sekaligus set flag untuk absensi
    $.post(
        baseUrl+"presence/save_presence",
        {'id_karyawan': id_karyawan,'opsi':opsi},
        function(data) {            
            if(data == '-1') {
                $('#err_msg').html('Sudah pernah absen');
            }
            else if(data == 0) {
                $('#err_msg').html('Tidak tercatat di absensi datang / tidak masuk');
            }
            else if(data == null) {
                $('#err_msg').html('Data tidak ditemukan');
            }
            else {
                $('#err_msg').html('');
                appendRowAbsensi(data);
                if(opsi == 1)
                    $('#id_karyawan').val('');
                else if(opsi == 2)
                    $('#id-karyawan').val('');
            }
        },
        "json"
    );
}
/**
*Menampilkan data absensi karyawan
*/
function appendRowAbsensi(data) {
    $('.table-data').css('display','table');  
       
    var i = $('.table-data tr').length; 
    //check data karyawan udah ada di baris blom    
    if($('.table-data tr:contains("'+data.NIK+'")').length == 0) {
        if(data.status == null) { //klo status msh null tampilin pilihan
            var status = '<input type="radio" name="status_'+i+'" value="1" checked="checked"/> Masuk<input type="radio" name="status_'+i+'" value="2" /> Sakit/Izin<input type="radio" name="status_'+i+'" value="3" /> Alpha <input type="radio" name="status_'+i+'" value="4" /> Libur / Off'; 
        }
        else {
            var status = data.status;
        }
        var tr = '<tr class="row-data"><td>'+i+'</td><td>'+data.NIK+'</td><td>'+data.nama+'</td><td>'+data.datang+'</td><td>'+data.pulang+'</td><td>'+status+'</td></tr>';
        $('.table-data').append(tr);
        //tampilin tombol submit
        $('#button-simpan').css('display','block');
        $('#button-simpan').css('text-align','center');
    }
    else {
        $('#err_msg').html('Sudah pernah absen');
    }
}
/**
*Menyimpan status absensi
*/
function confirmPresence() {
    $('#dialog-confirm-absensi').dialog({
        autoOpen: true,
        modal: true,
        buttons: {
            Simpan : function() {
                doConfirmPresence();                                  
            },
            Batal : function() {
                $(this).dialog('close');
            }
        }
    });
}
 
/**
*fungsi doConfirmPresence
*/
function doConfirmPresence() {
    //ambil data absensi
    var jumlah_hadir = parseInt($('.row-data').length);
    var id_karyawan = new Array();
    var status_absensi = new Array();
    var j = 0;
    for(i=0;i<jumlah_hadir;i++) {        
        var temp = $('input[name=status_'+(i+1)+']:checked').val();
        if(temp != null) {
            id_karyawan[j] = $('.table-data tr:nth-child('+(i+2)+') td:nth-child(2)').html();
            status_absensi[j] = temp;
            j++;
        }
    }
    if(j>0) {
        $.post(
            baseUrl+"presence/save_status",
            {'id_karyawan': id_karyawan,'status': status_absensi},
            function(data) {            
                if(data == 0) {
                    $('#dialog-msg p').html('<span style="color:red">Terjadi kesalahan, atau anda bukan supervisor</span>');
                    $('#dialog-msg').dialog({
                        autoOpen: true,
                        modal: true,
                        buttons: {            
                            OK : function() {
                                $(this).dialog('close');
                            }
                        }
                    });
                }
                if(data == 1) {
                    $('#dialog-msg p').html('<span style="color:green">Status absensi telah disimpan</span>');
                    $('#dialog-msg').dialog({
                        autoOpen: true,
                        modal: true,
                        buttons: {            
                            OK : function() {
                                $(this).dialog('close');
                                window.location.replace('check');
                            }
                        }
                    });                    
                }
            }
        );
    }
    $('#dialog-confirm-absensi').dialog('close');
}
/**
*view detail absensi
*/
function viewDetailAbsensi(nik,tanggal) {
    window.location.replace('view/'+nik+'/'+tanggal);
}
/**
*edit status absensi
*/
function editAbsensi(nik,tanggal) {
    window.location.replace('edit/'+nik+'/'+tanggal);
}
/**
*remove absensi
*/
function removeAbsensi(nik,tanggal,nama) {
    $('#nama').html(nama);
    $('#dialog-confirm-absensi').dialog({
        autoOpen: true,
        modal: true,
        buttons: {
            Batal : function() {
                $(this).dialog('close');
            },
            Hapus : function() {
                doRemoveAbsensi(nik,tanggal);                                  
            }
        }
    });
}
/**
*doRemoveAbsensi
*/
function doRemoveAbsensi(nik,tanggal) {
    $.post(
        baseUrl+"presence/remove",
        {'nik': nik,'tanggal': tanggal},
        function(data) {            
            if(data == 0) {
                alert('Terjadi kesalahan atau anda bukan supervisor');
            }
            if(data == 1) {
                $('#msg').html('Data absensi telah dihapus');                
                $('.table-data tr:contains("'+nik+'")').fadeOut('slow');                
            }
        }
    );
    $('#dialog-confirm-absensi').dialog('close');
}
/**
*view detail pengguna
*/
function detailPengguna(nik) {
    window.location.replace('view/'+nik);
}
/**
*edit data pengguna
*/
function editPengguna(nik) {
    window.location.replace('edit/'+nik);
}
/**
* edit password pengguna
*/
function changePassword(nik) {
    window.location.replace('editpasswd/'+nik);
}
/**
*block pengguna
*/
function blockPengguna(nik,nama) {
    $('#msg').html('Apakah anda akan memblokir ');
    $('#nama').html(nama);
    $('#dialog-confirm').dialog({
        autoOpen: true,
        modal: true,
        buttons: {
            Batal: function() {
                $(this).dialog('close');                            
            },
            Blokir: function() {
                doBlockPengguna(nik);                
            }
        }
    });
}
function doBlockPengguna(nik) {
    $.post(
        baseUrl+"user/block",
        {'nik': nik,'status': 0},
        function(data) {            
            if(data == 1) {
                $('#dialog-confirm').dialog('close');
                $('#err_msg').html('Pengguna telah diblokir');
                $('#dialog-msg').dialog({
                    autoOpen:true,
                    modal: true,
                    buttons: {
                        Ok: function() {
                            window.location.replace('manage');
                        }
                    }
                });
            }
            else if(data == 0) {
                alert('Gagal memblokir pengguna');
            }
        }
    );   
}
/**
*unblock pengguna
*/
function unblockPengguna(nik,nama) {
    $('#msg').html('Apakah anda akan melepas blokir terhadap ');
    $('#nama').html(nama);
    $('#dialog-confirm').dialog({
        autoOpen: true,
        modal: true,
        buttons: {
            Batal : function() {
                $(this).dialog('close');                               
            },
            Buka: function() {
                doUnblockPengguna(nik);
            }
        }
    });
}
function doUnblockPengguna(nik) {
    $.post(
        baseUrl+"user/block",
        {'nik': nik,'status': 1},
        function(data) {            
            if(data == 1) {
                $('#dialog-confirm').dialog('close');
                $('#err_msg').html('Pengguna telah aktif kembali');
                $('#dialog-msg').dialog({
                    autoOpen:true,
                    modal: true,
                    buttons: {
                        Ok: function() {
                            window.location.replace('manage');
                        }
                    }
                });         
            }
            else if(data == 0) {
                alert('Gagal membuka blokir');
            }
        }
    );    
}
/**
*remove pengguna
*/
function removePengguna(nik,nama) {
    $('#msg').html('Apakah anda akan menghapus ');
    $('#nama').html(nama);
    $('#dialog-confirm').dialog({
        autoOpen: true,
        modal: true,
        buttons: {
            Batal : function() {
                $(this).dialog('close');                               
            },
            Hapus: function() {
                doRemovePengguna(nik);
            }
        }
    });
}
function doRemovePengguna(nik) {
    $.post(
        baseUrl+"user/remove",
        {'nik': nik},
        function(data) {            
            if(data == 1) {
                $('#dialog-confirm').dialog('close');
                $('#succes_msg').html('Pengguna telah dihapus');
                $('.table-data tr:contains("'+nik+'")').fadeOut();                
            }
            else if(data == 0) {
                alert('Gagal membuka blokir');
            }
        }
    );    
}
/**
* Fungsi untuk save import data
*/
function saveImport(line) {
    var idx_save = line + 1;
    var item_code = $('.table-data tr:nth-child('+idx_save+') td:nth-child(2)').html();    
    var item_name = $('.table-data tr:nth-child('+idx_save+') td:nth-child(3)').html();    
    var cat_code = $('.table-data tr:nth-child('+idx_save+') td:nth-child(4)').html();    
    var item_disc = $('.table-data tr:nth-child('+idx_save+') td:nth-child(5)').html();    
    var quantity = $('.table-data tr:nth-child('+idx_save+') td:nth-child(7)').html();    
    var item_hj = $('#item_hj_'+line).val();
    var kode_bon = $('#kode_bon').val();
    var tgl_bon = $('#date_bon').val();
    //do post
    if(kode_bon != "" && tgl_bon != "") {
        $.post(
            baseUrl+"item/import",
            {'item_code': item_code, 'item_name':item_name, 'cat_code':cat_code, 'item_disc':item_disc, 'quantity':quantity, 'item_hj':item_hj,'kode_bon':kode_bon, 'tgl_bon':tgl_bon},
            function(data) {            
                if(data == '1') { //sukses
                    $('.table-data tr:nth-child('+idx_save+') td:last-child span').fadeOut('slow',function(){
                        $('.table-data tr:nth-child('+idx_save+') td:last-child').html('<span style="color:green">Tersimpan</span>');
                    });                    
                }
                else if(data == '-1') { //duplikasi data
                    $('.table-data tr:nth-child('+idx_save+') td:last-child span').fadeOut('slow',function(){
                        $('.table-data tr:nth-child('+idx_save+') td:last-child').html('<span style="color:red">Sudah Diproses</span>');
                    }); 
                }
                else if(data == '0') { //error saat insert
                    $('.table-data tr:nth-child('+idx_save+') td:last-child span').fadeOut('slow',function(){
                        $('.table-data tr:nth-child('+idx_save+') td:last-child').html('<span style="color:red">Error Database</span>');
                    });
                }
            }
        );
    }
    else {
        $('#dialog-msg p').html('<span style="color:red">Kode BON dan Tanggal BON tidak boleh dikosongkan</span>');
        $('#dialog-msg').dialog({
            autoOpen: true,
            modal: true,
            width: 320,
            buttons: {
                OK : function() {
                    $(this).dialog('close');
                }
            }
        });
    }
}
/**
* Fungsi untuk save import data penjualan
*/
function saveSales(line) {
    var idx_save = line + 1;
    var tanggal = $('.table-data tr:nth-child('+idx_save+') td:nth-child(2)').html();
    var id_transaksi = $('.table-data tr:nth-child('+idx_save+') td:nth-child(3)').html();
    var id_barang = $('.table-data tr:nth-child('+idx_save+') td:nth-child(4)').html();
    var qty = $('.table-data tr:nth-child('+idx_save+') td:nth-child(6)').html();
    var no_cc = $('.table-data tr:nth-child('+idx_save+') td:nth-child(7)').html();
    var disc_item = $('.table-data tr:nth-child('+idx_save+') td:nth-child(8)').html();
    var disc_all = $('.table-data tr:nth-child('+idx_save+') td:nth-child(9)').html();
    var id_kasir = $('#id_kasir_'+line).val();
    var id_pramuniaga = $('#id_pramuniaga_'+line).val();;
    var total = $('#total_'+line).val();    
    //do post    
    $.post(
        baseUrl+"checking/save_import",
        {'save_import': 1,'tanggal': tanggal, 'id_transaksi':id_transaksi, 'id_barang':id_barang, 'qty':qty,'no_cc':no_cc, 'disc_item':disc_item, 'disc_all':disc_all,'id_kasir':id_kasir, 'id_pramuniaga':id_pramuniaga,'total':total},
        function(data) {            
            if(data == '1') { //sukses
                $('.table-data tr:nth-child('+idx_save+') td:last-child span').fadeOut('slow',function(){
                    $('.table-data tr:nth-child('+idx_save+') td:last-child').html('<span style="color:green">Tersimpan</span>');
                });                    
            }
            else if(data == '-1') { //duplikasi data                
                $('.table-data tr:nth-child('+idx_save+') td:last-child span').fadeOut('slow',function(){
                    $('.table-data tr:nth-child('+idx_save+') td:last-child').html('<span style="color:red">Sudah Diproses</span>');
                }); 
            }
            else if(data == '0') { //error saat insert
                $('.table-data tr:nth-child('+idx_save+') td:last-child span').fadeOut('slow',function(){
                    $('.table-data tr:nth-child('+idx_save+') td:last-child').html('<span style="color:red">Error Database</span>');
                });
            }
            else if(data == '2') { //error saat insert
                $('.table-data tr:nth-child('+idx_save+') td:last-child span').fadeOut('slow',function(){
                    $('.table-data tr:nth-child('+idx_save+') td:last-child').html('<span style="color:red">Stok Habis</span>');
                });
            }
        }
    );    
}
