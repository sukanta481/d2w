<?php
/**
 * Bill PDF Generator
 * BizNexa CMS - Billing System
 * 
 * Generates professional PDF invoices with:
 * - Company logo from assets/images/logo.png
 * - Blue theme matching website (#0d6efd)
 * - Dynamic payment methods with QR code
 * - Authorized signatory section
 * - Professional footer
 */

require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Get bill ID
$billId = $_GET['id'] ?? 0;
$viewMode = isset($_GET['view']); // If view=1, display inline; otherwise download

if (!$billId) {
    die('Bill ID required');
}

// Get bill details
try {
    $stmt = $db->prepare("
        SELECT b.*, c.name as client_name, c.email as client_email, c.phone as client_phone,
               c.company as client_company, c.address as client_address, c.gst_number as client_gst,
               u.full_name as created_by_name
        FROM bills b
        LEFT JOIN clients c ON b.client_id = c.id
        LEFT JOIN admin_users u ON b.created_by = u.id
        WHERE b.id = :id
    ");
    $stmt->execute([':id' => $billId]);
    $bill = $stmt->fetch();
    
    if (!$bill) {
        die('Bill not found');
    }
    
    // Get bill items
    $itemsStmt = $db->prepare("SELECT * FROM bill_items WHERE bill_id = :id ORDER BY display_order");
    $itemsStmt->execute([':id' => $billId]);
    $items = $itemsStmt->fetchAll();
    
    // Get company settings
    $settingsStmt = $db->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $settingsStmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Get selected payment methods
    $bankAccount = null;
    $upiMethod = null;
    
    if (!empty($bill['bank_payment_method_id'])) {
        $bankStmt = $db->prepare("SELECT * FROM payment_methods WHERE id = :id AND is_active = 1");
        $bankStmt->execute([':id' => $bill['bank_payment_method_id']]);
        $bankAccount = $bankStmt->fetch();
    }
    
    if (!empty($bill['upi_payment_method_id'])) {
        $upiStmt = $db->prepare("SELECT * FROM payment_methods WHERE id = :id AND is_active = 1");
        $upiStmt->execute([':id' => $bill['upi_payment_method_id']]);
        $upiMethod = $upiStmt->fetch();
    }
    
} catch(PDOException $e) {
    die('Error: ' . $e->getMessage());
}

// Company information
$companyName = $settings['site_name'] ?? 'BizNexa';
$companyEmail = $settings['site_email'] ?? '';
$companyPhone = $settings['site_phone'] ?? '+91 94332 15443';
$companyAddress = $settings['site_address'] ?? 'Your Business Address';
$companyGST = $settings['company_gst'] ?? '';
$companyWebsite = $settings['site_url'] ?? 'biznexa.tech';

// Logo path - use assets/images/logo.png
$logoPath = '../assets/images/logo.png';

// Theme colors - matching website primary color
$primaryColor = '#0d6efd'; // Blue theme from website
$secondaryColor = '#6366f1'; // Purple accent
$darkColor = '#0f172a';
$textColor = '#333333';
$mutedColor = '#64748b';

// Format dates
$billDate = date('F d, Y', strtotime($bill['bill_date']));
$dueDate = $bill['due_date'] ? date('F d, Y', strtotime($bill['due_date'])) : 'Upon Receipt';

// Check if GST should be shown (only if tax > 0)
$showGST = $bill['tax_percent'] > 0 && $bill['tax_amount'] > 0;

// Generate PDF HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice ' . htmlspecialchars($bill['bill_number']) . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: ' . $textColor . ';
            background: #fff;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            position: relative;
            min-height: 100vh;
        }
        
        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 4px solid ' . $primaryColor . ';
        }
        .company-brand {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .company-logo {
            max-height: 45px;
            max-width: 160px;
        }
        .company-info h1 {
            font-size: 24px;
            color: ' . $darkColor . ';
            margin-bottom: 3px;
            font-weight: 700;
        }
        .company-info p {
            color: ' . $mutedColor . ';
            margin: 2px 0;
            font-size: 11px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 36px;
            color: ' . $darkColor . ';
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 700;
        }
        .invoice-number {
            font-size: 14px;
            color: ' . $mutedColor . ';
            margin-top: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 25px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 12px;
            letter-spacing: 0.5px;
        }
        .status-draft { background: #6c757d; color: #fff; }
        .status-sent { background: ' . $primaryColor . '; color: #fff; }
        .status-paid { background: #10B981; color: #fff; }
        .status-overdue { background: #dc3545; color: #fff; }
        .status-cancelled { background: #343a40; color: #fff; }
        
        /* Billing Info */
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .bill-to, .bill-details {
            width: 48%;
        }
        .section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: ' . $primaryColor . ';
            margin-bottom: 12px;
            font-weight: 700;
        }
        .bill-to h3 {
            font-size: 18px;
            color: ' . $darkColor . ';
            margin-bottom: 8px;
            font-weight: 600;
        }
        .bill-to p {
            color: ' . $mutedColor . ';
            margin: 3px 0;
            font-size: 11px;
        }
        .bill-details {
            text-align: right;
        }
        .detail-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 6px;
        }
        .detail-label {
            color: ' . $mutedColor . ';
            margin-right: 15px;
            font-size: 11px;
        }
        .detail-value {
            font-weight: 600;
            color: ' . $darkColor . ';
            min-width: 130px;
            text-align: left;
            font-size: 11px;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead {
            background: ' . $darkColor . ';
            color: #fff;
        }
        .items-table th {
            padding: 14px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        .items-table th:nth-child(2),
        .items-table th:nth-child(3),
        .items-table td:nth-child(2),
        .items-table td:nth-child(3) {
            text-align: center;
        }
        .items-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .items-table td {
            padding: 14px 15px;
            font-size: 11px;
        }
        .item-description {
            color: ' . $darkColor . ';
            font-weight: 500;
        }
        .item-sr {
            color: ' . $mutedColor . ';
            font-size: 10px;
        }
        
        /* Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
        }
        .totals-box {
            width: 320px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        .total-row:last-child {
            border-bottom: none;
        }
        .total-row.grand {
            border-bottom: none;
            border-top: 3px solid ' . $primaryColor . ';
            padding-top: 15px;
            margin-top: 10px;
        }
        .total-row.grand .total-label,
        .total-row.grand .total-value {
            font-size: 18px;
            font-weight: 700;
            color: ' . $darkColor . ';
        }
        .total-label {
            color: ' . $mutedColor . ';
        }
        .total-value {
            font-weight: 600;
            color: ' . $darkColor . ';
        }
        
        /* Payment Info */
        .payment-section {
            margin-top: 40px;
            padding: 25px;
            background: linear-gradient(135deg, #f8fafc, #fff);
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        .payment-section h4 {
            font-size: 14px;
            color: ' . $darkColor . ';
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid ' . $primaryColor . ';
            font-weight: 600;
        }
        .payment-grid {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }
        .payment-method {
            flex: 1;
            min-width: 200px;
        }
        .payment-method h5 {
            font-size: 12px;
            text-transform: uppercase;
            color: ' . $darkColor . ';
            margin-bottom: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .payment-method p {
            color: ' . $textColor . ';
            margin: 5px 0;
            font-size: 11px;
        }
        .payment-method .label {
            color: ' . $mutedColor . ';
            width: 70px;
            display: inline-block;
        }
        .payment-method .value {
            font-weight: 600;
        }
        .qr-code-section {
            text-align: center;
            flex: 0 0 140px;
            padding: 15px;
            background: #fff;
            border-radius: 10px;
            border: 2px dashed ' . $primaryColor . ';
        }
        .qr-code-section img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 8px;
        }
        .qr-code-section p {
            margin-top: 8px;
            font-size: 10px;
            color: ' . $mutedColor . ';
            font-weight: 500;
        }
        
        /* Notes & Terms */
        .notes-section {
            margin-top: 30px;
            padding: 20px;
            background: #fefefe;
            border-left: 4px solid ' . $primaryColor . ';
            border-radius: 0 8px 8px 0;
        }
        .notes-section h4 {
            font-size: 11px;
            color: ' . $darkColor . ';
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .notes-section p {
            color: ' . $mutedColor . ';
            font-size: 11px;
            line-height: 1.6;
        }
        
        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        .signature-box {
            text-align: center;
            min-width: 200px;
        }
        .signature-line {
            border-top: 2px solid ' . $darkColor . ';
            margin-bottom: 8px;
            
        }
        .signature-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: ' . $mutedColor . ';
            font-weight: 600;
        }
        .signature-company {
            font-size: 12px;
            color: ' . $darkColor . ';
            font-weight: 600;
            margin-top: 5px;
        }
        
        /* Footer */
        .invoice-footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }
        .thank-you-block {
            text-align: center;
            padding: 25px;
            background: linear-gradient(135deg, ' . $primaryColor . '15, ' . $primaryColor . '05);
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .thank-you-block p {
            font-size: 20px;
            color: ' . $darkColor . ';
            font-weight: 700;
            margin: 0;
        }
        .thank-you-block span {
            color: ' . $primaryColor . ';
        }
        .footer-info {
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
        }
        .footer-info p {
            margin: 3px 0;
        }
        .footer-info a {
            color: ' . $primaryColor . ';
            text-decoration: none;
        }
        .footer-divider {
            color: #ddd;
            margin: 0 8px;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            html, body {
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                font-size: 11px !important;
            }
            .invoice-container {
                padding: 10px !important;
                max-width: 100% !important;
                min-height: auto !important;
                transform: scale(0.92);
                transform-origin: top left;
            }
            /* Prevent page breaks inside elements */
            .invoice-header,
            .billing-section,
            .items-table,
            .totals-section,
            .payment-section,
            .notes-section,
            .terms-section,
            .signature-section,
            .invoice-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            /* Keep bottom sections together - no breaks before them */
            .payment-section,
            .signature-section,
            .invoice-footer {
                page-break-before: avoid !important;
                break-before: avoid !important;
            }
            /* Force page break before terms section */
            .page-break-before {
                page-break-before: always !important;
                break-before: page !important;
            }
            /* Reduce sizes for print to fit on one page */
            .payment-section {
                padding: 12px !important;
                margin-top: 15px !important;
            }
            .notes-section,
            .terms-section {
                margin-top: 15px !important;
                padding: 10px !important;
            }
            .signature-section {
                margin-top: 15px !important;
            }
            .invoice-footer {
                margin-top: 15px !important;
                padding-top: 10px !important;
            }
            .qr-code-section img {
                max-width: 70px !important;
                max-height: 70px !important;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-brand">';

// Add logo from assets/images/logo.png
if (file_exists($logoPath)) {
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoMime = mime_content_type($logoPath);
    $html .= '
                <img src="data:' . $logoMime . ';base64,' . $logoData . '" alt="' . htmlspecialchars($companyName) . '" class="company-logo">';
}

// Company info below logo (no company name text since logo shows it)
$html .= '
                <div class="company-info">
                    <p>' . htmlspecialchars($companyAddress) . '</p>';

// Only show email if it exists
if (!empty($companyEmail)) {
    $html .= '<p>' . htmlspecialchars($companyEmail) . ' | ' . htmlspecialchars($companyPhone) . '</p>';
} else {
    $html .= '<p>' . htmlspecialchars($companyPhone) . '</p>';
}

if ($companyGST) {
    $html .= '<p><strong>GSTIN:</strong> ' . htmlspecialchars($companyGST) . '</p>';
}

$html .= '
                </div>
            </div>
            <div class="invoice-title">
                <h2>Invoice</h2>
                <div class="invoice-number">#' . htmlspecialchars($bill['bill_number']) . '</div>
            </div>
        </div>
        
        <!-- Billing Info -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <h3>' . htmlspecialchars($bill['client_name']) . '</h3>
                ' . ($bill['client_company'] ? '<p><strong>' . htmlspecialchars($bill['client_company']) . '</strong></p>' : '') . '
                ' . ($bill['client_address'] ? '<p>' . nl2br(htmlspecialchars($bill['client_address'])) . '</p>' : '');

// Only show client email if it exists
if (!empty($bill['client_email'])) {
    $html .= '<p>' . htmlspecialchars($bill['client_email']) . '</p>';
}

$html .= ($bill['client_phone'] ? '<p>' . htmlspecialchars($bill['client_phone']) . '</p>' : '') . '
                ' . ($bill['client_gst'] ? '<p><strong>GSTIN:</strong> ' . htmlspecialchars($bill['client_gst']) . '</p>' : '') . '
            </div>
            <div class="bill-details">
                <div class="section-title">Invoice Details</div>
                <div class="detail-row">
                    <span class="detail-label">Invoice Date:</span>
                    <span class="detail-value">' . $billDate . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value">' . $dueDate . '</span>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Description</th>
                    <th style="width: 80px;">Qty</th>
                    <th style="width: 100px;">Rate</th>
                    <th style="width: 110px;">Amount</th>
                </tr>
            </thead>
            <tbody>';

$itemNum = 1;
foreach ($items as $item) {
    $html .= '
                <tr>
                    <td class="item-sr">' . $itemNum++ . '</td>
                    <td class="item-description">' . htmlspecialchars($item['description']) . '</td>
                    <td>' . number_format($item['quantity'], 2) . '</td>
                    <td>₹' . number_format($item['unit_price'], 2) . '</td>
                    <td>₹' . number_format($item['total_price'], 2) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span class="total-value">₹' . number_format($bill['subtotal'], 2) . '</span>
                </div>';

// Only show GST row if tax is applied (not zero)
if ($showGST) {
    $html .= '
                <div class="total-row">
                    <span class="total-label">GST (' . number_format($bill['tax_percent'], 1) . '%)</span>
                    <span class="total-value">₹' . number_format($bill['tax_amount'], 2) . '</span>
                </div>';
}

if ($bill['discount_amount'] > 0) {
    $html .= '
                <div class="total-row">
                    <span class="total-label">Discount</span>
                    <span class="total-value" style="color: #10B981;">-₹' . number_format($bill['discount_amount'], 2) . '</span>
                </div>';
}

$html .= '
                <div class="total-row grand">
                    <span class="total-label">Total Amount</span>
                    <span class="total-value">₹' . number_format($bill['total_amount'], 2) . '</span>
                </div>';

if ($bill['paid_amount'] > 0 && $bill['paid_amount'] < $bill['total_amount']) {
    $html .= '
                <div class="total-row">
                    <span class="total-label">Amount Paid</span>
                    <span class="total-value" style="color: #10B981;">₹' . number_format($bill['paid_amount'], 2) . '</span>
                </div>
                <div class="total-row">
                    <span class="total-label" style="font-weight: 600; color: #dc3545;">Balance Due</span>
                    <span class="total-value" style="color: #dc3545;">₹' . number_format($bill['total_amount'] - $bill['paid_amount'], 2) . '</span>
                </div>';
}

$html .= '
            </div>
        </div>';

// Payment information - Only show if payment methods are selected
if ($bankAccount || $upiMethod) {
    $html .= '
        <div class="payment-section">
            <h4>💳 Payment Information</h4>
            <div class="payment-grid">';
    
    // Bank Account
    if ($bankAccount) {
        $html .= '
                <div class="payment-method">
                    <h5>🏦 Bank Transfer</h5>
                    <p><span class="label">Bank:</span> <span class="value">' . htmlspecialchars($bankAccount['bank_name']) . '</span></p>
                    <p><span class="label">A/C No:</span> <span class="value">' . htmlspecialchars($bankAccount['account_number']) . '</span></p>
                    <p><span class="label">IFSC:</span> <span class="value">' . htmlspecialchars($bankAccount['ifsc_code']) . '</span></p>
                    <p><span class="label">Name:</span> <span class="value">' . htmlspecialchars($bankAccount['account_holder']) . '</span></p>';
        if ($bankAccount['branch_name']) {
            $html .= '
                    <p><span class="label">Branch:</span> ' . htmlspecialchars($bankAccount['branch_name']) . '</p>';
        }
        $html .= '
                </div>';
    }
    
    // UPI with QR Code
    if ($upiMethod) {
        $html .= '
                <div class="payment-method">
                    <h5>📱 UPI Payment</h5>
                    <p><span class="label">UPI ID:</span> <span class="value">' . htmlspecialchars($upiMethod['upi_id']) . '</span></p>
                    <p style="margin-top: 10px; font-size: 10px; color: #888;">Scan QR code or use UPI ID to pay instantly via any UPI app</p>
                </div>';
        
        // QR Code
        if ($upiMethod['qr_code_path']) {
            $qrPath = '../' . $upiMethod['qr_code_path'];
            if (file_exists($qrPath)) {
                // Convert to base64 for embedding in HTML
                $qrData = base64_encode(file_get_contents($qrPath));
                $qrMime = mime_content_type($qrPath);
                $html .= '
                <div class="qr-code-section">
                    <img src="data:' . $qrMime . ';base64,' . $qrData . '" alt="UPI QR Code">
                    <p>Scan to Pay</p>
                </div>';
            }
        }
    }
    
    $html .= '
            </div>
        </div>';
}

// Notes and Terms - on second page
if ($bill['notes'] || $bill['terms']) {
    $html .= '
        <div class="notes-section page-break-before">';
    
    if ($bill['notes']) {
        $html .= '
            <h4>Notes</h4>
            <p>' . nl2br(htmlspecialchars($bill['notes'])) . '</p>';
    }
    
    if ($bill['terms']) {
        $html .= '
            <h4 style="margin-top: 15px;">Terms & Conditions</h4>
            <p>' . nl2br(htmlspecialchars($bill['terms'])) . '</p>';
    }
    
    $html .= '
        </div>';
}

// Authorized Signatory Section with Stamp and Sign
$stampSignPath = '../assets/images/stamp and sign.png';
$html .= '
        <div class="signature-section">
            <div class="signature-box">';

// Add stamp and signature image if exists
if (file_exists($stampSignPath)) {
    $stampData = base64_encode(file_get_contents($stampSignPath));
    $stampMime = mime_content_type($stampSignPath);
    $html .= '
                <img src="data:' . $stampMime . ';base64,' . $stampData . '" alt="Authorized Signature" 
                     style="max-height: 80px; max-width: 180px; ">';
}

$html .= '
                <div class="signature-line"></div>
                <div class="signature-label">Authorized Signatory</div>
                <div class="signature-company">For ' . htmlspecialchars($companyName) . '</div>
            </div>
        </div>';

// Footer
$html .= '
        <div class="invoice-footer">
            <div class="thank-you-block">
                <p>Thank You for <span>Your Business!</span></p>
            </div>
            <div class="footer-info">
                <p>
                    <strong>' . htmlspecialchars($companyName) . '</strong>
                    <span class="footer-divider">|</span>
                    <a href="https://' . htmlspecialchars($companyWebsite) . '">' . htmlspecialchars($companyWebsite) . '</a>
                </p>
                <p>';

// Only show email in footer if it exists
if (!empty($companyEmail)) {
    $html .= htmlspecialchars($companyEmail) . '
                    <span class="footer-divider">|</span>';
}

$html .= htmlspecialchars($companyPhone) . '
                </p>
                <p style="margin-top: 10px; color: #bbb;">This is a computer-generated invoice and does not require a physical signature.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto print if in print mode
        if (window.location.search.includes("print=1")) {
            window.print();
        }
    </script>
</body>
</html>';

// If view mode, just display the HTML
if ($viewMode) {
    echo $html;
    exit;
}

// For download - open with print dialog so user can save as PDF
// Add print script and auto-trigger
$html = str_replace(
    'if (window.location.search.includes("print=1")) {',
    'if (true) { // Auto print for download',
    $html
);

header('Content-Type: text/html; charset=utf-8');
echo $html;
exit;
?>
