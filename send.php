<?php
// Check if request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    } else {
        header('Location: index.html?status=error');
    }
    exit;
}

$lang = isset($_POST['lang']) ? strip_tags(trim($_POST['lang'])) : 'ar';
$redirect_file = ($lang === 'en') ? 'en.html' : 'index.html';

$name = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$phone = isset($_POST['phone']) ? strip_tags(trim($_POST['phone'])) : '';
$subject = isset($_POST['subject']) ? strip_tags(trim($_POST['subject'])) : '';
$message = isset($_POST['message']) ? strip_tags(trim($_POST['message'])) : '';

if (empty($name) || empty($email) || empty($phone) || empty($message)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    } else {
        header("Location: {$redirect_file}?status=incomplete");
    }
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    } else {
        header("Location: {$redirect_file}?status=invalidemail");
    }
    exit;
}

$to = "boodagency@gmail.com";
$email_subject = "ProTrack Contact Form: " . (!empty($subject) ? $subject : "New Inquiry");

$email_content = "You have received a new message from the ProTrack landing page contact form.\n\n";
$email_content .= "Name: $name\n";
$email_content .= "Email: $email\n";
$email_content .= "Phone: $phone\n";
if (!empty($subject)) {
    $email_content .= "Subject: $subject\n";
}
$email_content .= "\nMessage:\n$message\n";

$headers = "From: no-reply@protrack.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $email_subject, $email_content, $headers)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
    } else {
        header("Location: {$redirect_file}?status=success");
    }
} else {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Oops! Something went wrong, and we couldn\'t send your message.']);
    } else {
        header("Location: {$redirect_file}?status=senderror");
    }
}
?>
