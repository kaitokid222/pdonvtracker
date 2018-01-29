$(document).ready(function(){
    $("#creator_show_button").click(function(){
        if($("#creator").is(":visible")){
            $("#creator").hide();
        }else{
            $("#creator").show();
            $("#creator_show_button").hide();
        }
    });

    $("#immer").click(function(){
		if(document.getElementById("immer").value == "Zurück"){
			document.getElementById("creator_input_date").value = "";
			document.getElementById("immer").value = "Immer gültig";
		}else{
			document.getElementById("creator_input_date").value = "0000-00-00";
			document.getElementById("immer").value = "Zurück";
		}
    });

    $("#plus").click(function(){
		var cval = Number(document.getElementById("creator_input_value").value);
		var nval = cval + 100;
		document.getElementById("creator_input_value").value = nval.toString();
    });
});