$(document).ready(function() {

  /* Formats time inputs */
  $('.groupInput').toArray().forEach(function(select) {
    new Cleave(select, {
      time: true,
      timePattern: ['h', 'm']
    })
  })

  /* Checks to see if inputs have value */
  $('.groupInput').on('change keyup', function() {
    checkInput(this);

    /* Calculates suggested clock out time if clock in and hours are set */
    if ($('#clockIn').val().length == 5 && $('#work').val().length == 5) {
      calcClockOut();
    }

    /* Calculates break time if break start and break end are set */
    if ($('#breakIn').val().length == 5 && $('#breakOut').val().length == 5) {
      calcBreak();
    }

    /* Calculates work time if clock in and clock out are set */
    if ($('#clockIn').val().length == 5 && $('#clockOut').val().length == 5) {
      calcWork();
    }

  });

  /* If a table row of a day is selected, submit the clock in times for that day if the form is filled */
  $('.clockTable > tbody > tr').click(function () {
    $('#date').val($(this).attr('id'));
    $('#day').val($(this).find('td:first').html());
    
    if ($('#clockIn').val().length == 5 && $('#work').val().length == 5 && $('#clockOut').val().length == 5) {
      $('#clockForm').submit();
    }
  });

});

/* Gives inputs with values in them a minified label */
function checkInput(element) {
  const $label = $(element).siblings('.groupLabel');

  if ($(element).val()) {
    $label.addClass('labelMini');
  }
  else {
    $label.removeClass('labelMini');
  }
}

/* Calculates suggested clock out time */
function calcClockOut() {
  var clockIn   = $('#clockIn').val();
  var work      = $('#work').val();
  var breakTime = $('#break').val();
  var hourIn    = parseInt(clockIn.slice(0, 2));
  var minIn     = parseInt(clockIn.slice(-2));
  var workHr    = parseInt(work.slice(0, 2));
  var workMin   = parseInt(work.slice(-2));
  var breakHr   = parseInt(breakTime.slice(0, 2));
  var breakMin  = parseInt(breakTime.slice(-2));
  
  if (!breakTime) {
    breakHr = 0;
    breakMin  = 0;
  }

  var hourOut1 = hourIn + workHr + breakHr;
  var hourOut2 = hourOut1;
  var minOut1  = minIn + workMin + breakMin;
  var minOut2  = minOut1 + 10;

  while (minOut1 >= 60) {
    hourOut1 += 1;
    minOut1  -=60;
  }

  while (minOut2 >= 60) {
    hourOut2 += 1;
    minOut2  -= 60;
  }

  if (hourOut1 < 10) { hourOut1 = '0' + hourOut1; }
  if (minOut1 < 10)  { minOut1  = '0' + minOut1; }
  if (hourOut2 < 10) { hourOut2 = '0' + hourOut2; }
  if (minOut2 < 10)  { minOut2  = '0' + minOut2; }

  $('#clockOut').siblings('.suggest').html('Suggested: ' + hourOut1 + ':' + minOut1 + ' - ' + hourOut2 + ':' + minOut2);
  $('#clockOut').siblings('.suggest').css('visibility', 'visible');
}

/* Calculates break time */
function calcBreak() {
  var breakIn  = $('#breakIn').val();
  var breakOut = $('#breakOut').val();
  var hourIn   = parseInt(breakIn.slice(0, 2));
  var minIn    = parseInt(breakIn.slice(-2));
  var hourOut  = parseInt(breakOut.slice(0, 2));
  var minOut   = parseInt(breakOut.slice(-2));
  var breakHr  = hourOut - hourIn;
  var breakMin = minOut - minIn;

  while(breakMin < 0) {
    breakHr  -= 1;
    breakMin += 60;
  }

  if (breakHr < 10)  { breakHr  = '0' + breakHr; }
  if (breakMin < 10) { breakMin = '0' + breakMin; }

  $('#break').val(breakHr + ':' + breakMin);
  $('#break').siblings('.groupLabel').addClass('labelMini');

  if ($('#clockIn').val().length == 5 && $('#work').val().length == 5) {
    calcClockOut();
  }

  if ($('#clockIn').val().length == 5 && $('#clockOut').val().length == 5) {
    calcWork();
  }
}

/* Calculates work time */
function calcWork() {
  var clockIn   = $('#clockIn').val();
  var clockOut  = $('#clockOut').val();
  var breakTime = $('#break').val();
  var hourIn    = parseInt(clockIn.slice(0, 2));
  var minIn     = parseInt(clockIn.slice(-2));
  var hourOut   = parseInt(clockOut.slice(0, 2));
  var minOut    = parseInt(clockOut.slice(-2));
  var breakHr   = parseInt(breakTime.slice(0, 2));
  var breakMin  = parseInt(breakTime.slice(-2));

  if (!breakTime) {
    breakHr  = 0;
    breakMin = 0;
  }

  var workHr  = hourOut - hourIn - breakHr;
  var workMin = minOut - minIn - breakMin;

  while(workMin < 0) {
    workHr  -= 1;
    workMin += 60;
  }

  if (workHr < 10)  { workHr  = '0' + workHr; }
  if (workMin < 10) { workMin = '0' + workMin; }

  $('#work').val(workHr + ':' + workMin);
  $('#work').siblings('.groupLabel').addClass('labelMini');
}