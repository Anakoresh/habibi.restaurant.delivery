<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Загрузка конфигурации
$config = require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars($_POST['address']);
    $phone = htmlspecialchars($_POST['phone']);
    $delivery_fee = htmlspecialchars($_POST['delivery_fee']);
    $payment = htmlspecialchars($_POST['payment']);
    $cartData = json_decode($_POST['cart_data'], true); // Декодируем данные корзины

    // Формируем HTML для заказа
    $orderDetails = "<h2>Order Details</h2>";
    $total = 0;
    foreach ($cartData as $item) {
        $orderDetails .= "<p>{$item['name']} - {$item['price']} LKR (x{$item['quantity']})</p>";
        $total += $item['price'] * $item['quantity'];
    }
    $total += $delivery_fee;
    $orderDetails .= "<p><strong>Delivery Fee:</strong> {$delivery_fee} LKR</p>";
    $orderDetails .= "<p><strong>Total:</strong> {$total} LKR</p>";
    $orderDetails .= "<p><strong>Payment Method:</strong> {$payment}</p>";

    // Настройка PHPMailer для отправки email админу
    $mail = new PHPMailer(true);
    try {
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

        // Получатель и отправитель
        $mail->setFrom($config['EMAIL'], 'Habibi Restaurant');
        $mail->addAddress('habibihostelsrilanka@gmail.com', 'Admin');

        // Контент
        $mail->isHTML(true);
        $mail->Subject = "New Order from $name";
        $mail->Body = "<p><strong>Name:</strong> $name</p>
                       <p><strong>Email:</strong> $email</p>
                       <p><strong>Address:</strong> $address</p>
                       <p><strong>Phone:</strong> $phone</p>" . $orderDetails;

        $mail->send();

        // Отправляем подтверждение гостю
        $mail->clearAddresses();
        $mail->addAddress($email); // Email гостя
        $mail->Subject = "Order Confirmation from Habibi Restaurant";
        $mail->Body = "<h2>Thank you for your order, $name!</h2>
                       <p>Your order details are as follows:</p>" . $orderDetails .
                      "<p>Thank you for choosing Habibi Restaurant! If you have any questions about your order, we're here to help—just call: +94 (77) 875 0629.</p>";

        $mail->send();

        header("Location: orderconfirmation.html");
        exit;
    } catch (Exception $e) {
        echo "Order could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}