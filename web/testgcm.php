<?php
function sendCGM($data, $registrationIDs, $key){
$fields = array(
                'registration_ids'  => $registrationIDs,
                'data'              => json_decode($data),
                );

$headers = array( 
                    'Authorization: key='.$key,
                    'Content-Type: application/json'
                );

$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
$result = curl_exec($ch);
curl_close($ch);
echo $result;
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    
    sendCGM($_POST['data'], array($_POST['device']), $_POST['token']);
    
}


?><html>
    <head>
        <style>
           
        </style>    
    </head>
    <body>
        <form method="post">
            <div>Key for browser apps: <input type="text" name="token"/></div>
            <div>Device token: <input type="text" name="device"/></div>
            <div>json data<textarea type="text" name="data"></textarea></div>
            <input type="submit"/>
        </form>
    </body>
</html>