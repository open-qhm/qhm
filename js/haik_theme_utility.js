$(function(){
  if ($("#msg").length > 0) {
    window.addEventListener("message", function(ev) {
      if (ev.data.message === "isOpenerEditable") {
        ev.source.postMessage({message: "openerIsEditable"}, "*");
      }
      else if (ev.data.message === "textareaClicked") {
        $.clickpad.cpInsert(ev.data.insertText);
        ev.source.postMessage({message: "insertedText"}, "*");
      }
      else if (ev.data.message === "sendCode") {
        $.clickpad.cpInsert(ev.data.code);
        tb_remove();
      }
    });    
  }
});
