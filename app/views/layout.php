<!DOCTYPE html>
<html lang="en">
<head>
  <title>Picture Challenge</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

  <link rel="stylesheet" href="/app.css">
  <script src="/app.js"></script>
</head>
<body>

<div class="container">
  <h1>Picture Challenge</h1>
  <form role="form" id="request_form">
    <div class="form-group">
      <label for="challenge_number">Challenge number filter:</label>
      <input type="challenge_number" class="form-control" id="challenge_number" placeholder="Enter challenge number, like #250">
    </div>
    <div class="form-group">
      <label for="days">Challenge date</label>
      <input class="form-control" type="text" name="daterange" id="daterange" autocomplete="off" readonly/>
    </div>
    <div class="checkbox">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
  </form>
</div>

<div class="container results_container">
  <hr>
  <h3>Result</h3>
  <div class="results"></div>
</div>

<div class="modal"></div>
</body>
</html>
