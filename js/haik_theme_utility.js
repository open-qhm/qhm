$(function(){
  if ($("#msg").length > 0) {
    window.addEventListener("message", function(ev) {
      if (ev.data.message === "isOpenerEditable") {
        ev.source.postMessage({message: "openerIsEditable"}, "*");
      }
      else if (ev.data.message === "textareaClicked") {
        window.insertText(null, ev.data.insertText);
        ev.source.postMessage({message: "insertedText"}, "*");
      }
      else if (ev.data.message === "sendCode") {
        window.insertText(null, ev.data.code);
        tb_remove();
      }
    });    
  }
});
