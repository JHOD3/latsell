<?php
session_start();
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Rakit\Validation\Validator;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


echo json_encode(request());

function request($mail_from_address = 'jsdlcs266@gmail.com', $mail_from_name = 'Contacto lebrain', $mail_subject = 'Contacto lebrain')
{
    $validator = new Validator;

    try {
        $full_name = $_POST['full_name'];
        $businnes = $_POST['businnes'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $message = htmlspecialchars($_POST['message']);

        $validation = $validator->validate($_POST, [
            'full_name' => 'required|max:128',
            'businnes' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'message' => 'required|max:1500'
        ]);

        $validation->validate();
        if ($validation->fails()) {
            // Debido a que la validacion se realiza del lado front con javascript
            // aqui le devolvemos un error 422, porque se asume que el usuario esta intentando
            // realizar una accion indevida tatando de saltarse la regla de javascript
            return $validation->getMessages();
        }
        if (!hash_equals($_SESSION['token'], $_POST['token']) || !$_POST['token']) {
            return 'error de token';
        }
        sendForm($mail_from_address, $mail_from_name, $mail_subject, $full_name, $phone, $email, $businnes, $message);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function sendForm($mail_from_address, $mail_from_name, $mail_subject, $full_name, $phone, $email, $businnes, $message)
{
    try {
        $mail = new PHPMailer();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        //datos de acceso al sevidor smtp <<<INIT>>>
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
        $mail->Port = $_ENV['SMTP_PORT'];
        $mail->IsHTML(true);
        //<<<END>>>

        // Emisor de email
        //$mail->setFrom('contacto@pro.buenbit.com', 'Contacto Buenbit Pro');
        $mail->setFrom($_ENV['SMTP_USER'], $mail_from_name);
        // Establecer una dirección de respuesta alternativa
        $mail->addReplyTo($_ENV['SMTP_USER'], $full_name);
        // Establecer a quién se enviará el mensaje
        $mail->addAddress($mail_from_address, $mail_from_name);
        // Asunto
        $mail->Subject = $mail_subject; // '[BBPro.pe] Contacto';
        // Mensaje
        $body = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/email/email_template.html');
        $body = str_replace('%full_name%', $full_name, $body);
        $body = str_replace('%phone%', $phone, $body);
        $body = str_replace('%email%', $email, $body);
        $body = str_replace('%businnes%', $businnes, $body);
        $body = str_replace('%message%', $message, $body);
        $body = str_replace('%date%', date('d/m/Y', time()), $body);

        //$mail->Body =  $msg;
        $mail->MsgHTML($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
