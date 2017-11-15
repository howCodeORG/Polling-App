<?php
require 'vendor/autoload.php';

$client = \Directus\SDK\ClientFactory::create('JcZsOHAWCDTx6JS4mbg3a6xnidqmmkZa', [
    // Directus API Path without its version
    'base_url' => 'http://directus.howco.de',
    'version' => '1.1' // Optional - default 1
]);

$id = $_GET['id'];
$sumOfVotes = 0;
$question = $client->getItem('polls', $id)->question;
$answers = $client->getItems('poll_answers', array('poll'=>$id));
foreach($answers as $answer) {
  $sumOfVotes += $answer->votes;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>polling</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div class="container">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <h1 class="text-center" style="margin-bottom:20px;"><?php echo $question; ?></h1>
            <div class="list-group">
              <?php
              foreach($answers as $answer) {
                echo '<a class="list-group-item" answer-id='.$answer->id.' answer-votes='.$answer->votes.' href="javascript:void(0)"><span>'.$answer->answer.'</span><div></div></a>';
              }
              ?>
              </div>
        </div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript">
      $('.list-group-item').click(function() {
        var tosend = {

          "AnswerID": $(this).attr('answer-id')

        }
        $.post('ajax-post-vote.php', tosend, function(err, data) {
          $('.list-group-item').addClass("disabled");
          $('.list-group-item div').css("opacity", "1");
          $('.list-group-item').each(function() {
            $("div", this).css("width", ($(this).attr('answer-votes') / <?php echo $sumOfVotes; ?> * 100) + "%")
          })
        })
      })
    </script>
</body>

</html>
