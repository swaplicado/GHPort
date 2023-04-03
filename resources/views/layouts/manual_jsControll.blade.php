<script>
    var arrIncludes = document.getElementsByClassName('manual');
    
    for(var i = 0 ; arrIncludes.length > i ; i++){

        arrIncludes[i].setAttribute('href', oServerData.manualRoute[i]);    
    }
</script>