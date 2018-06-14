<!DOCTYPE html>
<html lang="en">
<head>
  <title>Picture Challenge</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" href="/app.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
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
      <label for="days">Days to look back
      <button type="button" onclick="setDays(7)">One Week</button>
      <button type="button" onclick="setDays(30)">One Month</button></label>
      <input type="number" class="form-control" id="days" value="7">
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
