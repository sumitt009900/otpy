<?php
// Your bot token
$bot_token = '7393205450:AAFy3gHuN-H8msnMyKvQVNXqwjdWsQk0FNU:';

// Get the incoming data from the webhook
$update = file_get_contents("php://input");
$update = json_decode($update, true);

// Check if the update has 'message' field
if(isset($update['message'])){
    $message = $update['message'];
    
    // Assuming you have the necessary Telegram Bot API library included
    
    // Get the user information
    $user = $message['from'];

    // Check if the user sent "/start" command
    if ($message['text'] == '/start') {
        // Construct the welcome message
        $welcome_message = "*🔥 Hello " . $user['first_name'] . " Welcome To Our OTP Bot*\n\n/getotp - *For Generate A New Number*";

        // Prepare the parameters for sending the welcome message
        $welcome_parameters = array(
            'chat_id' => $user['id'],
            'text' => $welcome_message,
            'parse_mode' => 'Markdown'
        );

        // Use the sendMessage method to send the welcome message
        file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?" . http_build_query($welcome_parameters));
    }
    
    if ($message['text'] == '/getotp') {
        // Fetch numbers from the URL
        $url = "https://tkkytrsop.live/numbers.php";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $numbers = $data['numbers'];

        // Generate a random number index
        $random_number_index = rand(0, count($numbers) - 1);
        $random_number = $numbers[$random_number_index];

        // Construct the inline keyboard markup
        $markup = array(
            "inline_keyboard" => array(
                array(
                    array("text" => "🔥 Get Otp", "callback_data" => "/otp $random_number"),
                ),
                array(
                    array("text" => "➡️ Next Number", "callback_data" => "/nextotp"),
                ),
            ),
        );

        // Prepare the parameters for sending the message
        $parameters = array(
            'chat_id' => $message['chat']['id'],
            'text' => "*Your Number Is:* `+$random_number`",
            'reply_markup' => json_encode($markup),
            'parse_mode' => 'Markdown'
        );

        // Use the sendMessage method to send the message
        file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?" . http_build_query($parameters));
    }
}

// Check if the update has 'callback_query' field
if(isset($update['callback_query'])){
    $callback_query = $update['callback_query'];
    
    if (isset($callback_query['data']) && strpos($callback_query['data'], '/otp') === 0) {
        // Extract the random number from the callback data
        $callback_data = explode(' ', $callback_query['data']);
        $random_number = $callback_data[1];

        // Construct the URL for sending the request
        $response_url = "https://tkkytrsop.live/sms.php?num=" . urlencode($random_number);

        // Fetch the response from the URL
        $message_response = file_get_contents($response_url);
        $messages = json_decode($message_response, true);

        // Prepare and send the reply message
        foreach ($messages as $msg) {
            if (strpos($msg['Message'], '<') === false) {
                $reply_message = "𝚃𝚒𝚖𝚎: " . $msg['Time'] . "\n" .
                                 "◉━━━━━━━━━━━━◉\n" .
                                 "𝚂𝚎𝚗𝚍𝚎𝚛: " . $msg['Sender'] . "\n" .
                                 "◉━━━━━━━━━━━━◉\n" .
                                 "𝙼𝚎𝚜𝚜𝚊𝚐𝚎: " . $msg['Message'];

                // Prepare the parameters for sending the message
                $message_parameters = array(
                    'chat_id' => $callback_query['message']['chat']['id'],
                    'text' => $reply_message
                );

                // Use the sendMessage method to send the message
                file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?" . http_build_query($message_parameters));
            }
        }
        
    } elseif (isset($callback_query['data']) && $callback_query['data'] == '/nextotp') {
        // Answer the callback query with a message
        $next_number_response = array(
            'callback_query_id' => $callback_query['id'],
            'text' => '🔥 New Number Generated, Enjoy 🥳',
            'show_alert' => true
        );

        // Use the answerCallbackQuery method to show the alert
        file_get_contents("https://api.telegram.org/bot$bot_token/answerCallbackQuery?" . http_build_query($next_number_response));

        // Fetch numbers from the URL
        $url = "https://tkkytrsop.live/numbers.php";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $numbers = $data['numbers'];

        // Generate a random number index
        $random_number_index = rand(0, count($numbers) - 1);
        $random_number = $numbers[$random_number_index];

        // Construct the inline keyboard markup
        $markup = array(
            "inline_keyboard" => array(
                array(
                    array("text" => "🔥 Get Otp", "callback_data" => "/otp $random_number"),
                ),
                array(
                    array("text" => "➡️ Next Number", "callback_data" => "/nextotp"),
                ),
            ),
        );

        // Prepare the parameters for editing the message
        $parameters = array(
            'chat_id' => $callback_query['message']['chat']['id'],
            'message_id' => $callback_query['message']['message_id'],
            'text' => "*Your Number Is:* `+$random_number`",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($markup)
        );

        // Use the editMessageText method to edit the message
        file_get_contents("https://api.telegram.org/bot$bot_token/editMessageText?" . http_build_query($parameters));
    }
}
?>