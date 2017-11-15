<?php
require 'vendor/autoload.php';

$client = \Directus\SDK\ClientFactory::create('JcZsOHAWCDTx6JS4mbg3a6xnidqmmkZa', [
    // Directus API Path without its version
    'base_url' => 'http://directus.howco.de',
    'version' => '1.1' // Optional - default 1
]);

$answerID = $_POST['AnswerID'];

$votes = $client->getItem('poll_answers', $answerID)->votes;
echo $votes;
$client->updateItem('poll_answers', $answerID, ['votes'=>$votes+1]);
//print_r($answers);

?>
