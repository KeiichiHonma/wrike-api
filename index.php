<?php
    define('angus',TRUE); // setting a constant in order to prevent direct access. Low practical - i know...
    require 'api_handler.php';
    $api = new wrikeapi();
    // Look to the api_handler.php - method token_init can help you make the access token.
    // you should be able to recieve the token.
    $access_token = 'eyJ0dCI6InAiLCJhbGciOiJIUzI1NiIsInR2IjoiMSJ9.eyJkIjoie1wiYVwiOjI5NDYyMDEsXCJpXCI6NjM4MDk0OCxcImNcIjo0NjEyNTE1LFwidVwiOjYyOTYzNTYsXCJyXCI6XCJVU1wiLFwic1wiOltcIldcIixcIkZcIixcIklcIixcIlVcIixcIktcIixcIkNcIixcIkFcIixcIkxcIl0sXCJ6XCI6W10sXCJ0XCI6MH0iLCJpYXQiOjE1NjM0NDEzMjZ9.rimp4lgSo9HAr36wlW0NN99hHBZlD4IusTaXHI-0EcI';
?>
<!DOCTYPE html>
<!--
 Morten D. Hansen
 2015 morten@visia.dk
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Wrike API</title>
    </head>
    <body>
        <p>running</p>
        <?php
            // check that the API is running - this gets the version
            $version = $api->get_accounts($access_token);
            //$version = $api->create_tasks($access_token, 'IEACZ5EZI4LGZILT');
            //$version = $api->attachments_folders($access_token, 'IEACZ5EZI4LGZILT');
var_dump($version);
die();
            $version = json_decode($version);
var_dump($version);
die();
            echo "<p>The api version you are requesting is: " . $version->data[0]->major . "." . $version->data[0]->minor . "<p>";
            echo "That went well!";
        ?>
    </body>
</html>
