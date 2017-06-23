
function update()
{
  if($(".navbutton:hover").length > 0)
  {
    $("#underline").css({
      left: $(".navbutton:hover").offset().left + 25,
      width: $(".navbutton:hover").width() - 50
    });
  }
  else
  {
    $("#underline").css({
      left: $(".selected:first").offset().left,
      width: $(".selected:first").width()
    });
  }
}

window.setInterval(update, 300);
