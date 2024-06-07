window.onload=runScripts;

function runScripts(){
	setServiceReq();
}

function setCommodity(id){
	removeAllCommodityRows();

	var url = "/?action=quote_prodbook&id="+id;

	if(xmlhttp && xmlhttp.readyState != 0){
	xmlhttp.abort()
	}
	xmlhttp=getXMLHTTP();
	if(xmlhttp){
	xmlhttp.open("GET",url,true);
	xmlhttp.onreadystatechange=function() {
	  if(xmlhttp.readyState==4 && xmlhttp.responseText){			
		 eval(xmlhttp.responseText);
	  }
	}
	xmlhttp.send(null);
	}	
}

var xmlhttp;
function getXMLHTTP(){
  var ret=null;
  try{
	ret=new ActiveXObject("Msxml2.XMLHTTP");
  }catch(e){
	try{
	  ret=new ActiveXObject("Microsoft.XMLHTTP");
	} catch(oc){
	  ret=null;
	}
  }
  if(!ret && typeof XMLHttpRequest != "undefined"){
	ret=new XMLHttpRequest();
  }
  return ret;
}

function checkSpecial(){
	document.getElementById("special_pickup_mssg").style.display='none';
	document.getElementById("special_delivery_mssg").style.display='none';

	pu_s = document.getElementById("req_pickup_time_start").value;
	times1 = pu_s.split(":");
	pu_e = document.getElementById("req_pickup_time_end").value;
	times2 = pu_e.split(":");


	diff = (((times2[0]*3600)+(times2[1]*60))-((times1[0]*3600)+(times1[1]*60)))/3600;
	if(diff <= 2){
		document.getElementById("special_pickup_mssg").style.display='block';
	}
	
	del_s = document.getElementById("req_delivery_time_end").value;
	times = del_s.split(":");
	if(times[0] < 13){
		document.getElementById("special_delivery_mssg").style.display='block';
	}
}

function setServiceReq(){
	if(!document.getElementById("service_checked")){
		var tbl = document.getElementById("commodityTable");
		if(tbl){
			var ii = tbl.rows.length;
			for(i=0;i<=ii;i++){
				var trow=tbl.rows[i];				
				if(trow && trow.id){
					parts=trow.id.split("_");
					if(parts[1]){
						document.getElementById("dim_d_"+parts[1]).style.background=(getRequired('dim_d'))?"#EBEBD5":"#FFFFFF";
						document.getElementById("dim_w_"+parts[1]).style.background=(getRequired('dim_w'))?"#EBEBD5":"#FFFFFF";
						document.getElementById("dim_h_"+parts[1]).style.background=(getRequired('dim_h'))?"#EBEBD5":"#FFFFFF";
						document.getElementById("class_list_id_"+parts[1]).style.background=(getRequired('class_list_id'))?"#EBEBD5":"#FFFFFF";
						//document.getElementById("nmfc_"+parts[1]).style.background=(getRequired('nmfc'))?"#EBEBD5":"#FFFFFF";
	
						calcDimWeight(parts[1]);
					}
				}
			}			
		}
	}
}

function getRequired(field){
	if(!document.getElementById("service_checked")){
		var air_checked=(document.getElementById("service_air").checked)?true:false;
		var ground_checked=(document.getElementById("service_ground").checked)?true:false;
		switch(field){
			case "dim_d":
			case "dim_w":
			case "dim_h":
				if(air_checked){return true}
				if(ground_checked){return false}
				break;
			case "class_list_id":
			//case "nmfc":
				if(air_checked){return false}
				if(ground_checked){return true}
				break;
		}
	}
}

function zipFinder(mode){
	window.open("/?action=zipfinder&"+mode,"zip_finder","top=0,left=0,width=450,height=300,scrollbars=yes,menu=no,resizable=yes");
}

function updateZipOrigin(city,state,zip){
	document.getElementById("o_city").value=city;
	document.getElementById("o_state").value=state;
	document.getElementById("o_zip").value=zip;
}

function updateZipDestination(city,state,zip){
	document.getElementById("d_city").value=city;
	document.getElementById("d_state").value=state;
	document.getElementById("d_zip").value=zip;
}

function setAddress(val,mode){
	if(val){
		parts=val.split("~|~");
		if(parts){
			document.getElementById(mode+"_to").value=parts[0];
			document.getElementById(mode+"_address1").value=parts[1];
			document.getElementById(mode+"_address2").value=parts[2];
			document.getElementById(mode+"_city").value=parts[3];
			document.getElementById(mode+"_state").value=parts[4];
			document.getElementById(mode+"_zip").value=parts[5];
			document.getElementById(mode+"_contact_name").value=parts[6];
			document.getElementById(mode+"_contact_phone").value=parts[7];
			document.getElementById(mode+"_contact_fax").value=parts[8];
			document.getElementById(mode+"_contact_email").value=parts[9];

			document.getElementById(mode+"_addressbook").selectedIndex=0;
		}
	}
}

function calcDimWeight(row_num){
	var dim_factor=194;
	if(document.getElementById("service_ground").checked){
		var dim_factor=250;
	}

	dim_weight=0;
	pieces=document.getElementById("pieces_"+row_num).value;
	dim_d=document.getElementById("dim_d_"+row_num).value;
	dim_w=document.getElementById("dim_w_"+row_num).value;
	dim_h=document.getElementById("dim_h_"+row_num).value;
	if(pieces && dim_d && dim_w && dim_h){
		volweight = parseInt(pieces) * (parseInt(dim_d) * parseInt(dim_w) * parseInt(dim_h));
		dim_weight=(volweight)?Math.ceil(volweight/dim_factor):0;		
	}
	document.getElementById("dim_weight_"+row_num).value=dim_weight;
}

function addCommodityRow(pieces,packaging_list_id,dim_d,dim_w,dim_h,class_list_id,nmfc,description,weight){
	if(!pieces){pieces='';}
	if(!packaging_list_id){packaging_list_id='';}
	if(!dim_d){dim_d='';}
	if(!dim_w){dim_w='';}
	if(!dim_h){dim_h='';}
	if(!class_list_id){class_list_id='';}
	if(!nmfc){nmfc='';}
	if(!description){description='';}
	if(!weight){weight='';}

	var tbl = document.getElementById("commodityTable");
	if(tbl){
		var x = 0;
		var ii = tbl.rows.length;
		for(i=0;i<=ii;i++){
			var trow=tbl.rows[i];				
			if(trow && trow.id && trow.id.substring(0,4)=="row_"){
				x = trow.id.substring(4);
			}
		}
		x++;
		row_id = "row_"+x;
		row_num = x;

		var row = tbl.insertRow(tbl.rows.length);
		if(row){
			row.id = row_id;

			var cell = row.insertCell(0);
			cell.innerHTML='<input size="2" style="width:30px" style="background:#EBEBD5" type="text" name="pieces[]" value="'+pieces+'" id="pieces_'+row_num+'" onchange="calcDimWeight('+row_num+')">';
			
			var cell = row.insertCell(1);
			cell.innerHTML='<select name="packaging_list_id[]" id="packaging_list_id_'+row_num+'" style="background:#EBEBD5"><option value="0"></option>'+[packaging_list]+'</select>';

			if(packaging_list_id){
				var obj = document.getElementById("packaging_list_id_"+row_num);
				if(obj){
					ii=obj.options.length;
					for(i=0;i<ii;i++){
						if(obj.options[i].value == packaging_list_id){
							obj.options[i].selected=true;
							break;
						}
					}
				}
			}

			var cell = row.insertCell(2);
			cell.innerHTML='<nobr><input size="1" style="width:30px" type="text" name="dim_d[]" value="'+dim_d+'" id="dim_d_'+row_num+'" onchange="calcDimWeight('+row_num+')"> x <input size="1" type="text" style="width:30px" name="dim_w[]" value="'+dim_w+'" id="dim_w_'+row_num+'" onchange="calcDimWeight('+row_num+')"> x <input size="1" style="width:30px" type="text" name="dim_h[]" value="'+dim_h+'" id="dim_h_'+row_num+'" onchange="calcDimWeight('+row_num+')"></nobr>';

			var cell = row.insertCell(3);
			cell.innerHTML='<select name="class_list_id[]" id="class_list_id_'+row_num+'"><option value="0"></option>'+class_list+'</select>';

			if(class_list_id){
				var obj = document.getElementById("class_list_id_"+row_num);
				if(obj){
					ii=obj.options.length;
					for(i=0;i<ii;i++){
						if(obj.options[i].value == class_list_id){
							obj.options[i].selected=true;
							break;
						}
					}
				}
			}

			var cell = row.insertCell(4);
			cell.innerHTML='<input size="8" type="text" name="nmfc[]" value="'+nmfc+'" id="nmfc_'+row_num+'">';

			var cell = row.insertCell(5);
			cell.innerHTML='<input size="60" type="text" name="description[]" value="'+description+'" style="background:#EBEBD5">';

			var cell = row.insertCell(6);
			cell.innerHTML='<input size="8" type="text" style="text-align:right" style="background:#EBEBD5" name="weight[]" value="'+weight+'"> lbs';

			var cell = row.insertCell(7);
			cell.innerHTML='<input size="8" type="text" style="text-align:right;border:0px;" name="dim_weight[]" value="0" id="dim_weight_'+row_num+'" readonly tabindex="9999"> lbs';

			var cell = row.insertCell(8);
			cell.innerHTML='<input type="button" value="X" onclick="removeCommodityRow(\''+row_id+'\')">';

			document.getElementById('pieces_'+row_num).focus();

			calcDimWeight(row_num)
		}
	}
	setServiceReq();
}

function removeCommodityRow(row_id){
	var tbl = document.getElementById("commodityTable");
	if(tbl){
		var ii = tbl.rows.length;
		for(i=0;i<=ii;i++){
			var trow=tbl.rows[i];		
			if(trow && trow.id && trow.id==row_id){
				tbl.deleteRow(i);
			}
		}			
	}
}

function removeAllCommodityRows(){
	var tbl = document.getElementById("commodityTable");
	if(tbl){
		i=tbl.rows.length-1;		
		while(i){
			tbl.deleteRow(i);
			i=tbl.rows.length-1;
		}		
	}
}
