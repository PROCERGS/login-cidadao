$(function() {

  var preview = false;
  $("#btn-preview").on("click", function() {

    if (preview) {
      var template = $("#hidden-template").html();
      $("#notification-preview").html(template);
    }
    $("#notification-preview *").replaceText('%title%', $('#broadcast_title').val());
    $("#notification-preview *").replaceText('%shorttext%', $('#broadcast_shortText').val());    

    $(".placeholder").each(function() {
      var label = $(this).find(".label").data("placeholder");
      var val = $(this).find(".value").val();

      $("#notification-preview *").replaceText('%'+label+'%', val);
    });

    var templateNew = $("#notification-preview").html();
    $("#broadcast_template").val(templateNew);
    preview = true;
  });

});