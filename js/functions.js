var alapMainHttp = "http://localhost/creator/";

function logout(){
    window.location=alapMainHttp + "logout.php";
}

function headerLoader(str){
    document.loadHeader.headerLoaderInp.value=str;
    document.loadHeader.submit();
}

function ajaxLoader(){
    
    /*$.post(alapMainHttp + "control.php", {"loadPage":str}, function(result, status){
        
         if (result.indexOf("Fatal error") > 0){
            alert("Fatal coding error!");
            alert(result);
        }else if (result.indexOf("error") > 0){
            alert("Possible error, logged, please recheck.");
        }else if(status == "success"){
            
        }else{
            alert("Unknown error, logged.");
        }
    });*/
}