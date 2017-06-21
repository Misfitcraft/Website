
function httpget(url, callback, errorcallback)
{
    var http = new XMLHttpRequest();
    
    http.onreadystatechange = function()
    {
      if (this.readyState == 4 && this.status == 200)
      {
       callback(http.responseText);
      }
      else if(this.readyState == 4)
      {
        errorcallback(this.status);
      }
    };
    
    http.open("GET", url);
    http.send();
}
 
 function escapehtml(string)
 {
   return string.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;").replace("\"", "&quot;").replace("\'", "&apos;");
 }
