<?php
// https://developers.strava.com/docs/webhooks/

//https://www.curtiscode.dev/post/project/displaying-strava-stats-using-webhooks/
error_log("request to webhook url: ".file_get_contents("php://input"));



if (isset($_GET['hub_challenge'])) {
    $data = ['hub.challenge' => $_GET['hub_challenge']];
    error_log("webhook challenge: ");
    header("HTTP/1.1 200 OK");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    
    exit;
}
// do this with curl in command line:
// $client_id = '5';
// $client_secret = $STRAVA_CLIENT_SECRET;
// $callback_url = 'http://a-valid.com/url/webhook.php'; /* this should be the same URL as this page */
// $verify_token = 'STRAVA';

// curl -X POST https://www.strava.com/api/v3/push_subscriptions \
// -F client_id=$STRAVA_CLIENT_ID \
// -F client_secret=$STRAVA_CLIENT_SECRET  \
// -F callback_url=https://567c-95-89-45-59.ngrok-free.app/admin/create_strava_webhook_subscription.php \
// -F verify_token=$STRAVA_VERIFY_TOKEN


// $curl = curl_init();

// curl_setopt_array($curl, array(
//   CURLOPT_URL => "https://www.strava.com/api/v3/push_subscriptions?client_id={$client_id}&client_secret={$client_secret}&callback_url={$callback_url}&verify_token={$verify_token}",
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'POST',
// ));

// $response = curl_exec($curl);

// curl_close($curl);

// echo $response; /* will return the id or an error message */

// store and/or process id


// further messages rom strava appear at this url with POST reqeusts
// write all content of POSTS to textfiles

?>