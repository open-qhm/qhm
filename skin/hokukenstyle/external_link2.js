function external_link2(){
   var host_Name = location.hostname;
   var host_Check;
   var link_Href;

   for(var i=0; i < document.links.length; ++i)
   {
       link_Href = document.links[i].host;
       host_Check = link_Href.indexOf(host_Name,0);

       if(host_Check == -1){
           document.links[i].target="newwindow";
       }

   }
}
window.onload = external_link2;