<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $name = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $people = trim($_POST['people']);

    if (!empty($name) && !empty($email) && !empty($phone) && !empty($date) && !empty($time) && !empty($people)) { 
        $mail = new PHPMailer(true);  

        try { 
            $mail->SMTPDebug = 0;         //   change to 2 in case
            $mail->isSMTP();                                             
            $mail->Host = 'smtp.gmail.com';  
            $mail->SMTPAuth = true;                                    
            $mail->Username = $config['EMAIL'];
            $mail->Password = $config['PASSWORD'];                  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('habibihostelsrilanka@gmail.com', 'Habibi Restaurant'); 
            $mail->addAddress('habibihostelsrilanka@gmail.com');  

            $mail->isHTML(true);  
            $mail->Subject = 'Table Booking Request'; 
            $mail->Body = " <p><strong>Name:</strong> $name</p>
                            <p><strong>Email:</strong> $email</p>
                            <p><strong>Phone:</strong> $phone</p>
                            <p><strong>Date:</strong> $date</p>
                            <p><strong>Time:</strong> $time</p>
                            <p><strong>Number of people:</strong> $people</p>";

            $mail->send(); 
            
            header("Location: bookingconfirmation.html");
            exit;
        } catch (Exception $e) { 
            echo "Error sending booking request: {$mail->ErrorInfo}"; 
        } 
    } else { 
        echo "Please fill out all required fields correctly."; 
    } 
}
