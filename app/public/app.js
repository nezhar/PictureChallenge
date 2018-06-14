$(function () {
  $(document).on({
    ajaxStart: function() { $('body').addClass("loading");    },
    ajaxStop: function() { $('body').removeClass("loading"); }
  })

  $( "#request_form" ).submit(function( event ) {
    event.preventDefault();

    $('.results_container').slideUp( "slow");
    $.post( "/call", {
      challenge_number: $("#challenge_number").val(),
      start_date: $("#daterange").data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm') + ' -05:00',
      end_date: $("#daterange").data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm') + ' -05:00',
    }).done(function( data ) {
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
  
  var start = moment().subtract(7, 'days').hour('12').minute('0');
  var end = moment().hour('12').minute('0');

  $("#daterange").daterangepicker({
    timePicker: true,
    startDate: start,
    endDate: end,
    locale: {
      format: 'DD/MM/YYYY hh:mm A',
    },
    timeZone: 'est',
    ranges: {
      'Last 7 Days': [moment().subtract(7, 'days').hour('12').minute('0'), moment().hour('12').minute('0')],
      'Last 30 Days': [moment().subtract(30, 'days').hour('12').minute('0'), moment().hour('12').minute('0')],
   },
   alwaysShowCalendars: true,
   function(start, end) {
    console.log("Callback has been called!");
    startDate = start;
    endDate = end;    
   }
  });

});