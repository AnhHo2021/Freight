function round_decimals(original_number, decimals) {
    var result1 = original_number * Math.pow(10, decimals)
    var result2 = Math.round(result1)
    var result3 = result2 / Math.pow(10, decimals)
    return pad_with_zeros(result3, decimals)
}

function pad_with_zeros(rounded_value, decimal_places) {

    // Convert the number to a string
    var value_string = rounded_value.toString()
    
    // Locate the decimal point
    var decimal_location = value_string.indexOf(".")

    // Is there a decimal point?
    if (decimal_location == -1) {
        
        // If no, then all decimal places will be padded with 0s
        decimal_part_length = 0
        
        // If decimal_places is greater than zero, tack on a decimal point
        value_string += decimal_places > 0 ? "." : ""
    }
    else {

        // If yes, then only the extra decimal places will be padded with 0s
        decimal_part_length = value_string.length - decimal_location - 1
    }
    
    // Calculate the number of decimal places that need to be padded with 0s
    var pad_total = decimal_places - decimal_part_length
    
    if (pad_total > 0) {
        
        // Pad the string with 0s
        for (var counter = 1; counter <= pad_total; counter++) 
            value_string += "0"
        }
    return value_string
}
	
function intOnly(i) {
	if(i.value.length>0) {
		i.value = i.value.replace(/[^\d]+/g, ''); 
	}
}

function formatPhone(t){
	var v = t.value;
	var anum=/(^\d+$)/
	var out="",str=new Array(),ii=v.length,vv;
	for(i=0,c=0;i<ii;i++){
		vv=v.substr(i,1);
		if(anum.test(vv)){
			str[c]=vv;
			c++;
		}
	}
	ii=str.length;
	for(i=0;i<ii;i++){
		switch(i){
			case 3:out+="-";break;
			case 6:out+="-";break;
		}
		out+=str[i];
	}
	if(out.length > 12){out=out.substring(0,12);}
	t.value = out;
}
function NumberFormat (obj, decimal) {
	//decimal  - the number of decimals after the digit from 0 to 3
	//-- Returns the passed number as a string in the xxx,xxx.xx format.
	  // anynum=eval(obj.value);
	   anynum=obj;
	   divider =10;
	   switch(decimal){
			case 0:
				divider =1;
				break;
			case 1:
				divider =10;
				break;
			case 2:
				divider =100;
				break;
			default:  	 //for 3 decimal places
				divider =1000;
		}

	   workNum=Math.abs((Math.round(anynum*divider)/divider));

	   workStr=""+workNum

	   if (workStr.indexOf(".")==-1){workStr+="."}

	   dStr=workStr.substr(0,workStr.indexOf("."));dNum=dStr-0
	   pStr=workStr.substr(workStr.indexOf("."))

	   while (pStr.length-1< decimal){pStr+="0"}

	   if(pStr =='.') pStr ='';

	   //--- Adds a comma in the thousands place.    
	   if (dNum>=1000) {
		  dLen=dStr.length
		  dStr=parseInt(""+(dNum/1000))+","+dStr.substring(dLen-3,dLen)
	   }

	   //-- Adds a comma in the millions place.
	   if (dNum>=1000000) {
		  dLen=dStr.length
		  dStr=parseInt(""+(dNum/1000000))+","+dStr.substring(dLen-7,dLen)
	   }
	   retval = dStr + pStr
	   //-- Put numbers in parentheses if negative.
	   if (anynum<0) {retval="("+retval+")";}

	  
	//You could include a dollar sign in the return value.
	  //retval =  "$"+retval
	  
	  //obj.value = retval;
	  return retval;
 }

function formatCurrency(num){
	num = num.toString().replace(/\$|\,/g,'');
	if(isNaN(num))
	num = "0";
	sign = (num == (num = Math.abs(num)));
	num = Math.floor(num*100+0.50000000001);
	cents = num%100;
	num = Math.floor(num/100).toString();
	if(cents<10)
	cents = "0" + cents;
	for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
	num = num.substring(0,num.length-(4*i+3))+','+
	num.substring(num.length-(4*i+3));
	return (((sign)?'':'-') + num + '.' + cents);
}

function formatInts(t){
	var patt = /(\d*)\.{1}(\d{0,2})/;
	var donepatt = /^(\d*)\.{1}(\d{2})$/;
	var str = t.value;
	var result;
	if (!str.match(donepatt)){
		result = str.match(patt);
		if (result!= null){
			t.value = t.value.replace(/[^\d]/gi,'');
			str = result[1] + '.' + result[2];
			t.value = str;
		}else{
			if (t.value.match(/[^\d]/gi))t.value = t.value.replace(/[^\d]/gi,'');
		}
	}
}
function disableEnterKey(e){
	 var key;

	 if(window.event)
		  key = window.event.keyCode;     //IE
	 else
		  key = e.which;     //firefox

	 if(key == 13)
		  return false;
	 else
		  return true;
}
