/**
* Name : Point of Sales
* Author : PuRwa Love IcHa
* Version : Beta
* Desc: Program ini digunakan untuk menjalankan applikasi terkait point of sales
*/

//--APLIKASI UTAMA POINT OF SALES --//
var searchFocus=false;
var statusQty=false;
var screenWidth;
var screenHeight;
/**
*Run POS
*/
try {    
	$(document).ready(function(){
        /**
        *Retrieve screen configuration
        */        
        if(screenConfig == 14) {
            screenWidth = 1014;
            screenHeigth = 635;
        }
        if(screenConfig == 17) {
            screenWidth = 1350;
            screenHeight = 760;
        }    
        /**
        * Launch POS Application
        */
        //viewing dialog
		$('#dialog-form').dialog({
			width: screenWidth,
			height: screenHeight,
			dragable: false,
			resizeable: false,
			modal: true,
            open: function(){
                    $(this).parents(".ui-dialog:first").find(".ui-dialog-titlebar-close").remove();
                },
			closeOnEscape: false,            
			dialogClass: "dialog-form"
		});
        //display digital clock
        digitalClock();
        //fokus ke barcode
        $('#barcode').focus();
        //tulis selamat datang pada saat gak ada transaksi    
        displayMsg(formatMsg(defMsg));
        /*
		*Memunculkan pesan error
		*/
		$('#dialog-message').dialog({
			autoOpen: false,
			modal: true,
			buttons: {
				Ok : function() {
					$(this).dialog('close'); 
                    setTimeout("$('#barcode').focus()",250);
				}
			}
		}); 	
        /*
		*Listen event keyup, untuk shortcut dan lain lain.
		*/        
		$(window).keyup(function(event){               
			//$('#trigger').html(event.keyCode); 
            //F2 -- fokus ke text box kode label / barcode
			if(event.keyCode == 113) {
				$('#barcode').focus();
			}
            //F4 --disc per item
            if(event.keyCode == 115) {
                var num = $('#row-data tr').length - 1;
                $('#diskon_'+num).focus();
            }
            //F7 --qty per item
            if(event.keyCode == 118) {
                var num = $('#row-data tr').length - 1;
                $('#qty_'+num).focus();
            }
            //F8 -- select pramuniaga
            if(event.keyCode == 119) {
                $('#pramu_list').val("");
                $('#id_pramu').focus();                
            }
            //F9 -- mencetak ulang resi terakhir
            if(event.keyCode == 120) {
                printReceipt(2);
            }
            //F10 / page up disc all item
            if(event.keyCode == 121) {
                $('#disc_all').focus();
            }                
			//Space, pay bill and print receipt - Cash
            if(event.keyCode == 32 && !searchFocus) {
                //validate sales
                var data = $('#row-data tr')[0];
                if(data == null) {
                    displayNotification('Belum ada transaksi');
                }
                else {
                    //kalau ok, tampilin dialog untuk masukin duit yang dibayarin
                    $('#dialog-prompt-cash').dialog({
                        resizeable: false,
                        width: 250,
                        height: 200,                        
                        modal: true,
                        buttons : {
                            Cancel: function() {
                                $(this).dialog('close');
                            },
                            OK: function() {
                                //lakukan pembayaran transaksi
                                payTrans(1);//cash
                                //$(this).dialog('close');
                            }
                        }
                    });
                    $('#trans-cash').focus();
                }           
                
            }
            //Alt, pay bill and print receipt - Credit Card
            if(event.keyCode == 18 && !searchFocus) {
                //validate sales
                var data = $('#row-data tr')[0];
                if(data == null) {
                    displayNotification('Belum ada transaksi');
                }
                else {
                    //kalau ok, tampilin dialog untuk masukin duit yang dibayarin
                    $('#dialog-prompt-credit').dialog({
                        resizeable: false,
                        height: 190,  
                        width: 320,
                        modal: true,
                        buttons : {
                            Cancel: function() {
                                $(this).dialog('close');
                            },
                            OK: function() {
                                //lakukan pembayaran transaksi
                                payTrans(2);//credit
                                //$(this).dialog('close');
                            }
                        }
                    });
                    $('#cc_bank').focus();
                }           
                
            }
            //Delete - cancel item / void
            if(event.keyCode == 46) {
                //munculin dialog untuk minta input baris ke berapa yang akan dibatalkan
                var data = $('#row-data tr')[0];
                if(data == null) {
                    displayNotification('Belum ada transaksi');
                }
                else {
                    $('#dialog-prompt-trans').dialog({
                        resizeable: false,
                        height: 190,
                        width: 320,
                        modal: true,
                        buttons : {
                            Cancel: function() {
                                $(this).dialog('close');
                                $('#barcode').focus();
                            },
                            OK: function() {
                                //lakukan pembatalan transaksi
                                cancelTrans();                                
                            }
                        }
                    });   
                    $('#trans-nth').focus();
                }
            }
            //Insert - search barang
            if(event.keyCode == 45) {
                $('#dialog-search').dialog({
                    width: 800,
                    height: 600,
                    modal: true,
                    buttons: {
                        Back: function() {
                            $(this).dialog('close');
                        }
                    }
                }); 
                $('#key').focus();
            }
            //Home - refund barang
            if(event.keyCode == 36) {
                $('#dialog-refund').dialog({
                    width: 800,
                    height: 600,
                    modal: true,
                    buttons: {
                        Back: function() {
                            $(this).dialog('close');
                        }
                    }
                });
                $('#barang-tukar').focus();   
            }
            //Page Down - cut last two digit, make zero
            if(event.keyCode == 34) {
                var total = parseFloat($('#total_val').val());
                var disc = parseFloat($('#disc_all').val());
                total = total * (1 - disc/100);
                total = Math.floor(total/100) * 100;
                $('#total').val($.currency(total,{s:".",d:",",c:0})+',-');
                $('#barcode').focus();
                //display the total
                var msg = new Array();
                msg[0] = 'Total (Rupiah)';
                msg[1] = $.currency(total,{s:".",d:",",c:0})+',-';
                displayMsg(formatMsg(msg));
            }
            //End - Total penjualan hari ini
            if(event.keyCode == 35) {
                //ambil data total penjualan
                $.post(
                    "temp_sales",                    
                    function(data){
                        $('#sales').html('Rp. '+$.currency(data,{s:".",d:",",c:0})+',-');
                        $('#dialog-sales').dialog({                     
                            modal: true,
                            buttons: {
                                Cetak : function() {
                                    printTempSales();                                    
                                },
                                Batal : function() {
                                    $(this).dialog('close');
                                }
                            }
                        });
                        $('.ui-button').focus();
                    } 
                );
            }
            //Escape- Close Application-return to windows
            if(event.keyCode == 123) {
               $('#dialog-confirm-exit').dialog({
                    resizable: false,
                    height:160,
                    modal: true,
                    buttons: {
                        Cancel: function() {
                            $(this).dialog('close');
                        },
                        OK: function() {
                            $(this).dialog('close');
                            window.location.replace('home');
                        }                       
                    }
                });
                $('.ui-button').focus();
            }
		});
        $('#dialog-form').keyup(function(event){
            if(event.keyCode == 27) {
                $('#dialog-confirm-exit').dialog({
                    resizable: false,
                    height:160,
                    modal: true,
                    buttons: {
                        Cancel: function() {
                            $(this).dialog('close');
                        },
                        OK: function() {
                            $(this).dialog('close');
                            window.location.replace('home');
                        }                       
                    }
                });
                $('.ui-button').focus();
            }
        });
        /**
		*Handling event key pada saat focus di textbox barcode
		*/        
		$('#barcode').keyup(function(event){
			//keyCode : 13 -- enter
			//keyCode : 8 -- backspace
			//keycode : 46 -- delete            
			//keycode : 48-57 && 96-105 -- numeric
            //keyCode : 27 -- escape
			if(!((event.keyCode >= 112 && event.keyCode <= 123) || event.keyCode==18 || event.keyCode == 35 || event.keyCode == 33 ||event.keyCode == 34 || event.keyCode == 32 || event.keyCode == 45  || event.keyCode == 36 || event.keyCode == 46 || event.keyCode == 13 || event.keyCode == 8 || event.keyCode == 27 || (event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105))){
				displayNotification('Hanya boleh diisi angka');				
			}
			else {
				if(event.keyCode == 13) { //sukses baca barcode
                    var item_code = $('#barcode').val();
                    if(item_code.length == 8 || item_code.length == 10 || item_code.length == 13){
                        //request ajax
                        $.post(
                            "getItem",
                            {id_barang: item_code}, 
                            function(data){
                                //update ke table row
                                if(data == 0) {
                                    displayNotification('Stok habis atau kesalahan data');
                                }
                                else {
                                    appendRow(data);
                                }
                            },
                            "json"
                        );                        
                    }
                    else {
                        displayNotification('Kode Label tidak valid '+ item_code); 
                        $('#barcode').focus();
                    }
			    }
		    }
		});
        /**
        *Handling event untuk pembatalan transaksi
        */
        $('#trans-nth').keyup(function(event){
            if(event.keyCode == 13) {
                cancelTrans();
            }
        });
        /**
        *Handling event untuk pembayaran transaksi ketika tekan enter setalah tulis nomnal bayar
        */
        $('#trans-cash').keyup(function(event){
            if(event.keyCode == 13) {
                payTrans(1);//cash
            }
        });
        /**
        *Handling event untuk bayar refund ketika tekan enter setelah tulis nominal bayar
        */
        $('#refund-cash').keyup(function(event){
            if(event.keyCode == 13){                
                transRefund();
            }
        });
        /**
        *Handling event saat focus ke text box pramuniaga
        */
        $('#id_pramu').keyup(function(event){   
            var nama = $('#id_pramu').val();            
            $('#id_pramu').autocomplete({
                source: function(request,response){
                            $.ajax({
                                url: "pramu_autocomplete/"+nama,
                                method: "post",
                                dataType: "json",
                                success: function(data) {
                                            response($.map(data, function(item) {
                                                return {
                                                    label: item.nama,
                                                    value: item.NIK
                                                };
                                            }));
                                        }
                            });                        
                        },
                minLength: 1,
                select: function(event,ui){
                            var temp = $('#pramu_list').val();
                            if(allowPramu == 0 || temp=="") {
                                $('#pramu_list').val(ui.item.value);                               
                            }
                            else {
                                $('#pramu_list').val(temp +','+ui.item.value);
                            }                            
                        }
            });
            if(event.keyCode == 13){
                $('#id_pramu').val($('#pramu_list').val());
                $('#barcode').focus();
            }
        });
        /*Pramuniaga untuk proses refund*/
        $('#ref_pramu').keyup(function(event){   
            var nama = $('#ref_pramu').val();           
            $('#ref_pramu').autocomplete({
                source: function(request,response){
                            $.ajax({
                                url: "pramu_autocomplete/"+nama,
                                method: "post",
                                dataType: "json",
                                success: function(data) {
                                            response($.map(data, function(item) {
                                                return {
                                                    label: item.nama,
                                                    value: item.NIK
                                                };
                                            }));
                                        }
                            });                        
                        },
                minLength: 1,
                select: function(event,ui){
                            var temp = $('#refpramu_list').val();
                            if(allowPramu==0 || temp=="") {
                                $('#refpramu_list').val(ui.item.value);
                            }
                            else {
                                $('#refpramu_list').val(temp +','+ui.item.value);
                            }                            
                        }
            });
            if(event.keyCode == 13){
                $('#ref_pramu').val($('#refpramu_list').val());
            }
        });
        /**
        *handling event saat search barang
        */
        $('#key').keyup(function(event){
            if(event.keyCode == 13) {
                //do serach stuff
                searchItem();
            }
            //cuma boleh angka sama huruf
            //0-9,a-z,A-Z
            if(!((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 65 && event.keyCode <= 90) || event.keyCode == 13 || event.keyCode == 8)) {
                $('#err-key').html('Keywords hanya angka (0-9) dan huruf (a-z)');
                $('#key').val('');                
            }
            else {
                $('#err-key').html('');
            }
        });
        /**
        *handling event saat refund / tukar barang
        */
        //handling saat kursor di text box barang ditukar
        $('#barang-tukar').keyup(function(event){
            if(event.keyCode == 13) {
                //ambil info barang kalau kode barang 8 atau 10 digit baru diizinkan
                getItem(1);
            }
            if(!((event.keyCode >= 48 && event.keyCode <= 57)|| (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 13 || event.keyCode == 8)) {
                $('#err-tukar').html('Hanya boleh angka (0-9)');
                $('#barang-tukar').val('');                
            }
            else {
                $('#err-tukar').html('');
            }
        });
        //handling saat kursor di text box barang pengganti
        $('#barang-pengganti').keyup(function(event){
            if(event.keyCode == 13) {
                //ambil info barang
                getItem(2);
            }
            if(!((event.keyCode >= 48 && event.keyCode <= 57)|| (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 13 || event.keyCode == 8)) {
                $('#err-pengganti').html('Hanya boleh angka (0-9)');
                $('#barang-pengganti').val('');                
            }
            else {
                $('#err-pengganti').html('');
            }
        });
        //handling saat mau proses transaksi refund, pencet tombol Page Up untuk bayar refund
        $('#dialog-refund').keyup(function(event){            
            if(event.keyCode == 33) {
                //tampilin total harga setelah dibulatkan dulu                
                var harga_tukar = parseFloat($('#total_tukar').val());
                var harga_pengganti = parseFloat($('#total_pengganti').val());
                //do refund stuff                  
                if(harga_pengganti >= harga_tukar) {
                    //tampilkan dialog untuk jumlah pembayaran kurangnya berapa    
                    $('#dialog-prompt-refund').dialog({
                        resizeable: false,
                        width: 250,
                        height: 200,                        
                        modal: true,
                        buttons : {
                            Cancel: function() {
                                $(this).dialog('close');
                            },
                            OK: function() {
                                //lakukan pembayaran transaksi refund
                                transRefund();
                                //$(this).dialog('close');
                            }
                        }
                    });
                    $('#refund-cash').focus();
                }
                else {
                    $('#err-refund').html('Harga barang pengganti harus lebih besar atau sama dengan harga barang yang ditukar');
                }
            }
        });
    });
}
catch (err){
	alert(err.toString());
}

//----FUNGSI FUNGSI TERKAIT -----//
function runPos() {
    ;
}
/**
* fungsi untuk menampilkan data ke transaction detail / append table row
*/
function appendRow(data){ 
    if(data != null) {    
        var num = $('#row-data tr:last-child td:first-child').html();
        if(num == null) { 
            num = 0;
        }
        else {
            num = parseInt(num);
        }
        validateQtyById(data.id_barang,data.stok_barang);
        if(statusQty == true){
           var row = '<tr class="row">';
            row += '<td width="5%" style=" text-align: center;" class="num">'+ (num + 1)+ '</td>';
            row += '<td width="15%"style=" text-align: center;">'+data.id_barang+'</td>';
            row += '<td width="25%">'+data.nama+'</td>';
            row += '<td width="15%" style=" text-align: right;">'+$.currency(data.harga,{s:".",d:",",c:0})+',-'+'<input type="hidden" id="harga_'+num+'" value="'+data.harga+'"/></td>';
            row += '<td width="10%" style=" text-align: center;"><input type="text" size="5" style="text-align:center" id="diskon_'+num+'" value="'+data.diskon+'" onkeyup="countTotal('+num+')" ></td>';
            row += '<td width="10%" style=" text-align: center;"><input type="text" size="5" style="text-align:center" name="qty" id="qty_'+num+'" value="1" onkeyup="countTotal('+num+');validateQty('+num+');"><input type="hidden" id="stok_'+num+'" value="'+data.stok_barang+'"/></td>';
            row += '<td width="20%" style=" text-align: right;"><span id="jumlah_'+num+'">'+$.currency(data.harga,{s:".",d:",",c:0})+',-'+'</span><input type="hidden" id="jmlh_'+num+'" value="'+data.harga+'"/></td>';
            row += '</tr>';
            /*Display data to PD Series*/
            var msg = new Array();
            msg[0] = data.nama;
            msg[1] = $.currency(data.harga,{s:".",d:",",c:0}) +',-';
            
            displayMsg(formatMsg(msg));  
            //check apakah id barang tersebut sudah ada di dalam baris
            var check = $('#row-data tr:contains('+data.id_barang+') td:nth-child(1)').html();
            if(rowType == 1 && check != null) {
                check = parseInt(check) - 1;
                var new_qty = parseInt($('#qty_'+check).val()) + 1;
                $('#qty_'+check).val(new_qty);
                countTotal(check);
            }
            else {            
                $('#row-data').append(row);
                countTotal(num);
            }            
            $('#barcode').val('');
        }
    }
    else{
        displayNotification('Kode item tersebut tidak terdaftar dalam database');
    }
}
/*formatting message*/
function formatMsg(msg) {
    var message = '';
    //ambil dua puluh karakter aja untuk baris bertama
    if(msg[0].length > 20)
        msg[0] = msg[0].substr(0,20);
    //formating string
    message += msg[0] + spacer(20 - msg[0].length);
    message += spacer(20 - msg[1].length) + msg[1];
    return message;
}
function spacer(n) {
    var msg = '';
    for(i=0;i < n;i++) {
        msg += ' ';
    }
    return msg;
}
/**
*fungsi untuk menampilkan hasil searching 
*/
function appendResult(data) {
    $('#search-result tr').remove();
    var row = '<tr class="head"><td style="width:40px">N0</td><td style="width:150px">KODE BARANG</td><td style="width:230px">NAMA BARANG</td><td style="width:170px">HARGA BARANG (Rp)</td><td style="width:90px">STOK BARANG</td></tr>';
    if(data.length > 1) {
        var i=0;
        for(i=0;i<data.length;i++){
            row += '<tr class="row"><td style="text-align:center">'+ (i+1) +'</td><td>'+data[i].id_barang+'</td><td>'+data[i].nama+'</td><td style="text-align: right">'+$.currency(data[i].harga,{s:".",d:",",c:0})+',-</td><td style="text-align:center">'+ data[i].stok_barang+'</td></tr>';
        }
    }
    else {
        row += '<tr class="row"><td style="text-align:center">'+ 1 +'</td><td>'+data.id_barang+'</td><td>'+data.nama+'</td><td style="text-align: right">'+$.currency(data.harga,{s:".",d:",",c:0})+',-</td><td style="text-align:center">'+ data.stok_barang+'</td></tr>';
    }
    $('#search-result').append(row);
    //alert(data[0].id_barang)
}
/**
* fungsi untuk display notifikasi
*/
function displayNotification(message) {
        //showing notification
        $('#dialog-message').html(message);
        $('#barcode').val('');
        $('#dialog-message').dialog('open');
        $('.ui-button').focus();
        //end of showing notification       
}
/**
*Menampilkan jam digital
*/
function digitalClock() {
    var currentTime = new Date();
    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();
    var seconds = currentTime.getSeconds();
    if(hours < 10){
        hours = "0" + hours;
    }
    if (minutes < 10){
        minutes = "0" + minutes;
    }
    if(seconds < 10) {
        seconds = "0" + seconds;
    }    
    var clock = (hours + ":" + minutes + ":" + seconds);
    /*var month = "";
    switch(currentTime.getMonth()) {
        case 0 : month="Januari";break;
        case 1 : month="Februari";break;
        case 2 : month="Maret";break;
        case 3 : month="April";break;
        case 4 : month="Mei";break;
        case 5 : month="Juni";break;
        case 6 : month="Juli";break;
        case 7 : month="Agustus";break;
        case 8 : month="September";break;
        case 9 : month="Oktober";break;
        case 10 : month="November";break;
        case 11 : month="Desember";break;
    }
    var date = (currentTime.getDay() + " " + month + " " + currentTime.getFullYear());*/
    $('#digiClock').html(clock);
    //$('#digiDate').html(date);
    setTimeout("digitalClock()",1000);
}
/* Display message to PD Series */
function displayMsg(msg) {
    $.post(
            "get_kassa",                     
            function(kassa){                        
                $('#appletPrinter')[0].writeMessage(msg,kassaServer[kassa]);          
            }        
    );    
}
/*Print receipt*/
function printReceipt(mode,tunai,id_transaksi) {    
    $.post(
        "print_receipt",
        {'option': mode, 'cash': tunai,'id_transaksi':id_transaksi}, 
        function(receipt){
            /*$.post(
                    "get_kassa",                     
                    function(kassa){                        
                        $('#appletPrinter')[0].sendReceipt(receipt,kassaServer[kassa]);           
                    }        
            );*/                       
        }        
    );   
}
/**
*Print receipt refund
*/
function printRefundReceipt(mode,tunai,brg_tukar,qty_tukar,id_transaksi) {
    $.post(
        "print_receipt",
        {'option': mode, 'cash': tunai,'brg_tukar[]': brg_tukar, 'qty_tukar[]':qty_tukar,'id_transaksi':id_transaksi}, 
        function(receipt){
            /*$.post(
                    "get_kassa",                     
                    function(kassa){                        
                        $('#appletPrinter')[0].sendReceipt(receipt,kassaServer[kassa]);           
                    }        
            );*/   
        }        
    );
}
/**
*Print temporary sales 
*/
function printTempSales() {
    $.post(
        "temp_sales",
        {print: '1'}, 
        function(receipt){
            /*$.post(
                    "get_kassa",                     
                    function(kassa){                        
                        $('#appletPrinter')[0].sendReceipt(receipt,kassaServer[kassa]);
                        //alert(receipt);
                    }        
            );*/             
        }        
    );
    $('#dialog-sales').dialog('close');
}
/**
* Membatalkan transaksi ke n
*/
function cancelTrans() {
    //validasi baris ke n nya, ada atau tidak
    var nthTrans = $('#trans-nth').val();    
    var rowData = $('#row-data tr:nth-child('+nthTrans+')');    
    if(rowData[0] == null) {
        $('#dialog-prompt-trans #err-msg').html('Tidak ada transaksi dengan nomor tersebut');
    }
    else {
        if($('#row-data tr').length > 1) {
            rowData.css('text-decoration','line-through');
            $('#diskon_'+(nthTrans-1))[0].readOnly = true;
            $('#qty_'+(nthTrans-1))[0].readOnly = true;
            $('#jmlh_'+(nthTrans-1)).val(0);          
        }
        else {
            $('#row-data tr').remove();
        }
        $('#dialog-prompt-trans').dialog('close');
        $('#barcode').focus();
    }
    $('#trans-nth').val('');
    countAllTotal();
    countAllWithDisc();    
}
/**
* Membatalkan semua transaksi mirip void all
*/

/**
* Melakukan pembayaran transaksi
* mop => method of payment, 1->cash, 2->credit
*/
function payTrans(mop) {
    //retrieve data transaksi
    var num = $('#row-data tr:last-child td:first-child').html();
    if(num == null) { 
        num = 0;
    }
    else {
        num = parseInt(num);
    }
    var rowData = $('#row-data tr');
    var id_barang = new Array();
    var nama_barang = new Array();
    var harga_barang = new Array();
    var qty = new Array();
    var disc = new Array();
    var jumlah = new Array();
    var id_transaksi = $('#no_tunggu').val();
    var id_pramuniaga = $.trim($('#id_pramu').val());
    var disc_all = parseFloat($.trim($('#disc_all').val()));
    var total = parseFloat($('#total_val').val());
    var item_valid = 0;
    total = total*(1- (disc_all/100));
    total = Math.floor(total/100) * 100;
    var check = "";
    for(i=0; i<num; i++) {        
        cek = $('#row-data tr:nth-child('+(i+1)+')').css('text-decoration');        
        if(cek == "none") {            
            id_barang[i] = $('#row-data tr:nth-child('+(i+1)+') td:nth-child(2)').html();
            nama_barang[i] = $('#row-data tr:nth-child('+(i+1)+') td:nth-child(3)').html();
            harga_barang[i] = $('#harga_'+i).val();
            qty[i] = $('#qty_'+i).val();
            disc[i] = $('#diskon_'+i).val();
            jumlah[i] = $('#jmlh_'+i).val();
            item_valid++;
        }
        else {
            //jumlah di set negatif, biar di server dianggap sebagai transaksi tidak valid / dibatalkan
            jumlah[i] = -1;
        }            
    }
    
    //bayar cash
    if(mop == 1) {
        var cash = parseFloat($('#trans-cash').val());
        var bill = total;
        //tampilin cash
        var msg = new Array();
        msg[0] = 'Tunai (Rp)';
        msg[1] = $.currency(cash,{s:".",d:",",c:0})+',-';
        displayMsg(formatMsg(msg));
        /////////
        if(bill <= cash) {
            //send ajax request, saving transaction data to database
            $.post(
                "transaction",
                {'id_trans': id_transaksi, 'id_barang[]': id_barang,'item_valid':item_valid,'qty[]': qty,'disc[]':disc,'jumlah[]':jumlah,'id_pramu':id_pramuniaga,'disc_all':disc_all,'total':total},
                function(data){
                    //success
                    if(data == 1) { 
                        //print receipt + munculin angka kembalian
                        printReceipt(1,cash,id_transaksi);
                        //kalau tdk ok, kasih taw kesalahannya
                        //ketika kembalian dipilih ok, maka kembali ke siap transaksi
                        var kembalian = cash - bill;
                        $('#cashback').html('Rp. '+$.currency(kembalian,{s:".",d:",",c:0})+',-');
                        //display message
                        var msg = new Array();
                        msg[0] = 'Kembali (Rp)';
                        msg[1] = $.currency(kembalian,{s:".",d:",",c:0})+',-';
                        displayMsg(formatMsg(msg));
                        $('#dialog-cashback').dialog({                     
                            modal: true,
                            buttons: {
                                Ok : function() {
                                    $(this).dialog('close');
                                    //var cash = bill + kembalian;                        
                                    setTimeout("location.reload()",500);
                                }
                            }
                        });
                        $('.ui-button').focus();
                    }
                    else {
                    	displayNotification('Transaksi sudah dicash, minta supervisor untuk periksa laporan penjualan. Tekan F5');
                    }
                }
            );
            $('#dialog-prompt-cash').dialog('close');
        }
        else {       
            $('#err-msg-cash').html('Pembayaran kurang');
        }
    }
    //bayar pake kartu kredit
    else if(mop == 2) {
        //ambil info kartu kredit, nama bank dan nomor kartu kredit        
        var cc_num = $('#cc_bank').val() +'-'+ $('#cc_num_1').val() + $('#cc_num_2').val() + $('#cc_num_3').val() + $('#cc_num_4').val();
        //send ajax request, saving transaction data to database
            $.post(
                "transaction_credit",
                {'id_trans': id_transaksi, 'id_barang[]': id_barang,'qty[]': qty,'disc[]':disc,'jumlah[]':jumlah,'id_pramu':id_pramuniaga,'disc_all':disc_all,'cc_num':cc_num,'total':total},
                function(data){
                    //success
                    if(data == 1) { 
                        //print receipt + munculin angka kembalian
                        printReceipt(1,total,id_transaksi);
                        //kalau tdk ok, kasih taw kesalahannya
                        //ketika kembalian dipilih ok, maka kembali ke siap transaksi                       
                        $('#cashback').html('Rp. '+$.currency(0,{s:".",d:",",c:0})+',-');
                        $('#dialog-cashback').dialog({                     
                            modal: true,
                            buttons: {
                                Ok : function() {
                                    $(this).dialog('close');
                                    //var cash = bill + kembalian;                        
                                    setTimeout("window.location.replace('launch')",500);
                                }
                            }
                        });
                        $('.ui-button').focus();
                    }
                    else {
                        alert('error');
                    }
                }
            );
            $('#dialog-prompt-credit').dialog('close');
    }        
}
    

/**
*Bayar penukaran (refund) dengan pencet tombol refund
*/
function trigerButtonRefund() {
    //tampilkan dialog untuk jumlah pembayaran kurangnya berapa    
    $('#dialog-prompt-refund').dialog({
        resizeable: false,
        height: 200,                        
        modal: true,
        buttons : {
            Cancel: function() {
                $(this).dialog('close');
            },
            OK: function() {
                //lakukan pembayaran transaksi refund
                transRefund();
                //$(this).dialog('close');
            }
        }
    });
    $('#refund-cash').focus();
}
/**
*Melakukan penukaran barang / refund
*/
function transRefund() {
    //barang yang ditukar tambahkan ke stok   
    var brg_tukar = new Array();
    var qty_tukar = new Array();
    var disc_tukar = new Array();
    var tukar = $('#detail-tukar tr');
    for(i=1;i<tukar.length;i++) {
        brg_tukar[i-1] = $('#detail-tukar tr:nth-child('+(i+1)+') td:nth-child(2)').html();
        qty_tukar[i-1] = $('#qty_refund_'+i).val();
        disc_tukar[i-1] = $('#disc_refund').val();
    }    
    var brg_pengganti = new Array();
    var qty_pengganti = new Array();
    var disc_pengganti = new Array();
    var pengganti = $('#detail-pengganti tr');
    for(i=1;i<pengganti.length;i++) {
        brg_pengganti[i-1] = $('#detail-pengganti tr:nth-child('+(i+1)+') td:nth-child(2)').html(); 
        qty_pengganti[i-1] = $('#qty_ganti_'+i).val();
        disc_pengganti[i-1] = $('#disc_refund').val();
    }
    var id_pramu = $('#refpramu_list').val();
    //bayar kurangan ketika tukar barang
    var cash = parseFloat($('#refund-cash').val());    
    var total_tukar = parseFloat($('#total_tukar').val());
    var total_pengganti = parseFloat($('#total_pengganti').val());
    var bill = total_pengganti - total_tukar;
    bill = Math.floor(bill/100) * 100;
    //tampilin cash
    var msg = new Array();
    msg[0] = 'Tunai (Rp)';
    msg[1] = $.currency(cash,{s:".",d:",",c:0})+',-';
    displayMsg(formatMsg(msg));
    //ajax request untuk refund barang       
    if(bill <= cash) {
        $.post(
            "trans_refund",
            {'id_tukar[]': brg_tukar,'qty_tukar[]':qty_tukar,'id_pengganti[]': brg_pengganti,'qty_pengganti[]':qty_pengganti, 'disc_tukar':disc_tukar, 'disc_pengganti[]':disc_pengganti, 'id_pramu': id_pramu, 'total': bill},
            function(data){
                if(data.status == 1) {
                    //print receipt + munculin angka kembalian
                    printRefundReceipt(3,cash,brg_tukar,qty_tukar,data.id_transaksi);
                    var kembalian = cash - bill;
                    $('#cashback').html('Rp. '+$.currency(kembalian,{s:".",d:",",c:0})+',-');
                    //display message
                    var msg = new Array();
                    msg[0] = 'Kembali (Rp)';
                    msg[1] = $.currency(kembalian,{s:".",d:",",c:0})+',-';
                    displayMsg(formatMsg(msg));
                    $('#dialog-cashback').dialog({                     
                        modal: true,
                        buttons: {
                            Ok : function() {
                                $(this).dialog('close');                                                         
                                setTimeout("window.location.replace('launch')",500);
                            }
                        }
                    });
                    $('.ui-button').focus();                                                            
                }
            },
            "json"
        );
        $('#dialog-prompt-refund').dialog('close');
    }
    else {
        $('#err-msg-refund').html('Pembayaran kurang');
    }
}
/**
 *Validasi Qty, tidak boleh melebihi stock
 */
function validateQty(num) {    
    var stock = parseFloat($('#stok_'+num).val()); 
    //ambil semua datanya
    var id_barang = $('#row-data tr:nth-child('+(num+1)+') td:nth-child(2)').html();
    var line = $('#row-data tr:contains("'+id_barang+'") td:nth-child(6) input[name="qty"]');
    var total_qty = 0;
    for(i=0;i<line.length;i++) {
        idx = line[i].getAttribute('id');
        temp = $('#'+idx).val();
        if(temp=="") {
           temp = 0;
        }
        else {
            temp = parseFloat(temp);
        }
        total_qty += temp;
    }
    if(total_qty > stock) {        
        $('#dialog-msg span').html('Total Qty <b>'+id_barang+'</b> melebihi stok');       
        $('#dialog-msg').dialog({
			autoOpen: true,
			modal: true,
			buttons: {
				Ok : function() {
					$(this).dialog('close'); 
                    $('#qty_'+num).val('');        
                    $('#qty_'+num).focus();
				}
			}
		});        
    }
    else {
        statusQty = true;
    }
}
/**
* validasi qty berdasarkan id barang, untuk append row
*/
function validateQtyById(id_barang,stock) {
    var line = $('#row-data tr:contains("'+id_barang+'") td:nth-child(6) input[name="qty"]');
    if(line.length > 0) {
        var total_qty = 0;
        for(i=0;i<line.length;i++) {
            idx = line[i].getAttribute('id');
            temp = $('#'+idx).val();
            if(temp=="") {
                temp = 0;
            }
            else {
                temp = parseFloat(temp);
            }
            total_qty += temp;
        }        
        if(total_qty >= stock) {
            statusQty = false;
            displayNotification('<span style="color:red">Total Qty <b>'+id_barang+'</b> melebihi stok</span>');          
        }
        else {
            statusQty = true;
        }
    }
    else {
        statusQty = true;
    }
}
/**
*Validasi Qty untuk refund
*/
function validateQtyRefund(item_code, qty) {    
    //var item_code = $('#detail-tukar tr:nth-child('+(num+1)+') td:nth-child(2)').html();
    //search all line contain this item_code
    var line = $('#detail-tukar tr:contains('+item_code+') td:nth-child(6) input:text');
    var total_qty = 0;
    if(line.length > 0) {        
        for(i=0;i<line.length;i++) {
            idx = line[i].getAttribute('id');
            temp = $('#'+idx).val();
            if(temp=="") {
                temp = 0;
            }
            else {
                temp = parseFloat(temp);
            }
            total_qty += temp;
        }        
    }
    if(total_qty >= qty) {
        displayNotification('Total qty refund untuk <b>'+ item_code + '</b> berlebih, maksimal : '+qty);        
        statusQty = false;
    }
    else {
        statusQty = true;
    }    
}
function validateQtyGanti(item_code, qty) {
    //var item_code = $('#detail-pengganti tr:nth-child('+(num+1)+') td:nth-child(2)').html();
    //search all line contain this item_code
    var line = $('#detail-pengganti tr:contains('+item_code+') td:nth-child(6) input:text');
    if(line.length > 0) {
        var total_qty = 0;
        for(i=0;i<line.length;i++) {
            idx = line[i].getAttribute('id');
            temp = $('#'+idx).val();
            if(temp=="") {
                temp = 0;
            }
            else {
                temp = parseFloat(temp);
            }
            total_qty += temp;
        }        
    }
    if(total_qty >= qty) {
        displayNotification('Total qty ganti untuk <b>'+ item_code + '</b>  berlebih, maksimal : '+qty);        
        statusQty = false;
    }
    else {
        statusQty = true;
    }
}

/**
*menghitung jumlah total per item
*/
function countTotal(num) {
    //count for jumlah
    var disc = $('#diskon_'+num).val();
    var qty = $('#qty_'+num).val();
    var harga = $('#harga_'+num).val();
    var total = (harga * (1- (disc/100)))*qty;
    $('#jumlah_'+num).html($.currency(total,{s:".",d:",",c:0})+',-');
    $('#jmlh_'+num).val(total);     
    countAllTotal();    
    countAllWithDisc();
}
/**
*Menghitung total tagihan transaksi
*/
function countAllTotal() {
    //count for all total
    var num = $('#row-data tr:last-child td:first-child').html();
    if(num == null) { 
        num = 0;
    }
    else {
        num = parseInt(num);
    }
    var totalAll = 0;
    for(i=0;i<num;i++){
        totalAll += parseFloat($('#jmlh_'+i).val());        
    }
    $('#total_val').val(totalAll);    
    $('#total').val($.currency(totalAll,{s:".",d:",",c:0})+',-');
}
/**
*Menghitung total setelah di diskon all item
*/
function countAllWithDisc() {
    var disc = $.trim($('#disc_all').val());
    if(disc == "") {
        disc = 0;
    }
    else {
        disc = parseFloat(disc);
    }   
    var total = parseFloat($('#total_val').val());
    var totalWithDisc = total*(1 - (disc/100));
    
    $('#total').val($.currency(totalWithDisc,{s:".",d:",",c:0})+',-'); 
    
    //display total
    var msg = new Array();
    msg[0] = 'Total (Rp)';
    msg[1] = $.currency(totalWithDisc,{s:".",d:",",c:0})+',-';
    setTimeout(displayMsg(formatMsg(msg)),1500);
}
/**
*Fungsi untuk search barang
*/
function searchItem() {
    var key = $('#key').val();
    //make ajax request for retrieve data
    $.post(
        "search_item",
        {keywords: key}, 
        function(data){
            //update ke table row
            if(data == 0) {
                $('#err-key').html('Data tidak ditemukan');
            }
            else {
                //append result
                appendResult(data);
            }
        },
        "json"
    );
}
/**
*Fungsi untuk retrieve data barang
*/
function getItem(option) {
    //ambil id barang
    var id;
    if(option==1) {
        id = $('#barang-tukar').val();
    }
    if(option==2) {
        id = $('#barang-pengganti').val();
    }
    //ajax request ke server
    $.post(
        "get_item",
        {'id_barang': id, 'opsi': option}, 
        function(data){
            //update ke table row
            if(data == 0) {
                if(option==1) {
                    $('#err-tukar').html('Data tidak ditemukan');
                    $('#detail-tukar tr').remove();
                }
                if(option==2) {
                    $('#err-pengganti').html('Data tidak ditemukan');
                    $('#detail-pengganti tr').remove();
                }
            }
            else {
                //tampilkan datanya ke Display
                var msg = new Array();
                msg[0] = data.nama;
                msg[1] = $.currency(data.harga,{s:".",d:",",c:0})+',-';
                displayMsg(formatMsg(msg));
                //append row
                if(option==1) {
                    var row = '<tr class="head"><td style="width:40px">N0</td><td style="width:150px">KODE BARANG</td><td style="width:230px">NAMA BARANG</td><td style="width:170px">HARGA BARANG (Rp)</td><td style="width:90px">STOK BARANG</td><td style="width:90px">QTY</td></tr>';
                    var temp = $('#detail-tukar tr');
                    if(temp.length > 0) {
                        row = '<tr class="row"><td style="text-align:center">'+ temp.length +'</td><td>'+data.id_barang+'</td><td>'+data.nama+'</td><td style="text-align: right">'+$.currency(data.harga,{s:".",d:",",c:0})+',-<input type="hidden" id="harga_tukar_'+temp.length+'" value="'+data.harga+'"/></td><td style="text-align:center" id="stok_tukar">'+ data.stok_barang+'</td><td style="text-align:center"><input type="text" id="qty_refund_'+temp.length+'" style="width:25px" value="1" onkeyup="countRefund();validateQtyRefund('+data.id_barang+','+data.mutasi_keluar+');"/></td></tr>';                    
                    }
                    else {
                       row += '<tr class="row"><td style="text-align:center">'+ 1 +'</td><td>'+data.id_barang+'</td><td>'+data.nama+'</td><td style="text-align: right">'+$.currency(data.harga,{s:".",d:",",c:0})+',-<input type="hidden" id="harga_tukar_'+1+'" value="'+data.harga+'"/></td><td style="text-align:center" id="stok_tukar">'+ data.stok_barang+'</td><td style="text-align:center"><input type="text" id="qty_refund_'+1+'" style="width:25px" value="1" onkeyup="countRefund();validateQtyRefund('+data.id_barang+','+data.mutasi_keluar+');"/></td></tr>';                    
                    }
                    //validate qty                   
                    validateQtyRefund(data.id_barang,data.mutasi_keluar);                    
                    if(statusQty) {
                        $('#detail-tukar').append(row);
                        countRefund();
                    }                    
                }
                if(option==2) {
                    var row = '<tr class="head"><td style="width:40px">N0</td><td style="width:150px">KODE BARANG</td><td style="width:230px">NAMA BARANG</td><td style="width:170px">HARGA BARANG (Rp)</td><td style="width:90px">STOK BARANG</td><td style="width:90px">QTY</td></tr>';
                    var temp = $('#detail-pengganti tr');
                    if(temp.length > 0) {                    
                        row = '<tr class="row"><td style="text-align:center">'+ temp.length +'</td><td>'+data.id_barang+'</td><td>'+data.nama+'</td><td style="text-align: right">'+$.currency(data.harga,{s:".",d:",",c:0})+',-<input type="hidden" id="harga_pengganti_'+temp.length+'" value="'+data.harga+'"/></td><td style="text-align:center" id="stok_pengganti">'+ data.stok_barang+'</td><td style="text-align:center"><input type="text" id="qty_ganti_'+temp.length+'" style="width:25px" value="1" onkeyup="countPengganti();validateQtyGanti('+data.id_barang+','+data.stok_barang+');"/></td></tr>';
                    }
                    else {
                        row += '<tr class="row"><td style="text-align:center">'+ 1 +'</td><td>'+data.id_barang+'</td><td>'+data.nama+'</td><td style="text-align: right">'+$.currency(data.harga,{s:".",d:",",c:0})+',-<input type="hidden" id="harga_pengganti_1" value="'+data.harga+'"/></td><td style="text-align:center" id="stok_pengganti">'+ data.stok_barang+'</td><td style="text-align:center"><input type="text" id="qty_ganti_'+1+'" style="width:25px" value="1" onkeyup="countPengganti();validateQtyGanti('+data.id_barang+','+data.stok_barang+');"/></td></tr>';
                    }                    
                    //validate Qty                    
                    validateQtyGanti(data.id_barang,data.stok_barang);                    
                    if(statusQty) {
                        $('#detail-pengganti').append(row);
                        countRefund();
                        countPengganti();
                    }
                }                
            }
        },
        "json"
    );        
}
/**
*count refund
*/
function countRefund() {
    //hitung semua barang yang direfund
    var temp = $('#detail-tukar tr');
    var total_tukar = 0;
    for(i=1;i<temp.length;i++) {
        harga = parseFloat($('#harga_tukar_'+i).val());
        qty = $('#qty_refund_'+i).val();
        if(qty == "") {
            qty = 0;
        }
        else {
            qty = parseFloat(qty);
        }
        disc = parseFloat($('#disc_refund').val());
        total_tukar += (harga *(1 - disc/100)* qty);         
    }  
    $('#total_tukar').val(total_tukar);
    displayRefundInfo();
}
/**
*count pengganti
*/
function countPengganti() {
    var temp = $('#detail-pengganti tr');
    var total_ganti = 0;
    for(i=1;i<temp.length;i++) {
        harga = parseFloat($('#harga_pengganti_'+i).val());
        qty = $('#qty_ganti_'+i).val();
        if(qty == "") {
            qty = 0;
        }
        else {
            qty = parseFloat(qty);
        }
        disc = parseFloat($('#disc_refund').val());
        total_ganti += (harga *(1 - disc/100)* qty);        
    }    
    $('#total_pengganti').val(total_ganti);
    displayRefundInfo();
}
/**
*display refund message
*/
function displayRefundInfo() {
    if($('#detail-tukar tr').length > 0 && $('#detail-pengganti tr').length > 0) {
        var total_tukar = parseFloat($('#total_tukar').val());
        var total_pengganti = parseFloat($('#total_pengganti').val());
        var kurang = total_pengganti - total_tukar;
        var sisa = total_tukar - total_pengganti;
        kurang = Math.floor(kurang/100) * 100;
        
        if(total_pengganti >= total_tukar) {
            $('#button-refund').css('display','block');                            
            $('#kurang-bayar').css('display','block');
            $('#kurang-bayar').html('Kurang : Rp '+$.currency(kurang,{s:".",d:",",c:0}) + ',- <input type="hidden" id="bill-refund" value="'+kurang+'"/>');            
        }
        else {            
            $('#button-refund').css('display','none');
            $('#kurang-bayar').css('display','block'); 
            $('#kurang-bayar').html('Sisa : Rp '+ $.currency(kurang,{s:".",d:",",c:0}) + ',-');
        }
        //display to PD Series
        var msg = new Array();
        msg[0] = 'Kurang (Rp)';
        msg[1] = $.currency(kurang,{s:".",d:",",c:0}) + ',-';
        displayMsg(formatMsg(msg));
    }
}
/**
*Kasih tanda ratusan / ribuan
*/
function viewCash(opsi) {
    if(opsi ==  1) {
        var cash = $('#trans-cash').val();
    }
    if(opsi == 2) {
        var cash = $('#refund-cash').val();
    }
    if(cash == "") {
        cash = 0;
    }
    else {
        cash = parseFloat(cash);
    }
    if(opsi == 1)    
        $('#cash-view').html($.currency(cash,{s:".",d:",",c:0})+',-');
    if(opsi ==  2)
        $('#refund-view').html($.currency(cash,{s:".",d:",",c:0})+',-');
}
/**
*pindah cursor setelah 4 digit, untuk entri nomor kartu kredit
*/
function moveCursor(opsi) {
    var cc_num = $('#cc_num_'+opsi).val();    
    if(opsi == 4) {
        if(cc_num.length == 4) {
            $('.ui-button').focus();
        }
    }
    else {
        if(cc_num.length == 4) {
            opsi++;
            $('#cc_num_'+opsi).focus();
        }
    }
}
/**
*Fungsi untuk merubah status focus jadi false pada input search
*/
function removeFocus(){
    searchFocus=false;   
}
/**
*Fungsi untuk mengubah status focus jadi true pada input search
*/
function setFocus(){
    searchFocus=true;    
}
