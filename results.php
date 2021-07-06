<?php
// API configuration
define('LOQUIZ_API_URL', 'https://api.loquiz.com/v3');
define('LOQUIZ_AUTHORIZATION_HEADER', 'ENTER_YOUR_API_KEY_HERE');
​
if (substr(LOQUIZ_AUTHORIZATION_HEADER, 0, 9) != 'ApiKey-v1') {
  die('Please enter your Loquiz API authorization header in this file. Get your authorization header from Loquiz PRO -> Account settings -> API.');
}
​
// Filters
$gameId = ''; // game id from Loquiz
$scope = ''; // leave empty to get teams from all scopes OR specify one or multiple scopes separated by a comma
​
if (!$gameId) {
  die('Please enter a $gameId in this file. Get your game ID by opening a results page on Loquiz PRO and copying the ID from the URL.');
}
​
// Sorting
$sort = '-totalScore'; // sort by total score descending
​
// Pagination (these values can be used to show results page-by-page)
$skip = 0; // start reading paginated list from start
$limit = 30; // read up to 30 teams (100 is max in Loquiz API)
​
// Function to make Loquiz API GET requests
// NB: HTTPS calls require that the curl extension is enabled
function loquiz_get($path) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, LOQUIZ_API_URL . $path);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_ENCODING, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . LOQUIZ_AUTHORIZATION_HEADER));
​
  $response = curl_exec($ch);
  curl_close($ch);
​
  return json_decode($response);
}
​
// Load game data
$game = loquiz_get("/games/{$gameId}");
​
// Load teams
$teams = loquiz_get("/results/{$gameId}/teams?scope={$scope}&sort={$sort}&skip={$skip}&limit={$limit}");
​
// HTML
?><!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Loquiz results</title>
    <style>
    .container {
      max-width: 1200px;
      margin: auto;
    }
​
    table {
      width: 100%;
    }
​
    th {
      text-align: left;
    }
    </style>
  </head>
​
  <body>
    <div class="container">
      <!-- Game info -->
      <h1><?php echo $game->title; ?></h1>
      <p><?php echo $teams->total; ?> team(s) have played</p>
​
      <!-- Results table -->
      <table>
        <tr>
          <th>Rank</th>
          <th>Team</th>
          <th>Score</th>
          <th>Finished?</th>
          <th>Correct answers</th>
          <th>Incorrect answers</th>
          <th>Odometer</th>
        </tr>
        <!-- Every $team has id, scope, name, color, members, startTime, totalScore, correctAnswers, incorrectAnswers, isFinished, hints, odometer -->
        <?php foreach ($teams->items as $index => $team): ?>
        <tr>
          <td><?php echo $index + 1; ?></td>
          <td style="color: <?php echo $team->color; ?>"><?php echo $team->name; ?></td>
          <td><?php echo $team->totalScore; ?></td>
          <td><?php echo $team->isFinished ? 'finished' : 'not finished'; ?></td>
          <td><?php echo $team->correctAnswers; ?></td>
          <td><?php echo $team->incorrectAnswers; ?></td>
          <td><?php echo round($team->odometer, 2); ?> km</td>
        </tr>
        <?php endforeach; ?>
      </table>
​
      <!-- Game logo -->
      <p>
        <img src="<?php echo $game->logoUrl; ?>">
      </p>
    </div>
  </body>
</html>