<!DOCTYPE html>
<html>
  
  <?php include 'inc/header.php' ?>

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clockinator</title>

    <!-- Custom Style -->
    <link rel="stylesheet" href="css/main.css">

    <!-- Font Awesome -->
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

    <!-- Cleave.js -->
    <script src="https://nosir.github.io/cleave.js/dist/cleave.min.js"></script>

    <!-- Custom Scripts -->
    <script src="js/main.js"></script>
  </head>

  <body>

    <!-- Main Container -->
    <div class="container">
      <h1 class="title">Clockinator</h1>

      <!-- Main Form -->
      <form id="clockForm" action="inc/clockIn.php" method="post" autocomplete="off">

        <!-- Work Hours Inputs -->
        <div class="row">

          <div class="inputGroup">
            <input type="text" name="clockIn" id="clockIn" class="groupInput" maxlength="5" required>
            <label for="clockIn" class="groupLabel">Clock In</label>
          </div>

          <div class="inputGroup">
            <input type="text" name="work" id="work" class="groupInput">
            <label for="work" class="groupLabel">Hours</label>
          </div>

          <div class="inputGroup">
            <input type="text" name="clockOut" id="clockOut" class="groupInput" maxlength="5" required>
            <label for="clockOut" class="groupLabel">Clock Out</label>
            <span class="suggest">Suggested: 00:00 - 00:00</span>
          </div>

        </div>
        <!-- Work Hours Inputs End -->

        <!-- Break Hours Input -->
        <div class="row">

          <div class="inputGroup">
            <input type="text" name="breakIn" id="breakIn" class="groupInput" maxlength="5">
            <label for="breakIn" class="groupLabel">Break Start</label>
          </div>

          <div class="inputGroup">
            <input type="text" name="breakOut" id="breakOut" class="groupInput" maxlength="5">
            <label for="breakOut" class="groupLabel">Break End</label>
          </div>

          <div class="inputGroup">
            <input type="text" name="break" id="break" class="groupInput">
            <label for="break" class="groupLabel">Break Length</label>
          </div>

        </div>
        <!-- Break Hours Input End -->

        <input type="hidden" name="date" id="date">
        <input type="hidden" name="day" id="day">
      </form>
      <!-- Form End -->

      <?php
      $date  = strtotime(date('Y-m-d'));
      $week  = (date('w', $date) == 0) ? $date : strtotime('last sunday', $date);
      $start = date('Y-m-d', $week);
      $end   = date('Y-m-d', strtotime('next saturday', $week));

      $year = date('o', $date);
      $week = date('W', $date);

      $dates = array();
      for ($i = 1; $i <= 5; $i++) {
        $date = strtotime($year . 'W' . $week . $i);
        $day = date('l', $date);
        $dates[$day] = date('Y-m-d', $date);
      }

      $query = "
        SELECT clockid, clockin, clockout, work, break, clockdate, day
        FROM clock WHERE clockdate > $1 AND clockdate < $2
        ORDER BY clockdate
      ";
      $params = [$start, $end];
      $result = pg_query_params($db, $query, $params);

      $days     = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday');
      $tbody    = array();
      $totalHr  = 0;
      $totalMin = 0;

      while ($row = pg_fetch_array($result)) {
        $clockId  = $row['clockid'];
        $clockIn  = $row['clockin'];
        $clockOut = $row['clockout'];
        $work     = $row['work'];
        $break    = $row['break'];
        $day      = $row['day'];
        if (empty($break)) {
          $break = "00:00";
        }
        $workHr   = substr($work, 1, 1);
        $workMin  = substr($work, -2);
        $breakHr  = substr($break, 1, 1);
        $breakMin = substr($break, -2);
        $workHr   = $workHr . 'hr';
        $breakHr  = $breakHr . 'hr';
        $workMin  = ($workMin[0]  == '0') ? $workMin[1]  : $workMin;
        $breakMin = ($breakMin[0] == '0') ? $breakMin[1] : $breakMin;
        $workMin  = $workMin . 'min';
        $breakMin = $breakMin . 'min';

        $key = array_search($day, $days);

        if ($key !== FALSE) {
          $tbody[$key - 1] = "
            <tr id={$dates[$day]}>
              <td>$day</td>
              <td>$clockIn</td>
              <td>$clockOut</td>
              <td>$breakHr $breakMin</td>
              <td>$workHr $workMin</td>
            </tr>
          ";

          $totalHr  += (int)filter_var($workHr, FILTER_SANITIZE_NUMBER_INT);
          $totalMin += (int)filter_var($workMin, FILTER_SANITIZE_NUMBER_INT);
        }
      }

      foreach ($days as $key => $day) {
        if (empty($tbody[$key - 1])) {
          $tbody[$key - 1] = "
            <tr id={$dates[$day]}>
              <td>$day</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          ";
        }
      }

      echo "
        <table class=\"clockTable\">
        <thead>
          <tr>
            <th>Day</th>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Break</th>
            <th>Hours</th>
          </tr>
        </thead>
        <tbody>
      ";

      foreach ($days as $key => $day) {
        echo $tbody[$key - 1];
      }

      while ($totalMin > 60) {
        $totalMin -= 60;
        $totalHr  += 1;
      }

      $pay = money_format('%i', ($totalHr * 12.75) + (($totalMin / 60) * 12.75));
      $surplusHr  = 39 - $totalHr;
      $surplusMin = 60 - $totalMin;

      if ($surplusMin == 60) {
        $surplusMin = 0;
        $surplusHr++;
      }

      echo "
        </tbody>
        <tfoot>
            
        </tfoot>
        </table>

        <table class=\"clockTable\">
            <thead>
                <tr>
                    <th>Total Work Time</th>
                    <th>Pay</th>

                    <th>Surplus Time</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td>{$totalHr}hr {$totalMin}min</td>
                    <td>\$$pay</td>
                    <td>{$surplusHr}hr {$surplusMin}min</td>
                </tr>
            </tfoot>
        </table>
      ";
      ?>
    </div>
    <!-- Main Container End -->

  </body>

</html>