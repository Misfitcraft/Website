
function onresponse(data)
{
  var safedata = escapehtml(data); //If we don't escape it we could be vulnerable to XSS attacks
  
  var motd;
  
  var statusspans = document.getElementsByClassName("upstatus");
  var motdspans = document.getElementsByClassName("servermotd");
  var pspans = document.getElementsByClassName("serverplayers");
  var vspans = document.getElementsByClassName("serverversion");
  var ipparas = document.getElementsByClassName("serverip");
  
  var online = true;
  
  var motd = "N/A", maxplayers = "N/A", onlineplayers = "0", version = "1.12";
  
  if(safedata !== "Server down")
  {
    var splitdata = safedata.split("|");
    
    if(splitdata.length >= 4)
    {
      motd = splitdata[0], maxplayers = splitdata[1], onlineplayers = splitdata[2], version = splitdata[3];
    }
  }
  else
  {
    online = false;
  }
  
  for (var i = 0; i < statusspans.length; i++)
  {
    statusspans[i].innerText = online ? "online" : "offline";
    statusspans[i].style.color = (!online ? "red" : "#1aff00");
  }
  
  if(!online)
  {
    return;
  }
  
  for (var i = 0; i < motdspans.length; i++)
  {
    motdspans[i].innerText = motd;
  }
  
  for (var i = 0; i < pspans.length; i++)
  {
    pspans[i].innerText = onlineplayers + "/" + maxplayers;
  }
  
  for (var i = 0; i < vspans.length; i++)
  {
    vspans[i].innerText = version;
  }
  
  for (var i = 0; i < ipparas.length; i++)
  {
    statusboxes[i].style.color = (!online ? "red" : "");
  }
}

httpget("./php/status.php", onresponse, function(x){console.log("Status update HTTP error " + x);});
