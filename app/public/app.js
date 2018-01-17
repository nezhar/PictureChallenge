$(function () {
  $(document).on({
    ajaxStart: function() { $('body').addClass("loading");    },
    ajaxStop: function() { $('body').removeClass("loading"); }
  })


  $( "#request_form" ).submit(function( event ) {
    event.preventDefault();

    $('.results_container').slideUp( "slow");
    $.post( "/call", { challenge_number: $("#challenge_number").val(), weeks: $("#weeks").val() }).done(function( data ) {
        $('.results_container .result').html('');

        if (data) {
          $.each(data, function(key, value) {
              $('.results_container .results').append("<div class='result'>"+value+"</div>");
          });
        } else {
          $('.results_container .results').append("<div class='result'>None</div>");
        }

        $('.results_container').slideDown( "slow");
    });

  });

});
