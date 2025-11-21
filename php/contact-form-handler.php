<?php
header('Content-Type: application/json');

// Include database helper
include_once __DIR__ . '/../includes/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$service = isset($_POST['service']) ? trim($_POST['service']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
}

if (empty($service)) {
    $errors[] = 'Please select a service';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Save lead to database
$leadSaved = false;
try {
    $leadSaved = saveLead([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'service_type' => $service,
        'message' => $message,
        'source' => 'contact_form'
    ]);
} catch (Exception $e) {
    // Log error but continue with email sending
    error_log('Failed to save lead: ' . $e->getMessage());
}

// Get admin email from settings
$settings = getAllSettings();
$to = $settings['contact_email'] ?? 'info@dawntoweb.com';

$subject = 'New Contact Form Submission from ' . $name;
$email_content = "Name: $name\n";
$email_content .= "Email: $email\n";
$email_content .= "Phone: $phone\n";
$email_content .= "Service: $service\n\n";
$email_content .= "Message:\n$message\n";

$headers = "From: Dawn To Web Contact Form <noreply@dawntoweb.com>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$mail_sent = @mail($to, $subject, $email_content, $headers);

$contact_file = __DIR__ . '/../contacts.txt';

// Always consider successful if lead was saved to database (even if email fails)
if ($leadSaved || $mail_sent) {
    $log_entry = date('Y-m-d H:i:s') . " - SUCCESS (DB: " . ($leadSaved ? 'YES' : 'NO') . ", Email: " . ($mail_sent ? 'YES' : 'NO') . ")\n";
    $log_entry .= "  Name: $name\n";
    $log_entry .= "  Email: $email\n";
    $log_entry .= "  Phone: $phone\n";
    $log_entry .= "  Service: $service\n";
    $log_entry .= "  Message: $message\n\n";
    file_put_contents($contact_file, $log_entry, FILE_APPEND);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for contacting us! We will get back to you soon.'
    ]);
} else {
    $log_entry = date('Y-m-d H:i:s') . " - FAILED\n";
    $log_entry .= "  Name: $name\n";
    $log_entry .= "  Email: $email\n";
    $log_entry .= "  Phone: $phone\n";
    $log_entry .= "  Service: $service\n";
    $log_entry .= "  Message: $message\n\n";
    file_put_contents($contact_file, $log_entry, FILE_APPEND);

    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly at info@dawntoweb.com.'
    ]);
}
?>
