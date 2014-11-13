$(function() {

  var preview = false;
  $("#btn-preview").on("click", function() {

    if (preview) {
      var template = $("#hidden-template").html();
      $("#notification-preview").html(template);
    }

    $(".placeholder").each(function() {
      var label = $(this).find(".label").data("placeholder");
      var val = $(this).find(".value").val();

      $("#notification-preview *").replaceText(label, val);
    });

    var templateNew = $("#notification-preview").html();
    $("#broadcast_template").val(templateNew);
    preview = true;
  });

});