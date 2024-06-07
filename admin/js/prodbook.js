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
			cell.innerHTML='<input size="2" style="width:30px" style="background:#EBEBD5" type="text" name="pieces[]" value="'+pieces+'" id="pieces_'+row_num+'">';
			
			var cell = row.insertCell(1);
			cell.innerHTML='<select name="packaging_list_id[]" style="background:#EBEBD5"><option value="0"></option>'+[packaging_list]+'</select>';

			var cell = row.insertCell(2);
			cell.innerHTML='<nobr><input size="1" style="width:30px" type="text" name="dim_d[]" value="'+dim_d+'" id="dim_d_'+row_num+'"> x <input size="1" type="text" style="width:30px" name="dim_w[]" value="'+dim_w+'" id="dim_w_'+row_num+'"> x <input size="1" style="width:30px" type="text" name="dim_h[]" value="'+dim_h+'" id="dim_h_'+row_num+'"></nobr>';

			var cell = row.insertCell(3);
			cell.innerHTML='<select name="class_list_id[]" id="class_list_id_'+row_num+'"><option value="0"></option>'+class_list+'</select>';

			var cell = row.insertCell(4);
			cell.innerHTML='<input size="8" type="text" name="nmfc[]" value="'+nmfc+'" id="nmfc_'+row_num+'">';

			var cell = row.insertCell(5);
			cell.innerHTML='<input size="60" type="text" name="description[]" value="'+description+'" style="background:#EBEBD5">';

			var cell = row.insertCell(6);
			cell.innerHTML='<input size="8" type="text" style="text-align:right" style="background:#EBEBD5" name="weight[]" value="'+weight+'"> lbs';

			var cell = row.insertCell(7);
			cell.innerHTML='<input type="button" value="X" onclick="removeCommodityRow(\''+row_id+'\')">';

			document.getElementById('pieces_'+row_num).focus();

		}
	}
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