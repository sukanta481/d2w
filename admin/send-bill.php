<?php
/**
 * Send Bill via Email/WhatsApp
 * BizNexa CMS - Billing System
 */

require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
require_once 'config/email-config.php';

$database = new Database();
$db = $database->connect();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$billId = $_GET['id'] ?? 0;

if (!$billId) {
    echo json_encode(['success' => false, 'message' => 'Bill ID required']);
    exit;
}

// Get bill details
try {
    $stmt = $db->prepare("
        SELECT b.*, c.name as client_name, c.email as client_email, c.phone as client_phone
        FROM bills b
        LEFT JOIN clients c ON b.client_id = c.id
        WHERE b.id = :id
    ");
    $stmt->execute([':id' => $billId]);
    $bill = $stmt->fetch();
    
    if (!$bill) {
        echo json_encode(['success' => false, 'message' => 'Bill not found']);
        exit;
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

if ($action === 'email') {
    // Send email
    try {
        // Check if PHPMailer is available
        $phpmailerPath = __DIR__ . '/vendor/autoload.php';
        
        if (file_exists($phpmailerPath)) {
            require $phpmailerPath;
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = SMTP_AUTH;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            
            // Recipients
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($bill['client_email'], $bill['client_name']);
            $mail->addReplyTo(MAIL_REPLY_TO);
            
            // Content
            $mail->isHTML(true);
            
            // Replace placeholders in template
            $subject = str_replace('{bill_number}', $bill['bill_number'], BILL_EMAIL_SUBJECT);
            $body = BILL_EMAIL_TEMPLATE;
            $body = str_replace('{client_name}', $bill['client_name'], $body);
            $body = str_replace('{bill_number}', $bill['bill_number'], $body);
            $body = str_replace('{bill_date}', date('F d, Y', strtotime($bill['bill_date'])), $body);
            $body = str_replace('{due_date}', $bill['due_date'] ? date('F d, Y', strtotime($bill['due_date'])) : 'Upon Receipt', $body);
            $body = str_replace('{total_amount}', number_format($bill['total_amount'], 2), $body);
            
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Generate PDF and attach (simplified for now - just link)
            $pdfUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . 
                      dirname($_SERVER['PHP_SELF']) . '/generate-bill-pdf.php?id=' . $billId;
            
            $mail->send();
            
            // Update bill status
            $updateStmt = $db->prepare("UPDATE bills SET sent_via_email = 1, email_sent_at = NOW(), 
                                        status = CASE WHEN status = 'draft' THEN 'sent' ELSE status END 
                                        WHERE id = :id");
            $updateStmt->execute([':id' => $billId]);
            
            $auth->logActivity($auth->getUserId(), 'email', 'bills', $billId, 'Sent bill via email to ' . $bill['client_email']);
            
            echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
        } else {
            // PHPMailer not installed - use PHP mail() as fallback
            $to = $bill['client_email'];
            $subject = "Invoice #" . $bill['bill_number'] . " from BizNexa";
            
            $message = "Dear " . $bill['client_name'] . ",\n\n";
            $message .= "Please find your invoice #" . $bill['bill_number'] . " dated " . date('F d, Y', strtotime($bill['bill_date'])) . ".\n\n";
            $message .= "Amount Due: ₹" . number_format($bill['total_amount'], 2) . "\n";
            $message .= "Due Date: " . ($bill['due_date'] ? date('F d, Y', strtotime($bill['due_date'])) : 'Upon Receipt') . "\n\n";
            $message .= "View your invoice: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . 
                        dirname($_SERVER['PHP_SELF']) . '/generate-bill-pdf.php?id=' . $billId . "&view=1\n\n";
            $message .= "Thank you for your business!\n";
            $message .= "BizNexa Team";
            
            $headers = "From: " . MAIL_FROM_EMAIL . "\r\n";
            $headers .= "Reply-To: " . MAIL_REPLY_TO . "\r\n";
            
            if (mail($to, $subject, $message, $headers)) {
                // Update bill status
                $updateStmt = $db->prepare("UPDATE bills SET sent_via_email = 1, email_sent_at = NOW(),
                                            status = CASE WHEN status = 'draft' THEN 'sent' ELSE status END 
                                            WHERE id = :id");
                $updateStmt->execute([':id' => $billId]);
                
                $auth->logActivity($auth->getUserId(), 'email', 'bills', $billId, 'Sent bill via email to ' . $bill['client_email']);
                
                echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send email. Please configure SMTP settings.']);
            }
        }
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Email error: ' . $e->getMessage()]);
    }
    
} elseif ($action === 'whatsapp') {
    // Generate WhatsApp link
    $phone = preg_replace('/[^0-9]/', '', $bill['client_phone'] ?? '');
    
    if (empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Client phone number not available']);
        exit;
    }
    
    // Make sure phone has country code
    if (strlen($phone) === 10) {
        $phone = '91' . $phone; // Add India country code
    }
    
    $pdfUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . 
              dirname($_SERVER['PHP_SELF']) . '/generate-bill-pdf.php?id=' . $billId . '&view=1';
    
    $message = WHATSAPP_MESSAGE_TEMPLATE;
    $message = str_replace('{client_name}', $bill['client_name'], $message);
    $message = str_replace('{bill_number}', $bill['bill_number'], $message);
    $message = str_replace('{bill_date}', date('F d, Y', strtotime($bill['bill_date'])), $message);
    $message = str_replace('{due_date}', $bill['due_date'] ? date('F d, Y', strtotime($bill['due_date'])) : 'Upon Receipt', $message);
    $message = str_replace('{total_amount}', number_format($bill['total_amount'], 2), $message);
    $message = str_replace('{pdf_link}', $pdfUrl, $message);
    
    $whatsappUrl = 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    
    // Update bill status
    $updateStmt = $db->prepare("UPDATE bills SET sent_via_whatsapp = 1, whatsapp_sent_at = NOW(),
                                status = CASE WHEN status = 'draft' THEN 'sent' ELSE status END 
                                WHERE id = :id");
    $updateStmt->execute([':id' => $billId]);
    
    $auth->logActivity($auth->getUserId(), 'whatsapp', 'bills', $billId, 'Opened WhatsApp for ' . $phone);
    
    echo json_encode(['success' => true, 'url' => $whatsappUrl]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
