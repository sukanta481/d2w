<?php
/**
 * Email Configuration
 * BizNexa CMS - Billing System
 * 
 * Configure your SMTP settings here for sending bills via email
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');          // SMTP server host
define('SMTP_PORT', 587);                        // SMTP port (587 for TLS, 465 for SSL)
define('SMTP_SECURE', 'tls');                    // Security: 'tls' or 'ssl'
define('SMTP_AUTH', true);                       // Enable SMTP authentication

// SMTP Credentials - UPDATE THESE WITH YOUR CREDENTIALS
define('SMTP_USER', 'your-email@gmail.com');     // Your email address
define('SMTP_PASS', 'your-app-password');        // Your app password (not regular password)

// Sender Information
define('MAIL_FROM_EMAIL', 'billing@biznexa.tech');
define('MAIL_FROM_NAME', 'BizNexa Billing');
define('MAIL_REPLY_TO', 'info@biznexa.tech');

// Email Templates
define('BILL_EMAIL_SUBJECT', 'Invoice #{bill_number} from BizNexa');
define('BILL_EMAIL_TEMPLATE', '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #1a1a2e; color: #fff; padding: 20px; text-align: center; }
        .content { padding: 30px; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .btn { display: inline-block; padding: 12px 30px; background: #f7b731; color: #1a1a2e; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .amount { font-size: 24px; color: #1a1a2e; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice from BizNexa</h1>
    </div>
    <div class="content">
        <p>Dear {client_name},</p>
        <p>Please find attached your invoice <strong>#{bill_number}</strong> dated {bill_date}.</p>
        <p><strong>Amount Due:</strong> <span class="amount">₹{total_amount}</span></p>
        <p><strong>Due Date:</strong> {due_date}</p>
        <p>Please review the attached PDF for complete details.</p>
        <p>If you have any questions about this invoice, please don\'t hesitate to contact us.</p>
        <p>Thank you for your business!</p>
        <br>
        <p>Best regards,<br>BizNexa Team</p>
    </div>
    <div class="footer">
        <p>BizNexa - Digital Solutions for Your Business</p>
        <p>Email: info@biznexa.tech | Phone: +91 94332 15443</p>
    </div>
</body>
</html>
');

// WhatsApp Configuration
define('WHATSAPP_MESSAGE_TEMPLATE', 
'Hello {client_name},

Your invoice *#{bill_number}* has been generated.

📄 *Invoice Details:*
Amount: ₹{total_amount}
Date: {bill_date}
Due: {due_date}

You can download your invoice here:
{pdf_link}

Thank you for your business!
- BizNexa Team');
?>
