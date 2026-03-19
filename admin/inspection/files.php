<?php
require_once __DIR__ . '/../includes/auth.php';
$auth->requireLogin();

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->connect();

$successMessage = '';
$errorMessage = '';
$errorDetails = [];

function storeBulkImportErrorReport($rows) {
    $_SESSION['inspection_import_error_report'] = $rows;
}

function getBulkImportErrorReportCsv($rows) {
    $headers = ['row_number', 'error_message'];
    foreach ($rows as $row) {
        foreach (($row['data'] ?? []) as $key => $value) {
            if (!in_array($key, $headers, true)) {
                $headers[] = $key;
            }
        }
    }

    $stream = fopen('php://temp', 'r+');
    fputcsv($stream, $headers);

    foreach ($rows as $row) {
        $csvRow = [];
        foreach ($headers as $header) {
            if ($header === 'row_number') {
                $csvRow[] = $row['row'] ?? '';
            } elseif ($header === 'error_message') {
                $csvRow[] = $row['message'] ?? '';
            } else {
                $csvRow[] = $row['data'][$header] ?? '';
            }
        }
        fputcsv($stream, $csvRow);
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    return $csv;
}

function downloadCsvResponse($filename, $headers, $rows) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    $stream = fopen('php://output', 'w');
    fputcsv($stream, $headers);
    foreach ($rows as $row) {
        $csvRow = [];
        foreach ($headers as $header) {
            $csvRow[] = $row[$header] ?? '';
        }
        fputcsv($stream, $csvRow);
    }
    fclose($stream);
    exit;
}

// Generate next file number: INS-YYYY-NNNN
function generateFileNumber($db) {
    $year = date('Y');
    $stmt = $db->prepare("SELECT COALESCE(MAX(CAST(RIGHT(file_number, 4) AS UNSIGNED)), 0)
        FROM inspection_files
        WHERE file_number LIKE :prefix
        AND file_number REGEXP :pattern");
    $stmt->execute([
        ':prefix' => "INS-{$year}-%",
        ':pattern' => '^INS-' . $year . '-[0-9]{4}$',
    ]);
    $num = ((int)$stmt->fetchColumn()) + 1;
    return sprintf("INS-%s-%04d", $year, $num);
}

function tableColumnExists($db, $tableName, $columnName) {
    static $columnCache = [];
    $cacheKey = $tableName . '.' . $columnName;
    if (array_key_exists($cacheKey, $columnCache)) {
        return $columnCache[$cacheKey];
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name");
    $stmt->execute([
        ':table_name' => $tableName,
        ':column_name' => $columnName,
    ]);
    $columnCache[$cacheKey] = ((int)$stmt->fetchColumn()) > 0;

    return $columnCache[$cacheKey];
}

function getActiveLikeStatusCondition($alias = '') {
    $prefix = $alias ? rtrim($alias, '.') . '.' : '';
    return "({$prefix}status = 'active' OR {$prefix}status IS NULL OR {$prefix}status = '')";
}

function formatBulkImportRowError($message) {
    $friendly = $message;

    if (strpos($message, 'Unknown branch') !== false && strpos($message, 'for the selected bank') !== false) {
        $friendly = $message . ' Check the Branches master list or make sure the bank name in Excel matches that branch.';
    } elseif (strpos($message, 'Unknown bank') !== false) {
        $friendly = $message . ' Add this bank in Inspection Masters first, or correct the bank name in Excel.';
    } elseif (strpos($message, 'Unknown source') !== false) {
        $friendly = $message . ' Add this source in Inspection Masters first, or correct the source name in Excel.';
    } elseif (strpos($message, 'Unknown payment mode') !== false) {
        $friendly = $message . ' Add this payment mode in Inspection Masters first, or correct the payment mode name in Excel.';
    } elseif (strpos($message, 'Unknown received account') !== false) {
        $friendly = $message . ' Add this account in Inspection Masters first, or correct the account name in Excel.';
    } elseif (strpos($message, 'Selected branch does not belong to the selected bank') !== false) {
        $friendly = 'The selected branch does not belong to the bank given in that row. Please check the bank and branch columns.';
    } elseif (strpos($message, 'Invalid date') !== false) {
        $friendly = $message . ' Use a valid date like 2026-03-19.';
    } elseif (strpos($message, 'Duplicate entry') !== false) {
        $friendly = 'This row would create a duplicate file number. Please change the file number or leave it blank for auto-generation.';
    }

    return $friendly;
}

// Server-side commission/amount calculation
function calculateAmounts($data) {
    $result = [
        'fees' => null, 'office_amount' => null, 'commission' => 0,
        'amount' => null, 'paid_to_office' => null, 'payment_status' => null,
        'payment_mode_id' => null, 'report_status' => null, 'location' => null,
    ];

    if ($data['file_type'] === 'office') {
        $result['location'] = $data['location'] ?? null;
        $result['commission'] = ($result['location'] === 'kolkata') ? 300 : 350;
    } else {
        $fees = floatval($data['fees'] ?? 0);
        $result['fees'] = $fees;
        $result['commission'] = round($fees * 0.30, 2);
        $result['office_amount'] = round($fees * 0.70, 2);
        $result['report_status'] = !empty($data['report_status']) ? $data['report_status'] : null;
        $result['payment_mode_id'] = !empty($data['payment_mode_id']) ? $data['payment_mode_id'] : null;
        $result['payment_status'] = $data['payment_status'] ?? 'due';
        $result['paid_to_office'] = $data['paid_to_office'] ?? 'due';

        if ($result['payment_status'] === 'paid') {
            $result['amount'] = $fees;
        } elseif ($result['payment_status'] === 'partially') {
            $result['amount'] = floatval($data['amount'] ?? 0);
        }
    }

    $extra = floatval($data['extra_amount'] ?? 0);
    $result['extra_amount'] = $extra;
    $result['gross_amount'] = round($result['commission'] + $extra, 2);

    return $result;
}

function normalizeImportKey($value) {
    $value = trim((string)$value);
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '_', $value);
    return trim($value, '_');
}

function isImportEmptyValue($value) {
    $normalized = normalizeImportKey($value);
    return $normalized === '' || in_array($normalized, ['na', 'n_a', 'null', 'none', 'nil', 'not_applicable', 'dash'], true) || trim((string)$value) === '-';
}

function getImportColumnMap() {
    return [
        'file_number' => ['file_number', 'file_no', 'file'],
        'file_date' => ['file_date', 'date'],
        'file_type' => ['file_type', 'type'],
        'location' => ['location'],
        'customer_name' => ['customer_name', 'customer'],
        'customer_phone' => ['customer_phone', 'phone', 'mobile'],
        'property_address' => ['property_address', 'address'],
        'property_value' => ['property_value'],
        'bank_id' => ['bank_id'],
        'bank_name' => ['bank_name', 'bank'],
        'branch_id' => ['branch_id'],
        'branch_name' => ['branch_name', 'branch'],
        'source_id' => ['source_id'],
        'source_name' => ['source_name', 'source'],
        'fees' => ['fees', 'fee'],
        'report_status' => ['report_status'],
        'report_status_date' => ['report_status_date', 'status_date', 'last_updated_date'],
        'payment_mode_id' => ['payment_mode_id'],
        'payment_mode' => ['payment_mode', 'payment_mode_name', 'mode_name'],
        'payment_status' => ['payment_status'],
        'payment_status_date' => ['payment_status_date', 'payment_date'],
        'amount' => ['amount', 'amount_received'],
        'paid_to_office' => ['paid_to_office'],
        'extra_amount' => ['extra_amount', 'extra'],
        'received_account_id' => ['received_account_id'],
        'received_account' => ['received_account', 'received_in', 'account_name'],
        'notes' => ['notes', 'remark', 'remarks'],
    ];
}

function mapImportHeaders(array $headers) {
    $map = [];
    $aliases = getImportColumnMap();

    foreach ($headers as $index => $header) {
        $normalized = normalizeImportKey($header);
        $canonical = null;

        foreach ($aliases as $field => $fieldAliases) {
            if (in_array($normalized, $fieldAliases, true)) {
                $canonical = $field;
                break;
            }
        }

        $map[$index] = $canonical ?: $normalized;
    }

    return $map;
}

function buildImportedRows(array $headers, array $rawRows) {
    $mappedHeaders = mapImportHeaders($headers);
    $rows = [];

    foreach ($rawRows as $row) {
        $assoc = [];
        foreach ($mappedHeaders as $index => $field) {
            if (!$field) {
                continue;
            }
            $assoc[$field] = isset($row[$index]) ? trim((string)$row[$index]) : '';
        }
        $rows[] = $assoc;
    }

    return $rows;
}

function readCsvImportRows($path) {
    $handle = fopen($path, 'r');
    if (!$handle) {
        throw new Exception('Could not open uploaded CSV file.');
    }

    $headers = fgetcsv($handle);
    if ($headers === false) {
        fclose($handle);
        return [];
    }

    $rows = [];
    while (($data = fgetcsv($handle)) !== false) {
        $rows[] = $data;
    }
    fclose($handle);

    return buildImportedRows($headers, $rows);
}

function xlsxColumnIndex($cellRef) {
    $letters = preg_replace('/[^A-Z]/', '', strtoupper($cellRef));
    $index = 0;
    for ($i = 0; $i < strlen($letters); $i++) {
        $index = ($index * 26) + (ord($letters[$i]) - 64);
    }
    return max(0, $index - 1);
}

function readXlsxImportRows($path) {
    if (!class_exists('ZipArchive')) {
        throw new Exception('XLSX import requires the PHP Zip extension. Please use CSV or enable ZipArchive.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new Exception('Could not open uploaded XLSX file.');
    }

    $sharedStrings = [];
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStringsXml !== false) {
        $xml = @simplexml_load_string($sharedStringsXml);
        if ($xml && isset($xml->si)) {
            foreach ($xml->si as $item) {
                $text = '';
                if (isset($item->t)) {
                    $text = (string)$item->t;
                } elseif (isset($item->r)) {
                    foreach ($item->r as $run) {
                        $text .= (string)$run->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }
    }

    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if ($sheetXml === false) {
        throw new Exception('Could not read the first worksheet from the XLSX file.');
    }

    $sheet = @simplexml_load_string($sheetXml);
    if (!$sheet || !isset($sheet->sheetData->row)) {
        return [];
    }

    $headers = [];
    $rows = [];
    $isHeaderRow = true;

    foreach ($sheet->sheetData->row as $row) {
        $cells = [];
        foreach ($row->c as $cell) {
            $index = xlsxColumnIndex((string)$cell['r']);
            $type = (string)$cell['t'];
            $value = '';

            if ($type === 'inlineStr') {
                $value = (string)$cell->is->t;
            } else {
                $rawValue = isset($cell->v) ? (string)$cell->v : '';
                if ($type === 's') {
                    $value = $sharedStrings[(int)$rawValue] ?? '';
                } else {
                    $value = $rawValue;
                }
            }

            $cells[$index] = $value;
        }

        if (empty($cells)) {
            continue;
        }

        ksort($cells);
        $maxIndex = max(array_keys($cells));
        $flatRow = [];
        for ($i = 0; $i <= $maxIndex; $i++) {
            $flatRow[$i] = $cells[$i] ?? '';
        }

        if ($isHeaderRow) {
            $headers = $flatRow;
            $isHeaderRow = false;
            continue;
        }

        $rows[] = $flatRow;
    }

    return buildImportedRows($headers, $rows);
}

function readImportedSpreadsheet($path, $originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($extension === 'csv') {
        return readCsvImportRows($path);
    }
    if ($extension === 'xlsx') {
        return readXlsxImportRows($path);
    }

    throw new Exception('Only CSV and XLSX files are supported for bulk import.');
}

function isImportRowBlank(array $row) {
    foreach ($row as $value) {
        if (!isImportEmptyValue($value)) {
            return false;
        }
    }
    return true;
}

function normalizeLookupName($value) {
    return normalizeImportKey($value);
}

function parseImportedDate($value) {
    $value = trim((string)$value);
    if (isImportEmptyValue($value)) {
        return null;
    }

    if (is_numeric($value) && (float)$value > 20000) {
        $timestamp = (((int)$value) - 25569) * 86400;
        return gmdate('Y-m-d', $timestamp);
    }

    $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y', 'd.m.Y', 'Y/m/d'];
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $value);
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d');
        }
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    throw new Exception("Invalid date '{$value}'. Use YYYY-MM-DD in the sheet.");
}

function buildImportedTimestampFromDate($value) {
    $date = parseImportedDate($value);
    return $date ? ($date . ' 00:00:00') : null;
}

function parseImportedEnum($value, array $allowedMap, $label) {
    $value = trim((string)$value);
    if (isImportEmptyValue($value)) {
        return null;
    }

    $normalized = normalizeLookupName($value);
    if (!array_key_exists($normalized, $allowedMap)) {
        throw new Exception("Invalid {$label} '{$value}'.");
    }

    return $allowedMap[$normalized];
}

function parseImportedDecimal($value) {
    $value = trim((string)$value);
    if (isImportEmptyValue($value)) {
        return null;
    }

    $value = str_replace([',', ' '], '', $value);
    if (!is_numeric($value)) {
        throw new Exception("Invalid number '{$value}'.");
    }

    return round((float)$value, 2);
}

function findLookupId($value, array $byId, array $byName, $label) {
    $value = trim((string)$value);
    if (isImportEmptyValue($value)) {
        return null;
    }

    if (ctype_digit($value)) {
        $id = (int)$value;
        if (!isset($byId[$id])) {
            throw new Exception("Unknown {$label} id '{$value}'.");
        }
        return $id;
    }

    $normalized = normalizeLookupName($value);
    if (!isset($byName[$normalized])) {
        throw new Exception("Unknown {$label} '{$value}'.");
    }

    return $byName[$normalized];
}

function buildImportTemplateCsv() {
    $headers = [
        'file_date', 'file_type', 'location', 'customer_name', 'customer_phone',
        'property_address', 'property_value', 'bank_name', 'branch_name', 'source_name',
        'fees', 'report_status', 'report_status_date', 'payment_mode', 'payment_status', 'payment_status_date', 'amount',
        'paid_to_office', 'extra_amount', 'received_account', 'notes'
    ];

    $sample = [
        date('Y-m-d'), 'self', '', 'Sample Customer', '9876543210',
        'Sample Address', '2500000', 'Sample Bank', 'Main Branch', 'Referral',
        '1500', 'draft', date('Y-m-d'), 'Cash', 'due', date('Y-m-d'), '',
        'due', '0', 'Main Account', 'Optional note'
    ];

    $stream = fopen('php://temp', 'r+');
    fputcsv($stream, $headers);
    fputcsv($stream, $sample);
    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    return $csv;
}

// Handle AJAX: get branches by bank
if (isset($_GET['ajax']) && $_GET['ajax'] === 'branches' && isset($_GET['bank_id'])) {
    header('Content-Type: application/json');
    try {
        $stmt = $db->prepare("SELECT id, branch_name FROM inspection_branches WHERE bank_id = :bank_id AND " . getActiveLikeStatusCondition() . " ORDER BY branch_name");
        $stmt->execute([':bank_id' => intval($_GET['bank_id'])]);
        echo json_encode($stmt->fetchAll());
    } catch(Exception $e) {
        echo json_encode([]);
    }
    exit;
}

if (isset($_GET['download']) && $_GET['download'] === 'inspection-import-template') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inspection_import_template.csv');
    echo buildImportTemplateCsv();
    exit;
}

if (isset($_GET['download']) && $_GET['download'] === 'inspection-import-errors') {
    $reportRows = $_SESSION['inspection_import_error_report'] ?? [];
    if (empty($reportRows)) {
        http_response_code(404);
        exit('No import error report available.');
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inspection_import_errors.csv');
    echo getBulkImportErrorReportCsv($reportRows);
    exit;
}

if (isset($_GET['download']) && $_GET['download'] === 'inspection-files-export') {
    $stmt = $db->query("SELECT
            f.id, f.file_number, f.file_date, f.file_type, f.location, f.customer_name, f.customer_phone,
            f.property_address, f.property_value, ib.bank_name, ibr.branch_name, isrc.source_name,
            f.fees, f.report_status, " . (tableColumnExists($db, 'inspection_files', 'report_status_date') ? 'f.report_status_date,' : 'NULL AS report_status_date,') . "
            ipm.mode_name AS payment_mode, f.payment_status, " . (tableColumnExists($db, 'inspection_files', 'payment_status_date') ? 'f.payment_status_date,' : 'NULL AS payment_status_date,') . "
            f.amount, f.paid_to_office, f.office_amount, f.commission, f.extra_amount, f.gross_amount,
            ima.account_name AS received_account, f.notes, f.created_at, f.updated_at
        FROM inspection_files f
        LEFT JOIN inspection_banks ib ON f.bank_id = ib.id
        LEFT JOIN inspection_branches ibr ON f.branch_id = ibr.id
        LEFT JOIN inspection_sources isrc ON f.source_id = isrc.id
        LEFT JOIN inspection_payment_modes ipm ON f.payment_mode_id = ipm.id
        LEFT JOIN inspection_my_accounts ima ON f.received_account_id = ima.id
        ORDER BY f.file_date DESC, f.id DESC");
    $rows = $stmt->fetchAll();
    $headers = [
        'id', 'file_number', 'file_date', 'file_type', 'location', 'customer_name', 'customer_phone',
        'property_address', 'property_value', 'bank_name', 'branch_name', 'source_name', 'fees',
        'report_status', 'report_status_date', 'payment_mode', 'payment_status', 'payment_status_date',
        'amount', 'paid_to_office', 'office_amount', 'commission', 'extra_amount', 'gross_amount',
        'received_account', 'notes', 'created_at', 'updated_at'
    ];
    downloadCsvResponse('inspection_files_export.csv', $headers, $rows);
}

// ===== BULK IMPORT FILES =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_import_files'])) {
    try {
        unset($_SESSION['inspection_import_error_report']);

        if (empty($_FILES['import_file']['tmp_name']) || !is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            throw new Exception('Please choose a CSV or XLSX file to import.');
        }

        $rows = readImportedSpreadsheet($_FILES['import_file']['tmp_name'], $_FILES['import_file']['name'] ?? '');
        if (empty($rows)) {
            throw new Exception('The uploaded sheet is empty.');
        }

        $bankRows = $db->query("SELECT id, bank_name FROM inspection_banks WHERE " . getActiveLikeStatusCondition() . "")->fetchAll();
        $banksById = [];
        $banksByName = [];
        foreach ($bankRows as $bank) {
            $banksById[(int)$bank['id']] = $bank;
            $banksByName[normalizeLookupName($bank['bank_name'])] = (int)$bank['id'];
        }

        $branchRows = $db->query("SELECT id, bank_id, branch_name FROM inspection_branches WHERE " . getActiveLikeStatusCondition() . "")->fetchAll();
        $branchesById = [];
        $branchesByBankName = [];
        $branchesByName = [];
        foreach ($branchRows as $branch) {
            $branchId = (int)$branch['id'];
            $bankId = (int)$branch['bank_id'];
            $normalizedBranchName = normalizeLookupName($branch['branch_name']);
            $branchesById[$branchId] = ['id' => $branchId, 'bank_id' => $bankId, 'branch_name' => $branch['branch_name']];
            $branchesByBankName[$bankId][$normalizedBranchName] = $branchId;
            $branchesByName[$normalizedBranchName][] = $branchId;
        }

        $sourceRows = $db->query("SELECT id, source_name FROM inspection_sources WHERE " . getActiveLikeStatusCondition() . "")->fetchAll();
        $sourcesById = [];
        $sourcesByName = [];
        foreach ($sourceRows as $source) {
            $sourcesById[(int)$source['id']] = $source;
            $sourcesByName[normalizeLookupName($source['source_name'])] = (int)$source['id'];
        }

        $modeRows = $db->query("SELECT id, mode_name FROM inspection_payment_modes WHERE " . getActiveLikeStatusCondition() . "")->fetchAll();
        $modesById = [];
        $modesByName = [];
        foreach ($modeRows as $mode) {
            $modesById[(int)$mode['id']] = $mode;
            $modesByName[normalizeLookupName($mode['mode_name'])] = (int)$mode['id'];
        }

        $accountRows = $db->query("SELECT id, account_name FROM inspection_my_accounts WHERE " . getActiveLikeStatusCondition() . "")->fetchAll();
        $accountsById = [];
        $accountsByName = [];
        foreach ($accountRows as $account) {
            $accountsById[(int)$account['id']] = $account;
            $accountsByName[normalizeLookupName($account['account_name'])] = (int)$account['id'];
        }

        $hasReportStatusDate = tableColumnExists($db, 'inspection_files', 'report_status_date');
        $hasPaymentStatusDate = tableColumnExists($db, 'inspection_files', 'payment_status_date');

        $insertColumns = [
            'file_number', 'file_date', 'file_type', 'location', 'customer_name', 'customer_phone', 'property_address', 'property_value',
            'bank_id', 'branch_id', 'source_id', 'fees', 'report_status'
        ];
        if ($hasReportStatusDate) {
            $insertColumns[] = 'report_status_date';
        }
        $insertColumns = array_merge($insertColumns, ['payment_mode_id', 'payment_status']);
        if ($hasPaymentStatusDate) {
            $insertColumns[] = 'payment_status_date';
        }
        $insertColumns = array_merge($insertColumns, [
            'amount', 'paid_to_office', 'office_amount', 'commission', 'extra_amount', 'gross_amount',
            'received_account_id', 'notes', 'updated_at'
        ]);

        $insertPlaceholders = array_map(static function ($column) {
            return ':' . $column;
        }, $insertColumns);

        $insertStmt = $db->prepare(
            'INSERT INTO inspection_files (' . implode(', ', $insertColumns) . ')
             VALUES (' . implode(', ', $insertPlaceholders) . ')'
        );

        $db->beginTransaction();
        $importedCount = 0;
        $skippedCount = 0;
        $rowErrors = [];

        foreach ($rows as $index => $row) {
            $sheetRowNumber = $index + 2;

            if (isImportRowBlank($row)) {
                $skippedCount++;
                continue;
            }

            try {
                $bankId = null;
                $branchId = null;
                $sourceId = null;
                $paymentModeId = null;
                $receivedAccountId = null;

                if (!isImportEmptyValue($row['bank_id'] ?? '') || !isImportEmptyValue($row['bank_name'] ?? '')) {
                    $bankId = !isImportEmptyValue($row['bank_id'] ?? '')
                        ? findLookupId($row['bank_id'], $banksById, $banksByName, 'bank')
                        : findLookupId($row['bank_name'], $banksById, $banksByName, 'bank');
                }

                if (!isImportEmptyValue($row['branch_id'] ?? '')) {
                    $branchId = findLookupId($row['branch_id'], $branchesById, [], 'branch');
                    if ($bankId === null) {
                        $bankId = $branchesById[$branchId]['bank_id'];
                    }
                } elseif (!isImportEmptyValue($row['branch_name'] ?? '')) {
                    $normalizedBranchName = normalizeLookupName($row['branch_name']);
                    if ($bankId !== null) {
                        if (!isset($branchesByBankName[$bankId][$normalizedBranchName])) {
                            throw new Exception("Unknown branch '{$row['branch_name']}' for the selected bank.");
                        }
                        $branchId = $branchesByBankName[$bankId][$normalizedBranchName];
                    } else {
                        $matches = $branchesByName[$normalizedBranchName] ?? [];
                        if (count($matches) === 1) {
                            $branchId = $matches[0];
                            $bankId = $branchesById[$branchId]['bank_id'];
                        } elseif (count($matches) > 1) {
                            throw new Exception("Branch '{$row['branch_name']}' matches multiple banks. Please include bank_name.");
                        } else {
                            throw new Exception("Unknown branch '{$row['branch_name']}'.");
                        }
                    }
                }

                if ($bankId !== null && $branchId !== null && $branchesById[$branchId]['bank_id'] !== $bankId) {
                    throw new Exception('Selected branch does not belong to the selected bank.');
                }

                if (!isImportEmptyValue($row['source_id'] ?? '') || !isImportEmptyValue($row['source_name'] ?? '')) {
                    $sourceId = !isImportEmptyValue($row['source_id'] ?? '')
                        ? findLookupId($row['source_id'], $sourcesById, [], 'source')
                        : findLookupId($row['source_name'], $sourcesById, $sourcesByName, 'source');
                }

                if (!isImportEmptyValue($row['payment_mode_id'] ?? '') || !isImportEmptyValue($row['payment_mode'] ?? '')) {
                    $paymentModeId = !isImportEmptyValue($row['payment_mode_id'] ?? '')
                        ? findLookupId($row['payment_mode_id'], $modesById, [], 'payment mode')
                        : findLookupId($row['payment_mode'], $modesById, $modesByName, 'payment mode');
                }

                if (!isImportEmptyValue($row['received_account_id'] ?? '') || !isImportEmptyValue($row['received_account'] ?? '')) {
                    $receivedAccountId = !isImportEmptyValue($row['received_account_id'] ?? '')
                        ? findLookupId($row['received_account_id'], $accountsById, [], 'received account')
                        : findLookupId($row['received_account'], $accountsById, $accountsByName, 'received account');
                }

                $importData = [
                    'file_type' => parseImportedEnum($row['file_type'] ?? '', [
                        'office' => 'office',
                        'self' => 'self',
                    ], 'file type'),
                    'location' => parseImportedEnum($row['location'] ?? '', [
                        'kolkata' => 'kolkata',
                        'out_of_kolkata' => 'out_of_kolkata',
                        'out_of_kolkata_city' => 'out_of_kolkata',
                        'out_of_kolkata_' => 'out_of_kolkata',
                        'out_of_kolkata_area' => 'out_of_kolkata',
                        'out_of_kolkata_branch' => 'out_of_kolkata',
                        'out_of_kolkata_location' => 'out_of_kolkata',
                        'out_of_kolkata_side' => 'out_of_kolkata',
                        'out_of_kolkata_zone' => 'out_of_kolkata',
                        'out_of_kolkata_region' => 'out_of_kolkata',
                        'out_of_kolkata_town' => 'out_of_kolkata',
                        'out_of_kolkata_district' => 'out_of_kolkata',
                        'out_of_kolkata_place' => 'out_of_kolkata',
                        'out_of_kolkata_outside' => 'out_of_kolkata',
                        'out_of_kolkata_' => 'out_of_kolkata',
                        'out_of_kolkata_kolkata' => 'out_of_kolkata',
                        'out_of_kolkata_out_of_kolkata' => 'out_of_kolkata',
                        'out_of_kolkata_city_area' => 'out_of_kolkata',
                        'out_of_kolkata_city_zone' => 'out_of_kolkata',
                        'out_of_kolkata_town_area' => 'out_of_kolkata',
                        'out_of_kolkata_town_zone' => 'out_of_kolkata',
                        'out_of_kolkata_place_area' => 'out_of_kolkata',
                        'out_of_kolkata_place_zone' => 'out_of_kolkata',
                        'out_of_kolkata_region_area' => 'out_of_kolkata',
                        'out_of_kolkata_region_zone' => 'out_of_kolkata',
                        'out_of_kolkata_district_area' => 'out_of_kolkata',
                        'out_of_kolkata_district_zone' => 'out_of_kolkata',
                        'out_of_kolkata_side_area' => 'out_of_kolkata',
                        'out_of_kolkata_side_zone' => 'out_of_kolkata',
                        'out_of_kolkata_location_area' => 'out_of_kolkata',
                        'out_of_kolkata_location_zone' => 'out_of_kolkata',
                        'out_of_kolkata_branch_area' => 'out_of_kolkata',
                        'out_of_kolkata_branch_zone' => 'out_of_kolkata',
                        'out_of_kolkata_outside_area' => 'out_of_kolkata',
                        'out_of_kolkata_outside_zone' => 'out_of_kolkata',
                        'outofkolkata' => 'out_of_kolkata',
                        'out_of_kolkata' => 'out_of_kolkata',
                    ], 'location'),
                    'fees' => parseImportedDecimal($row['fees'] ?? ''),
                    'report_status' => parseImportedEnum($row['report_status'] ?? '', [
                        'draft' => 'draft',
                        'final_soft' => 'final_soft',
                        'finalsoft' => 'final_soft',
                        'final_soft_copy' => 'final_soft',
                        'finalsoftcopy' => 'final_soft',
                        'final_hard' => 'final_hard',
                        'finalhard' => 'final_hard',
                        'final_hard_copy' => 'final_hard',
                        'finalhardcopy' => 'final_hard',
                    ], 'report status'),
                    'payment_mode_id' => $paymentModeId,
                    'payment_status' => parseImportedEnum($row['payment_status'] ?? '', [
                        'due' => 'due',
                        'paid' => 'paid',
                        'partially' => 'partially',
                        'partial' => 'partially',
                    ], 'payment status'),
                    'amount' => parseImportedDecimal($row['amount'] ?? ''),
                    'paid_to_office' => parseImportedEnum($row['paid_to_office'] ?? '', [
                        'paid' => 'paid',
                        'due' => 'due',
                    ], 'paid to office'),
                    'extra_amount' => parseImportedDecimal($row['extra_amount'] ?? '') ?? 0,
                ];
                $reportStatusDate = parseImportedDate($row['report_status_date'] ?? '');
                $paymentStatusDate = parseImportedDate($row['payment_status_date'] ?? '');
                $updatedAt = null;
                if ($paymentStatusDate) {
                    $updatedAt = $paymentStatusDate . ' 00:00:00';
                } elseif ($reportStatusDate) {
                    $updatedAt = $reportStatusDate . ' 00:00:00';
                }

                $calc = calculateAmounts($importData);
                $fileNumber = trim((string)($row['file_number'] ?? '')) ?: generateFileNumber($db);

                $insertParams = [
                    ':file_number' => $fileNumber,
                    ':file_date' => parseImportedDate($row['file_date'] ?? ''),
                    ':file_type' => $importData['file_type'],
                    ':location' => $calc['location'],
                    ':customer_name' => trim((string)($row['customer_name'] ?? '')) ?: null,
                    ':customer_phone' => trim((string)($row['customer_phone'] ?? '')) ?: null,
                    ':property_address' => trim((string)($row['property_address'] ?? '')) ?: null,
                    ':property_value' => parseImportedDecimal($row['property_value'] ?? ''),
                    ':bank_id' => $bankId,
                    ':branch_id' => $branchId,
                    ':source_id' => $sourceId,
                    ':fees' => $calc['fees'],
                    ':report_status' => $calc['report_status'],
                    ':payment_mode_id' => $calc['payment_mode_id'],
                    ':payment_status' => $calc['payment_status'],
                    ':amount' => $calc['amount'],
                    ':paid_to_office' => $calc['paid_to_office'],
                    ':office_amount' => $calc['office_amount'],
                    ':commission' => $calc['commission'],
                    ':extra_amount' => $calc['extra_amount'],
                    ':gross_amount' => $calc['gross_amount'],
                    ':received_account_id' => $receivedAccountId,
                    ':notes' => trim((string)($row['notes'] ?? '')) ?: null,
                    ':updated_at' => $updatedAt ?: date('Y-m-d H:i:s'),
                ];

                if ($hasReportStatusDate) {
                    $insertParams[':report_status_date'] = $reportStatusDate;
                }
                if ($hasPaymentStatusDate) {
                    $insertParams[':payment_status_date'] = $paymentStatusDate;
                }

                $insertStmt->execute($insertParams);

                $importedCount++;
            } catch (Exception $rowError) {
                $rowErrors[] = [
                    'row' => $sheetRowNumber,
                    'message' => formatBulkImportRowError($rowError->getMessage()),
                    'data' => $row,
                ];
            }
        }

        if ($importedCount > 0) {
            $db->commit();
            $auth->logActivity($auth->getUserId(), 'create', 'inspection_files', null, "Bulk imported {$importedCount} inspection files");
        } else {
            $db->rollBack();
        }

        if (!empty($rowErrors)) {
            storeBulkImportErrorReport($rowErrors);
            $errorMessage = $importedCount > 0
                ? 'Import finished with some problems. Valid rows were uploaded, and invalid rows were skipped.'
                : 'Bulk import could not be completed. No rows were uploaded because every row had a problem.';
            $errorDetails = array_slice($rowErrors, 0, 8);
            if (count($rowErrors) > 8) {
                $errorDetails[] = [
                    'row' => null,
                    'message' => (count($rowErrors) - 8) . ' more row(s) also have issues.',
                    'data' => [],
                ];
            }
        }

        if ($importedCount > 0) {
            $successMessage = "Imported {$importedCount} inspection files successfully.";
            if ($skippedCount > 0) {
                $successMessage .= " Skipped {$skippedCount} blank row(s).";
            }
            if (!empty($rowErrors)) {
                $successMessage .= ' Some invalid rows were skipped.';
            }
        }
    } catch (Exception $e) {
        if ($db && $db->inTransaction()) {
            $db->rollBack();
        }
        $errorMessage = "Import error: " . $e->getMessage();
    }
}

// ===== ADD FILE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_file'])) {
    try {
        unset($_SESSION['inspection_import_error_report']);
        $calc = calculateAmounts($_POST);

        // Validate branch belongs to bank (only if both are provided)
        if (!empty($_POST['bank_id']) && !empty($_POST['branch_id'])) {
            $stmt = $db->prepare("SELECT id FROM inspection_branches WHERE id = :bid AND bank_id = :bankid");
            $stmt->execute([':bid' => $_POST['branch_id'], ':bankid' => $_POST['bank_id']]);
            if (!$stmt->fetch()) {
                throw new Exception("Selected branch does not belong to selected bank.");
            }
        }

        $stmt = $db->prepare("INSERT INTO inspection_files
            (file_number, file_date, file_type, location, customer_name, customer_phone, property_address, property_value,
             bank_id, branch_id, source_id, fees, report_status, payment_mode_id, payment_status, amount,
             paid_to_office, office_amount, commission, extra_amount, gross_amount, received_account_id, notes)
            VALUES
            (:file_number, :file_date, :file_type, :location, :customer_name, :customer_phone, :property_address, :property_value,
             :bank_id, :branch_id, :source_id, :fees, :report_status, :payment_mode_id, :payment_status, :amount,
             :paid_to_office, :office_amount, :commission, :extra_amount, :gross_amount, :received_account_id, :notes)");

        $baseParams = [
            ':file_date' => !empty($_POST['file_date']) ? $_POST['file_date'] : null,
            ':file_type' => !empty($_POST['file_type']) ? $_POST['file_type'] : null,
            ':location' => $calc['location'],
            ':customer_name' => trim($_POST['customer_name']) ?: null,
            ':customer_phone' => trim($_POST['customer_phone']) ?: null,
            ':property_address' => trim($_POST['property_address']) ?: null,
            ':property_value' => !empty($_POST['property_value']) ? floatval($_POST['property_value']) : null,
            ':bank_id' => !empty($_POST['bank_id']) ? $_POST['bank_id'] : null,
            ':branch_id' => !empty($_POST['branch_id']) ? $_POST['branch_id'] : null,
            ':source_id' => !empty($_POST['source_id']) ? $_POST['source_id'] : null,
            ':fees' => $calc['fees'],
            ':report_status' => $calc['report_status'],
            ':payment_mode_id' => $calc['payment_mode_id'],
            ':payment_status' => $calc['payment_status'],
            ':amount' => $calc['amount'],
            ':paid_to_office' => $calc['paid_to_office'],
            ':office_amount' => $calc['office_amount'],
            ':commission' => $calc['commission'],
            ':extra_amount' => $calc['extra_amount'],
            ':gross_amount' => $calc['gross_amount'],
            ':received_account_id' => !empty($_POST['received_account_id']) ? $_POST['received_account_id'] : null,
            ':notes' => trim($_POST['notes']) ?: null,
        ];

        $fileNumber = null;
        $inserted = false;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $fileNumber = generateFileNumber($db);
            try {
                $stmt->execute([':file_number' => $fileNumber] + $baseParams);
                $inserted = true;
                break;
            } catch (PDOException $e) {
                if (($e->errorInfo[1] ?? null) == 1062 && strpos($e->getMessage(), 'uk_file_number') !== false) {
                    continue;
                }
                throw $e;
            }
        }

        if (!$inserted) {
            throw new Exception('Could not generate a unique file number. Please try again.');
        }

        $auth->logActivity($auth->getUserId(), 'create', 'inspection_files', $db->lastInsertId(), "Created file {$fileNumber}");
        $successMessage = "File {$fileNumber} created successfully!";
    } catch(Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// ===== UPDATE FILE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_file'])) {
    try {
        unset($_SESSION['inspection_import_error_report']);
        $calc = calculateAmounts($_POST);

        // Validate branch belongs to bank (only if both are provided)
        if (!empty($_POST['bank_id']) && !empty($_POST['branch_id'])) {
            $stmt = $db->prepare("SELECT id FROM inspection_branches WHERE id = :bid AND bank_id = :bankid");
            $stmt->execute([':bid' => $_POST['branch_id'], ':bankid' => $_POST['bank_id']]);
            if (!$stmt->fetch()) {
                throw new Exception("Selected branch does not belong to selected bank.");
            }
        }

        $stmt = $db->prepare("UPDATE inspection_files SET
            file_date = :file_date, file_type = :file_type, location = :location,
            customer_name = :customer_name, customer_phone = :customer_phone,
            property_address = :property_address, property_value = :property_value,
            bank_id = :bank_id, branch_id = :branch_id, source_id = :source_id,
            fees = :fees, report_status = :report_status, payment_mode_id = :payment_mode_id,
            payment_status = :payment_status, amount = :amount, paid_to_office = :paid_to_office,
            office_amount = :office_amount, commission = :commission, extra_amount = :extra_amount,
            gross_amount = :gross_amount, received_account_id = :received_account_id, notes = :notes,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");

        $stmt->execute([
            ':file_date' => !empty($_POST['file_date']) ? $_POST['file_date'] : null,
            ':file_type' => !empty($_POST['file_type']) ? $_POST['file_type'] : null,
            ':location' => $calc['location'],
            ':customer_name' => trim($_POST['customer_name']) ?: null,
            ':customer_phone' => trim($_POST['customer_phone']) ?: null,
            ':property_address' => trim($_POST['property_address']) ?: null,
            ':property_value' => !empty($_POST['property_value']) ? floatval($_POST['property_value']) : null,
            ':bank_id' => !empty($_POST['bank_id']) ? $_POST['bank_id'] : null,
            ':branch_id' => !empty($_POST['branch_id']) ? $_POST['branch_id'] : null,
            ':source_id' => !empty($_POST['source_id']) ? $_POST['source_id'] : null,
            ':fees' => $calc['fees'],
            ':report_status' => $calc['report_status'],
            ':payment_mode_id' => $calc['payment_mode_id'],
            ':payment_status' => $calc['payment_status'],
            ':amount' => $calc['amount'],
            ':paid_to_office' => $calc['paid_to_office'],
            ':office_amount' => $calc['office_amount'],
            ':commission' => $calc['commission'],
            ':extra_amount' => $calc['extra_amount'],
            ':gross_amount' => $calc['gross_amount'],
            ':received_account_id' => !empty($_POST['received_account_id']) ? $_POST['received_account_id'] : null,
            ':notes' => trim($_POST['notes']) ?: null,
            ':id' => $_POST['file_id'],
        ]);

        $auth->logActivity($auth->getUserId(), 'update', 'inspection_files', $_POST['file_id'], "Updated file");
        $successMessage = "File updated successfully!";
    } catch(Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// ===== DELETE FILE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    try {
        unset($_SESSION['inspection_import_error_report']);
        $stmt = $db->prepare("DELETE FROM inspection_files WHERE id = :id");
        $stmt->execute([':id' => $_POST['file_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_files', $_POST['file_id'], "Deleted file");
        $successMessage = "File deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// ===== BULK ACTIONS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update_files'])) {
    try {
        unset($_SESSION['inspection_import_error_report']);

        $selectedIds = array_values(array_unique(array_filter(array_map('intval', $_POST['selected_file_ids'] ?? []))));
        if (empty($selectedIds)) {
            throw new Exception('Please select at least one file first.');
        }

        $bulkAction = trim((string)($_POST['bulk_action'] ?? ''));
        $allowedActions = ['delete', 'payment_status', 'report_status', 'paid_to_office'];
        if (!in_array($bulkAction, $allowedActions, true)) {
            throw new Exception('Please choose a valid bulk action.');
        }

        $placeholders = [];
        $idParams = [];
        foreach ($selectedIds as $index => $selectedId) {
            $key = ':id' . $index;
            $placeholders[] = $key;
            $idParams[$key] = $selectedId;
        }
        $idListSql = implode(', ', $placeholders);

        $db->beginTransaction();

        if ($bulkAction === 'delete') {
            $stmt = $db->prepare("DELETE FROM inspection_files WHERE id IN ({$idListSql})");
            $stmt->execute($idParams);
            $affectedRows = $stmt->rowCount();

            foreach ($selectedIds as $selectedId) {
                $auth->logActivity($auth->getUserId(), 'delete', 'inspection_files', $selectedId, 'Bulk deleted file');
            }

            $db->commit();
            $successMessage = "{$affectedRows} file(s) deleted successfully.";
        } else {
            $updateSql = '';
            $updateParams = $idParams;
            $affectedRows = 0;
            $officeRowsSkipped = 0;

            if ($bulkAction === 'payment_status') {
                $paymentStatusValue = trim((string)($_POST['bulk_payment_status'] ?? ''));
                if (!in_array($paymentStatusValue, ['due', 'paid'], true)) {
                    throw new Exception('Please choose a valid payment status for the selected files.');
                }

                $skippedStmt = $db->prepare("SELECT COUNT(*) FROM inspection_files WHERE id IN ({$idListSql}) AND file_type = 'office'");
                $skippedStmt->execute($idParams);
                $officeRowsSkipped = (int)$skippedStmt->fetchColumn();

                $updateSql = "UPDATE inspection_files
                    SET payment_status = :bulk_value,
                        amount = CASE WHEN :bulk_value = 'paid' THEN fees ELSE NULL END,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id IN ({$idListSql}) AND file_type = 'self'";
                $updateParams[':bulk_value'] = $paymentStatusValue;

                if (tableColumnExists($db, 'inspection_files', 'payment_status_date')) {
                    $updateSql = "UPDATE inspection_files
                        SET payment_status = :bulk_value,
                            amount = CASE WHEN :bulk_value = 'paid' THEN fees ELSE NULL END,
                            payment_status_date = CURRENT_DATE,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id IN ({$idListSql}) AND file_type = 'self'";
                }
            } elseif ($bulkAction === 'report_status') {
                $reportStatusValue = trim((string)($_POST['bulk_report_status'] ?? ''));
                if (!in_array($reportStatusValue, ['draft', 'final_soft', 'final_hard'], true)) {
                    throw new Exception('Please choose a valid report status for the selected files.');
                }

                $updateSql = "UPDATE inspection_files
                    SET report_status = :bulk_value,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id IN ({$idListSql})";
                $updateParams[':bulk_value'] = $reportStatusValue;

                if (tableColumnExists($db, 'inspection_files', 'report_status_date')) {
                    $updateSql = "UPDATE inspection_files
                        SET report_status = :bulk_value,
                            report_status_date = CURRENT_DATE,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id IN ({$idListSql})";
                }
            } elseif ($bulkAction === 'paid_to_office') {
                $paidToOfficeValue = trim((string)($_POST['bulk_paid_to_office'] ?? ''));
                if (!in_array($paidToOfficeValue, ['paid', 'due'], true)) {
                    throw new Exception('Please choose a valid Paid to Office value.');
                }

                $skippedStmt = $db->prepare("SELECT COUNT(*) FROM inspection_files WHERE id IN ({$idListSql}) AND file_type = 'office'");
                $skippedStmt->execute($idParams);
                $officeRowsSkipped = (int)$skippedStmt->fetchColumn();

                $updateSql = "UPDATE inspection_files
                    SET paid_to_office = :bulk_value,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id IN ({$idListSql}) AND file_type = 'self'";
                $updateParams[':bulk_value'] = $paidToOfficeValue;
            }

            $stmt = $db->prepare($updateSql);
            $stmt->execute($updateParams);
            $affectedRows = $stmt->rowCount();

            foreach ($selectedIds as $selectedId) {
                $auth->logActivity($auth->getUserId(), 'update', 'inspection_files', $selectedId, 'Bulk updated file');
            }

            $db->commit();

            $actionLabels = [
                'payment_status' => 'Payment status',
                'report_status' => 'Report status',
                'paid_to_office' => 'Paid to office',
            ];
            $successMessage = ($actionLabels[$bulkAction] ?? 'Bulk action') . " updated for {$affectedRows} file(s).";
            if ($officeRowsSkipped > 0) {
                $successMessage .= " Skipped {$officeRowsSkipped} office file(s) where that action does not apply.";
            }
        }
    } catch (Exception $e) {
        if ($db && $db->inTransaction()) {
            $db->rollBack();
        }
        $errorMessage = 'Error: ' . $e->getMessage();
    }
}

// ===== FILTERS & PAGINATION =====
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$typeFilter = $_GET['file_type'] ?? '';
$statusFilter = $_GET['payment_status'] ?? '';
$statusGroupFilter = $_GET['status_group'] ?? '';
$bankFilter = $_GET['bank_id'] ?? '';
$sourceFilter = $_GET['source_id'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$dateBasis = $_GET['date_basis'] ?? 'file';
$dateField = $dateBasis === 'updated' ? 'DATE(f.updated_at)' : 'f.file_date';

try {
    $where = "WHERE 1=1";
    $params = [];

    if ($typeFilter) { $where .= " AND f.file_type = :type"; $params[':type'] = $typeFilter; }
    if ($statusGroupFilter === 'pending') {
        $where .= " AND f.file_type = 'self' AND f.payment_status IN ('due', 'partially')";
    } elseif ($statusFilter) {
        $where .= " AND f.payment_status = :status";
        $params[':status'] = $statusFilter;
    }
    if ($bankFilter) { $where .= " AND f.bank_id = :bank"; $params[':bank'] = $bankFilter; }
    if ($sourceFilter) { $where .= " AND f.source_id = :source"; $params[':source'] = $sourceFilter; }
    if ($dateFrom) { $where .= " AND {$dateField} >= :dfrom"; $params[':dfrom'] = $dateFrom; }
    if ($dateTo) { $where .= " AND {$dateField} <= :dto"; $params[':dto'] = $dateTo; }
    if ($searchQuery) {
        $where .= " AND (f.customer_name LIKE :search OR f.file_number LIKE :search OR f.property_address LIKE :search)";
        $params[':search'] = "%{$searchQuery}%";
    }

    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM inspection_files f {$where}");
    $countStmt->execute($params);
    $totalFiles = $countStmt->fetch()['total'];
    $totalPages = max(1, ceil($totalFiles / $perPage));

    $query = "SELECT f.*, ib.bank_name, ibr.branch_name, isrc.source_name, ipm.mode_name, ima.account_name as received_account_name
              FROM inspection_files f
              LEFT JOIN inspection_banks ib ON f.bank_id = ib.id
              LEFT JOIN inspection_branches ibr ON f.branch_id = ibr.id
              LEFT JOIN inspection_sources isrc ON f.source_id = isrc.id
              LEFT JOIN inspection_payment_modes ipm ON f.payment_mode_id = ipm.id
              LEFT JOIN inspection_my_accounts ima ON f.received_account_id = ima.id
              {$where}
              ORDER BY f.file_date DESC, f.id DESC
              LIMIT " . intval($perPage) . " OFFSET " . intval($offset);

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $files = $stmt->fetchAll();

    $banks = $db->query("SELECT id, bank_name FROM inspection_banks WHERE " . getActiveLikeStatusCondition() . " ORDER BY bank_name")->fetchAll();
    $branches = $db->query("SELECT id, bank_id, branch_name FROM inspection_branches WHERE " . getActiveLikeStatusCondition() . " ORDER BY branch_name")->fetchAll();
    $branchesByBank = [];
    foreach ($branches as $branch) {
        $branchesByBank[$branch['bank_id']][] = $branch;
    }

    $sources = $db->query("SELECT id, source_name FROM inspection_sources WHERE " . getActiveLikeStatusCondition() . " ORDER BY source_name")->fetchAll();
    $paymentModes = $db->query("SELECT id, mode_name FROM inspection_payment_modes WHERE " . getActiveLikeStatusCondition() . " ORDER BY mode_name")->fetchAll();
    $myAccounts = $db->query("SELECT id, account_name FROM inspection_my_accounts WHERE " . getActiveLikeStatusCondition() . " ORDER BY account_name")->fetchAll();

} catch(PDOException $e) {
    $files = $banks = $branches = $branchesByBank = $sources = $paymentModes = $myAccounts = [];
    $totalFiles = 0; $totalPages = 1;
    error_log("Files fetch error: " . $e->getMessage());
}

// Build query string for pagination links
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);

$pageTitle = 'Inspection Files';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_responsive.php';
?>

<div class="admin-content inspection-page inspection-files-page">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Inspection Files</h1>
            <p class="page-subtitle">Manage property inspection cases</p>
        </div>
        <div class="inspection-toolbar">
            <a href="files.php?download=inspection-files-export" class="btn btn-outline-secondary">
                <i class="fas fa-download me-2"></i>Download Files
            </a>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                <i class="fas fa-file-import me-2"></i>Bulk Import
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFileModal">
                <i class="fas fa-plus me-2"></i>New File
            </button>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="border border-danger-subtle bg-danger-subtle text-danger rounded p-3 mb-4 position-relative shadow-sm" role="alert">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" aria-label="Close" onclick="this.parentElement.remove();"></button>
            <div class="d-flex align-items-start pe-4">
                <i class="fas fa-exclamation-circle me-2 mt-1"></i>
                <div>
                    <div class="fw-semibold"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php if (!empty($_SESSION['inspection_import_error_report'])): ?>
                        <div class="mt-2">
                            <a href="files.php?download=inspection-import-errors" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-download me-1"></i>Download Error Report
                            </a>
                            <small class="ms-2 text-danger">Open the CSV in Excel to review the skipped rows and their problems.</small>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errorDetails)): ?>
                        <ul class="mb-0 mt-2 ps-3">
                            <?php foreach ($errorDetails as $detail): ?>
                                <li>
                                    <?php if (!empty($detail['row'])): ?>
                                        <strong>Row <?php echo (int)$detail['row']; ?>:</strong>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($detail['message']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="content-card">
        <!-- Filters -->
        <form method="GET" class="row mb-4 g-2 inspection-filter-form">
            <input type="hidden" name="date_basis" value="<?php echo htmlspecialchars($dateBasis); ?>">
            <div class="col-md-3">
                <div class="inspection-search-row">
                    <input type="text" name="search" class="form-control" placeholder="Search name, file#, address..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>" placeholder="From"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>" placeholder="To"></div>
            <div class="col-md-1">
                <select name="file_type" class="form-select">
                    <option value="">Type</option>
                    <option value="office" <?php echo $typeFilter === 'office' ? 'selected' : ''; ?>>Office</option>
                    <option value="self" <?php echo $typeFilter === 'self' ? 'selected' : ''; ?>>Self</option>
                </select>
            </div>
            <div class="col-md-1">
                <select name="payment_status" class="form-select">
                    <option value="">Status</option>
                    <option value="due" <?php echo $statusFilter === 'due' ? 'selected' : ''; ?>>Due</option>
                    <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="partially" <?php echo $statusFilter === 'partially' ? 'selected' : ''; ?>>Partial</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="bank_id" class="form-select">
                    <option value="">All Banks</option>
                    <?php foreach ($banks as $b): ?>
                        <option value="<?php echo $b['id']; ?>" <?php echo $bankFilter == $b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['bank_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1"><a href="files.php" class="btn btn-secondary w-100" title="Reset"><i class="fas fa-redo"></i></a></div>
        </form>

        <!-- Files Table -->
        <form method="POST" id="bulkFilesForm"></form>
        <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
            <div class="flex-grow-1" style="min-width: 220px;">
                <label for="bulkAction" class="form-label mb-1">Bulk Action</label>
                <select name="bulk_action" id="bulkAction" class="form-select" form="bulkFilesForm">
                    <option value="">Choose action</option>
                    <option value="payment_status">Change Payment Status</option>
                    <option value="report_status">Change Report Status</option>
                    <option value="paid_to_office">Change Paid to Office</option>
                    <option value="delete">Delete Selected</option>
                </select>
            </div>
            <div class="bulk-action-field" data-bulk-field="payment_status" style="display:none; min-width: 200px;">
                <label for="bulkPaymentStatus" class="form-label mb-1">Payment Status</label>
                <select name="bulk_payment_status" id="bulkPaymentStatus" class="form-select" form="bulkFilesForm">
                    <option value="">Select payment status</option>
                    <option value="due">Due</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div class="bulk-action-field" data-bulk-field="report_status" style="display:none; min-width: 220px;">
                <label for="bulkReportStatus" class="form-label mb-1">Report Status</label>
                <select name="bulk_report_status" id="bulkReportStatus" class="form-select" form="bulkFilesForm">
                    <option value="">Select report status</option>
                    <option value="draft">Draft</option>
                    <option value="final_soft">Final Soft Copy</option>
                    <option value="final_hard">Final Hard Copy</option>
                </select>
            </div>
            <div class="bulk-action-field" data-bulk-field="paid_to_office" style="display:none; min-width: 200px;">
                <label for="bulkPaidToOffice" class="form-label mb-1">Paid to Office</label>
                <select name="bulk_paid_to_office" id="bulkPaidToOffice" class="form-select" form="bulkFilesForm">
                    <option value="">Select value</option>
                    <option value="paid">Paid</option>
                    <option value="due">Due</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1 d-block">&nbsp;</label>
                <button type="submit" name="bulk_update_files" class="btn btn-outline-primary" id="bulkApplyButton" form="bulkFilesForm" disabled>
                    <i class="fas fa-tasks me-1"></i>Apply
                </button>
            </div>
            <div class="text-muted small" id="bulkSelectedCount">0 file(s) selected</div>
        </div>
        <div class="table-responsive inspection-table-wrap">
            <div class="inspection-table-mobile-note">Files are shown as stacked cards on mobile for easier reading and actions.</div>
            <table class="data-table inspection-table">
                <thead>
                    <tr>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input mt-0" id="selectAllFiles" aria-label="Select all files on this page">
                                <span>File #</span>
                            </div>
                        </th>
                        <th>Date</th><th>Type</th><th>Customer</th>
                        <th>Bank / Branch</th><th>Fees</th><th>Commission</th>
                        <th>Gross</th><th>Payment</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($files)): ?>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td data-label="File #">
                                    <div class="d-flex align-items-center gap-2 inspection-file-select-wrap">
                                        <input type="checkbox" class="form-check-input bulk-file-checkbox mt-0" name="selected_file_ids[]" value="<?php echo (int)$file['id']; ?>" form="bulkFilesForm" aria-label="Select file <?php echo htmlspecialchars($file['file_number']); ?>">
                                        <strong class="inspection-file-number-desktop"><?php echo htmlspecialchars($file['file_number']); ?></strong>
                                    </div>
                                    <div class="inspection-mobile-file-card">
                                        <div class="inspection-mobile-file-top">
                                            <div class="d-flex align-items-start gap-2">
                                                <input type="checkbox" class="form-check-input bulk-file-checkbox mt-1" name="selected_file_ids[]" value="<?php echo (int)$file['id']; ?>" form="bulkFilesForm" aria-label="Select file <?php echo htmlspecialchars($file['file_number']); ?>">
                                                <div>
                                                    <div class="inspection-mobile-file-label">File #</div>
                                                    <div class="inspection-mobile-file-value"><?php echo htmlspecialchars($file['file_number']); ?></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="inspection-mobile-file-label">Date</div>
                                                <div class="inspection-mobile-file-value"><?php echo $file['file_date'] ? date('d M Y', strtotime($file['file_date'])) : '-'; ?></div>
                                            </div>
                                            <details class="inspection-mobile-actions-menu">
                                                <summary class="inspection-mobile-actions-toggle" aria-label="More actions">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </summary>
                                                <div class="inspection-mobile-actions-list">
                                                    <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#viewFileModal<?php echo $file['id']; ?>" title="View"><i class="fas fa-eye"></i></button>
                                                    <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editFileModal<?php echo $file['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                                    <button type="button" class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteFileModal<?php echo $file['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </details>
                                        </div>
                                        <div class="inspection-mobile-file-block">
                                            <div class="inspection-mobile-file-label">Customer</div>
                                            <div class="inspection-mobile-file-value"><?php echo htmlspecialchars($file['customer_name'] ?: '-'); ?></div>
                                        </div>
                                        <div class="inspection-mobile-file-block">
                                            <div class="inspection-mobile-file-label">Bank / Branch</div>
                                            <div class="inspection-mobile-file-value">
                                                <?php echo $file['bank_name'] ? htmlspecialchars($file['bank_name']) : '-'; ?>
                                                <?php if ($file['branch_name']): ?>
                                                    <br><small><?php echo htmlspecialchars($file['branch_name']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="inspection-mobile-file-grid">
                                            <div class="inspection-mobile-file-block">
                                                <div class="inspection-mobile-file-label">Source</div>
                                                <div class="inspection-mobile-file-value"><?php echo htmlspecialchars($file['source_name'] ?: '-'); ?></div>
                                            </div>
                                            <div class="inspection-mobile-file-block">
                                                <div class="inspection-mobile-file-label">Payment</div>
                                                <div class="inspection-mobile-file-value">
                                                    <?php
                                                        if ($file['file_type'] === 'office') {
                                                            echo '<span class="text-muted">NA</span>';
                                                        } else {
                                                            $colors = ['due' => 'danger', 'paid' => 'success', 'partially' => 'warning'];
                                                            echo '<span class="badge bg-' . ($colors[$file['payment_status']] ?? 'secondary') . '">' . ucfirst($file['payment_status'] ?? '-') . '</span>';
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Date"><?php echo $file['file_date'] ? date('d M Y', strtotime($file['file_date'])) : '<span class="text-muted">-</span>'; ?></td>
                                <td data-label="Type"><?php if ($file['file_type']): ?><span class="badge bg-<?php echo $file['file_type'] === 'office' ? 'info' : 'primary'; ?>"><?php echo ucfirst($file['file_type']); ?></span><?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                    <?php if ($file['location']): ?><br><small class="text-muted"><?php echo $file['location'] === 'kolkata' ? 'Kolkata' : 'Out of Kolkata'; ?></small><?php endif; ?>
                                </td>
                                <td data-label="Customer">
                                    <?php echo htmlspecialchars($file['customer_name']); ?>
                                    <?php if ($file['customer_phone']): ?><br><small class="text-muted"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($file['customer_phone']); ?></small><?php endif; ?>
                                </td>
                                <td data-label="Bank / Branch"><?php echo $file['bank_name'] ? htmlspecialchars($file['bank_name']) : '<span class="text-muted">-</span>'; ?><?php if ($file['branch_name']): ?><br><small class="text-muted"><?php echo htmlspecialchars($file['branch_name']); ?></small><?php endif; ?></td>
                                <td data-label="Fees"><?php echo $file['fees'] !== null ? '&#8377;' . number_format($file['fees'], 0) : '<span class="text-muted">NA</span>'; ?></td>
                                <td data-label="Commission">&#8377;<?php echo number_format($file['commission'], 0); ?></td>
                                <td data-label="Gross"><strong>&#8377;<?php echo number_format($file['gross_amount'], 0); ?></strong></td>
                                <td data-label="Payment"><?php
                                    if ($file['file_type'] === 'office') {
                                        echo '<span class="text-muted">NA</span>';
                                    } else {
                                        $colors = ['due' => 'danger', 'paid' => 'success', 'partially' => 'warning'];
                                        echo '<span class="badge bg-' . ($colors[$file['payment_status']] ?? 'secondary') . '">' . ucfirst($file['payment_status'] ?? '-') . '</span>';
                                    }
                                ?></td>
                                <td data-label="Actions">
                                    <div class="inspection-actions">
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#viewFileModal<?php echo $file['id']; ?>" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editFileModal<?php echo $file['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteFileModal<?php echo $file['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- View File Modal -->
                            <div class="modal fade inspection-modal" id="viewFileModal<?php echo $file['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg"><div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title">File: <?php echo htmlspecialchars($file['file_number']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><strong>Date:</strong><br><?php echo date('d M Y', strtotime($file['file_date'])); ?></div>
                                            <div class="col-md-4 mb-3"><strong>File Type:</strong><br><span class="badge bg-<?php echo $file['file_type'] === 'office' ? 'info' : 'primary'; ?>"><?php echo ucfirst($file['file_type']); ?></span>
                                                <?php if ($file['location']): ?> - <?php echo $file['location'] === 'kolkata' ? 'Kolkata' : 'Out of Kolkata'; ?><?php endif; ?>
                                            </div>
                                            <div class="col-md-4 mb-3"><strong>Source:</strong><br><?php echo htmlspecialchars($file['source_name']); ?></div>
                                            <div class="col-md-6 mb-3"><strong>Customer:</strong><br><?php echo htmlspecialchars($file['customer_name']); ?><?php if ($file['customer_phone']): ?> | <?php echo htmlspecialchars($file['customer_phone']); ?><?php endif; ?></div>
                                            <div class="col-md-6 mb-3"><strong>Property Address:</strong><br><?php echo htmlspecialchars($file['property_address']); ?></div>
                                            <div class="col-md-4 mb-3"><strong>Property Value:</strong><br>&#8377;<?php echo number_format($file['property_value'], 0); ?></div>
                                            <div class="col-md-4 mb-3"><strong>Bank:</strong><br><?php echo htmlspecialchars($file['bank_name']); ?></div>
                                            <div class="col-md-4 mb-3"><strong>Branch:</strong><br><?php echo htmlspecialchars($file['branch_name']); ?></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-3 mb-3"><strong>Fees:</strong><br><?php echo $file['fees'] !== null ? '&#8377;' . number_format($file['fees'], 2) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Report Status:</strong><br><?php echo $file['report_status'] ? ucfirst(str_replace('_', ' ', $file['report_status'])) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Payment Mode:</strong><br><?php echo $file['mode_name'] ?? 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Payment Status:</strong><br><?php
                                                if ($file['payment_status']) {
                                                    $colors = ['due' => 'danger', 'paid' => 'success', 'partially' => 'warning'];
                                                    echo '<span class="badge bg-' . ($colors[$file['payment_status']] ?? 'secondary') . '">' . ucfirst($file['payment_status']) . '</span>';
                                                } else { echo 'NA'; }
                                            ?></div>
                                            <div class="col-md-3 mb-3"><strong>Amount Received:</strong><br><?php echo $file['amount'] !== null ? '&#8377;' . number_format($file['amount'], 2) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Paid to Office:</strong><br><?php echo $file['paid_to_office'] ? ucfirst($file['paid_to_office']) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Office Amount:</strong><br><?php echo $file['office_amount'] !== null ? '&#8377;' . number_format($file['office_amount'], 2) : 'NA'; ?></div>
                                            <div class="col-md-3 mb-3"><strong>Received In:</strong><br><?php echo $file['received_account_name'] ?? '-'; ?></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><strong>Commission:</strong><br><span class="text-success fw-bold">&#8377;<?php echo number_format($file['commission'], 2); ?></span></div>
                                            <div class="col-md-4 mb-3"><strong>Extra Amount:</strong><br>&#8377;<?php echo number_format($file['extra_amount'], 2); ?></div>
                                            <div class="col-md-4 mb-3"><strong>Gross Amount:</strong><br><span class="text-primary fw-bold fs-5">&#8377;<?php echo number_format($file['gross_amount'], 2); ?></span></div>
                                        </div>
                                        <?php if ($file['notes']): ?>
                                            <hr><strong>Notes:</strong><br><?php echo nl2br(htmlspecialchars($file['notes'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div></div>
                            </div>

                            <!-- Edit File Modal -->
                            <div class="modal fade inspection-modal" id="editFileModal<?php echo $file['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST" id="editForm<?php echo $file['id']; ?>">
                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <div class="modal-header"><h5 class="modal-title">Edit: <?php echo htmlspecialchars($file['file_number']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3"><label class="form-label">Date</label><input type="date" name="file_date" class="form-control" value="<?php echo $file['file_date']; ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">File Type</label>
                                                <select name="file_type" class="form-select">
                                                    <option value="office" <?php echo $file['file_type'] === 'office' ? 'selected' : ''; ?>>Office</option>
                                                    <option value="self" <?php echo $file['file_type'] === 'self' ? 'selected' : ''; ?>>Self</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Location</label>
                                                <select name="location" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="kolkata" <?php echo $file['location'] === 'kolkata' ? 'selected' : ''; ?>>Kolkata</option>
                                                    <option value="out_of_kolkata" <?php echo $file['location'] === 'out_of_kolkata' ? 'selected' : ''; ?>>Out of Kolkata</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Customer Name</label><input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($file['customer_name']); ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Customer Phone</label><input type="text" name="customer_phone" class="form-control" value="<?php echo htmlspecialchars($file['customer_phone'] ?? ''); ?>"></div>
                                            <div class="col-md-6 mb-3"><label class="form-label">Property Address</label><textarea name="property_address" class="form-control" rows="1"><?php echo htmlspecialchars($file['property_address']); ?></textarea></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Property Value (&#8377;)</label><input type="number" name="property_value" class="form-control" step="0.01" value="<?php echo $file['property_value']; ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Bank</label>
                                                <select name="bank_id" class="form-select">
                                                    <option value="">Select Bank</option>
                                                    <?php foreach ($banks as $b): ?>
                                                        <option value="<?php echo $b['id']; ?>" <?php echo $file['bank_id'] == $b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['bank_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Branch</label>
                                                <select name="branch_id" class="form-select" data-selected="<?php echo $file['branch_id']; ?>">
                                                    <option value="">Select Branch</option>
                                                    <?php foreach (($branchesByBank[$file['bank_id']] ?? []) as $branch): ?>
                                                        <option value="<?php echo $branch['id']; ?>" <?php echo $file['branch_id'] == $branch['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($branch['branch_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Source</label>
                                                <select name="source_id" class="form-select">
                                                    <?php foreach ($sources as $s): ?>
                                                        <option value="<?php echo $s['id']; ?>" <?php echo $file['source_id'] == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['source_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Fees (&#8377;)</label><input type="number" name="fees" class="form-control" step="0.01" value="<?php echo $file['fees']; ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Report Status</label>
                                                <select name="report_status" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="draft" <?php echo $file['report_status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="final_soft" <?php echo $file['report_status'] === 'final_soft' ? 'selected' : ''; ?>>Final Soft Copy</option>
                                                    <option value="final_hard" <?php echo $file['report_status'] === 'final_hard' ? 'selected' : ''; ?>>Final Hard Copy</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Payment Mode</label>
                                                <select name="payment_mode_id" class="form-select">
                                                    <option value="">Select</option>
                                                    <?php foreach ($paymentModes as $pm): ?>
                                                        <option value="<?php echo $pm['id']; ?>" <?php echo $file['payment_mode_id'] == $pm['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pm['mode_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Payment Status</label>
                                                <select name="payment_status" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="due" <?php echo $file['payment_status'] === 'due' ? 'selected' : ''; ?>>Due</option>
                                                    <option value="paid" <?php echo $file['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                    <option value="partially" <?php echo $file['payment_status'] === 'partially' ? 'selected' : ''; ?>>Partially</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Amount Received (&#8377;)</label><input type="number" name="amount" class="form-control" step="0.01" value="<?php echo $file['amount']; ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Paid to Office</label>
                                                <select name="paid_to_office" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="paid" <?php echo $file['paid_to_office'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                    <option value="due" <?php echo $file['paid_to_office'] === 'due' ? 'selected' : ''; ?>>Due</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Office Amount (&#8377;)</label><input type="number" name="office_amount" class="form-control" step="0.01" value="<?php echo $file['office_amount']; ?>" readonly></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Commission (&#8377;)</label><input type="number" name="commission" class="form-control" step="0.01" value="<?php echo $file['commission']; ?>" readonly></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Extra Amount (&#8377;)</label><input type="number" name="extra_amount" class="form-control" step="0.01" value="<?php echo $file['extra_amount']; ?>"></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Gross Amount (&#8377;)</label><input type="number" name="gross_amount" class="form-control" step="0.01" value="<?php echo $file['gross_amount']; ?>" readonly></div>
                                            <div class="col-md-3 mb-3"><label class="form-label">Received In</label>
                                                <select name="received_account_id" class="form-select">
                                                    <option value="">Select Account</option>
                                                    <?php foreach ($myAccounts as $a): ?>
                                                        <option value="<?php echo $a['id']; ?>" <?php echo $file['received_account_id'] == $a['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['account_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($file['notes'] ?? ''); ?></textarea></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_file" class="btn btn-primary">Update File</button></div>
                                </form></div></div>
                            </div>

                            <!-- Delete File Modal -->
                            <div class="modal fade inspection-modal" id="deleteFileModal<?php echo $file['id']; ?>" tabindex="-1">
                                <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                    <input type="hidden" name="delete_file" value="1"><input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <div class="modal-header"><h5 class="modal-title">Delete File</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body"><p>Delete file <strong><?php echo htmlspecialchars($file['file_number']); ?></strong> (<?php echo htmlspecialchars($file['customer_name']); ?>)?</p><p class="text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i>This action cannot be undone.</p></div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                </form></div></div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-folder-open fa-3x mb-3 d-block"></i>No files found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $queryString; ?>">Prev</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $p; ?>&<?php echo $queryString; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $queryString; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <div class="text-center text-muted mt-2"><small>Showing <?php echo count($files); ?> of <?php echo $totalFiles; ?> files</small></div>
    </div>
</div>

<!-- Bulk Import Modal -->
<div class="modal fade inspection-modal" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Bulk Import Inspection Files</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Supported files:</strong> CSV and XLSX
                        <br>
                        <small>Use existing bank, branch, source, payment mode, and received account names from the masters section.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Sheet</label>
                        <input type="file" name="import_file" class="form-control" accept=".csv,.xlsx" required>
                    </div>
                    <div class="small text-muted">
                        Required-style columns can be kept simple:
                        <br>`file_date`, `file_type`, `customer_name`, `bank_name`, `branch_name`, `source_name`
                        <br>Optional columns include `fees`, `report_status`, `report_status_date`, `payment_status`, `payment_status_date`, `payment_mode`, `received_account`, `notes`, and more.
                    </div>
                    <div class="mt-3">
                        <a href="files.php?download=inspection-import-template" class="btn btn-sm btn-light border">
                            <i class="fas fa-download me-1"></i>Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="bulk_import_files" class="btn btn-primary">Import Files</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add File Modal -->
<div class="modal fade inspection-modal" id="addFileModal" tabindex="-1">
    <div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST" id="addForm">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Inspection File</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">Date</label><input type="date" name="file_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="col-md-3 mb-3"><label class="form-label">File Type</label>
                    <select name="file_type" class="form-select">
                        <option value="">Select Type</option>
                        <option value="office">Office</option>
                        <option value="self">Self</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Location</label>
                    <select name="location" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="kolkata">Kolkata</option>
                        <option value="out_of_kolkata">Out of Kolkata</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Customer Name</label><input type="text" name="customer_name" class="form-control" placeholder="Customer name"></div>
                <div class="col-md-3 mb-3"><label class="form-label">Customer Phone</label><input type="text" name="customer_phone" class="form-control" placeholder="Phone number"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Property Address</label><textarea name="property_address" class="form-control" rows="1" placeholder="Full property address"></textarea></div>
                <div class="col-md-3 mb-3"><label class="form-label">Property Value (&#8377;)</label><input type="number" name="property_value" class="form-control" step="0.01" placeholder="0.00"></div>
                <div class="col-md-3 mb-3"><label class="form-label">Bank</label>
                    <select name="bank_id" class="form-select">
                        <option value="">Select Bank</option>
                        <?php foreach ($banks as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['bank_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">Select Bank first</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Source</label>
                    <select name="source_id" class="form-select">
                        <option value="">Select Source</option>
                        <?php foreach ($sources as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['source_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Fees (&#8377;)</label><input type="number" name="fees" class="form-control" step="0.01" placeholder="0.00" disabled></div>
                <div class="col-md-3 mb-3"><label class="form-label">Report Status</label>
                    <select name="report_status" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="draft">Draft</option>
                        <option value="final_soft">Final Soft Copy</option>
                        <option value="final_hard">Final Hard Copy</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Payment Mode</label>
                    <select name="payment_mode_id" class="form-select" disabled>
                        <option value="">Select</option>
                        <?php foreach ($paymentModes as $pm): ?>
                            <option value="<?php echo $pm['id']; ?>"><?php echo htmlspecialchars($pm['mode_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="due">Due</option>
                        <option value="paid">Paid</option>
                        <option value="partially">Partially</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Amount Received (&#8377;)</label><input type="number" name="amount" class="form-control" step="0.01" placeholder="0.00" disabled></div>
                <div class="col-md-3 mb-3"><label class="form-label">Paid to Office</label>
                    <select name="paid_to_office" class="form-select" disabled>
                        <option value="">Select</option>
                        <option value="paid">Paid</option>
                        <option value="due">Due</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label">Office Amount (&#8377;)</label><input type="number" name="office_amount" class="form-control" step="0.01" readonly></div>
                <div class="col-md-3 mb-3"><label class="form-label">Commission (&#8377;)</label><input type="number" name="commission" class="form-control" step="0.01" readonly></div>
                <div class="col-md-3 mb-3"><label class="form-label">Extra Amount (&#8377;)</label><input type="number" name="extra_amount" class="form-control" step="0.01" placeholder="0.00" value="0"></div>
                <div class="col-md-3 mb-3"><label class="form-label">Gross Amount (&#8377;)</label><input type="number" name="gross_amount" class="form-control" step="0.01" readonly></div>
                <div class="col-md-3 mb-3"><label class="form-label">Received In</label>
                    <select name="received_account_id" class="form-select">
                        <option value="">Select Account</option>
                        <?php foreach ($myAccounts as $a): ?>
                            <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['account_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_file" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create File</button></div>
    </form></div></div>
</div>

<script>
function initFileForm(formEl) {
    if (!formEl) return;

    const fileType = formEl.querySelector('[name="file_type"]');
    const location = formEl.querySelector('[name="location"]');
    const fees = formEl.querySelector('[name="fees"]');
    const reportStatus = formEl.querySelector('[name="report_status"]');
    const paymentMode = formEl.querySelector('[name="payment_mode_id"]');
    const paymentStatus = formEl.querySelector('[name="payment_status"]');
    const amount = formEl.querySelector('[name="amount"]');
    const paidToOffice = formEl.querySelector('[name="paid_to_office"]');
    const officeAmount = formEl.querySelector('[name="office_amount"]');
    const commission = formEl.querySelector('[name="commission"]');
    const extraAmount = formEl.querySelector('[name="extra_amount"]');
    const grossAmount = formEl.querySelector('[name="gross_amount"]');
    const bankSelect = formEl.querySelector('[name="bank_id"]');
    const branchSelect = formEl.querySelector('[name="branch_id"]');

    function toggleFields() {
        const isOffice = fileType.value === 'office';
        const isSelf = fileType.value === 'self';

        location.disabled = !isOffice;
        if (!isOffice) location.value = '';

        fees.disabled = isOffice;
        reportStatus.disabled = isOffice;
        paymentMode.disabled = isOffice;
        paymentStatus.disabled = isOffice;
        paidToOffice.disabled = isOffice;

        if (isOffice) {
            fees.value = '';
            reportStatus.value = '';
            paymentMode.value = '';
            paymentStatus.value = '';
            amount.value = '';
            amount.disabled = true;
            paidToOffice.value = '';
            officeAmount.value = '';
        } else if (isSelf) {
            toggleAmount();
        }
        calcCommission();
    }

    function toggleAmount() {
        if (fileType.value === 'office') { amount.disabled = true; amount.value = ''; return; }
        if (paymentStatus.value === 'partially') {
            amount.disabled = false;
        } else if (paymentStatus.value === 'paid') {
            amount.disabled = true;
            amount.value = fees.value || '';
        } else {
            amount.disabled = true;
            amount.value = '';
        }
    }

    function calcCommission() {
        let comm = 0, offAmt = 0;
        if (fileType.value === 'office') {
            if (location.value === 'kolkata') comm = 300;
            else if (location.value === 'out_of_kolkata') comm = 350;
        } else {
            const f = parseFloat(fees.value) || 0;
            comm = Math.round(f * 0.30 * 100) / 100;
            offAmt = Math.round(f * 0.70 * 100) / 100;
        }
        commission.value = comm ? comm.toFixed(2) : '';
        officeAmount.value = (fileType.value === 'self' && offAmt) ? offAmt.toFixed(2) : '';
        const extra = parseFloat(extraAmount.value) || 0;
        grossAmount.value = (comm + extra) ? (comm + extra).toFixed(2) : '';
    }

    function loadBranches(bankId, selectedBranchId) {
        branchSelect.innerHTML = '<option value="">Loading...</option>';
        if (!bankId) { branchSelect.innerHTML = '<option value="">Select Bank first</option>'; return; }
        fetch('files.php?ajax=branches&bank_id=' + bankId)
            .then(r => r.json())
            .then(branches => {
                branchSelect.innerHTML = '<option value="">Select Branch</option>';
                branches.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = b.branch_name;
                    if (selectedBranchId && b.id == selectedBranchId) opt.selected = true;
                    branchSelect.appendChild(opt);
                });
            })
            .catch(() => { branchSelect.innerHTML = '<option value="">Error loading</option>'; });
    }

    fileType.addEventListener('change', toggleFields);
    location.addEventListener('change', calcCommission);
    fees.addEventListener('input', () => { calcCommission(); toggleAmount(); });
    paymentStatus.addEventListener('change', toggleAmount);
    extraAmount.addEventListener('input', calcCommission);
    bankSelect.addEventListener('change', () => {
        branchSelect.dataset.selected = '';
        loadBranches(bankSelect.value);
    });

    // Initialize on load
    if (fileType.value) toggleFields();

    // Load branches for edit forms whenever a bank is already selected.
    if (bankSelect.value) {
        loadBranches(bankSelect.value, branchSelect.dataset.selected || '');
    }
}

// Init add form
document.addEventListener('DOMContentLoaded', () => {
    const addForm = document.getElementById('addForm');
    if (addForm) initFileForm(addForm);
});

// Init edit forms when modals open
document.querySelectorAll('[id^="editFileModal"]').forEach(modal => {
    modal.addEventListener('shown.bs.modal', function() {
        const form = this.querySelector('form');
        if (!form) return;
        if (!form.dataset.initialized) {
            initFileForm(form);
            form.dataset.initialized = 'true';
        } else {
            // Always reload branches on re-open
            const bankSelect = form.querySelector('[name="bank_id"]');
            const branchSelect = form.querySelector('[name="branch_id"]');
            if (bankSelect && bankSelect.value && branchSelect) {
                const selectedBranch = branchSelect.dataset.selected;
                fetch('files.php?ajax=branches&bank_id=' + bankSelect.value)
                    .then(r => r.json())
                    .then(branches => {
                        branchSelect.innerHTML = '<option value="">Select Branch</option>';
                        branches.forEach(b => {
                            const opt = document.createElement('option');
                            opt.value = b.id;
                            opt.textContent = b.branch_name;
                            if (selectedBranch && b.id == selectedBranch) opt.selected = true;
                            branchSelect.appendChild(opt);
                        });
                    });
            }
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const bulkForm = document.getElementById('bulkFilesForm');
    const actionSelect = document.getElementById('bulkAction');
    const selectAll = document.getElementById('selectAllFiles');
    const applyButton = document.getElementById('bulkApplyButton');
    const countLabel = document.getElementById('bulkSelectedCount');
    const actionFields = document.querySelectorAll('.bulk-action-field');
    const allCheckboxes = Array.from(document.querySelectorAll('.bulk-file-checkbox'));

    if (!bulkForm || !actionSelect || !applyButton || !countLabel || allCheckboxes.length === 0) {
        return;
    }

    function getUniqueSelectedIds() {
        const selected = new Set();
        allCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selected.add(checkbox.value);
            }
        });
        return Array.from(selected);
    }

    function syncCheckboxGroup(changedCheckbox) {
        allCheckboxes.forEach(checkbox => {
            if (checkbox !== changedCheckbox && checkbox.value === changedCheckbox.value) {
                checkbox.checked = changedCheckbox.checked;
            }
        });
    }

    function toggleActionFields() {
        const selectedAction = actionSelect.value;
        actionFields.forEach(field => {
            const shouldShow = field.dataset.bulkField === selectedAction;
            field.style.display = shouldShow ? '' : 'none';
        });
    }

    function isActionValueValid() {
        if (!actionSelect.value) {
            return false;
        }

        if (actionSelect.value === 'delete') {
            return true;
        }

        if (actionSelect.value === 'payment_status') {
            return !!document.getElementById('bulkPaymentStatus').value;
        }

        if (actionSelect.value === 'report_status') {
            return !!document.getElementById('bulkReportStatus').value;
        }

        if (actionSelect.value === 'paid_to_office') {
            return !!document.getElementById('bulkPaidToOffice').value;
        }

        return false;
    }

    function updateBulkUi() {
        const selectedIds = getUniqueSelectedIds();
        countLabel.textContent = `${selectedIds.length} file(s) selected`;
        applyButton.disabled = selectedIds.length === 0 || !isActionValueValid();

        if (selectAll) {
            const uniqueIds = Array.from(new Set(allCheckboxes.map(checkbox => checkbox.value)));
            selectAll.checked = uniqueIds.length > 0 && selectedIds.length === uniqueIds.length;
            selectAll.indeterminate = selectedIds.length > 0 && selectedIds.length < uniqueIds.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const shouldCheck = this.checked;
            allCheckboxes.forEach(checkbox => {
                checkbox.checked = shouldCheck;
            });
            updateBulkUi();
        });
    }

    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            syncCheckboxGroup(this);
            updateBulkUi();
        });
    });

    actionSelect.addEventListener('change', () => {
        toggleActionFields();
        updateBulkUi();
    });

    ['bulkPaymentStatus', 'bulkReportStatus', 'bulkPaidToOffice'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('change', updateBulkUi);
        }
    });

    bulkForm.addEventListener('submit', event => {
        const selectedIds = getUniqueSelectedIds();
        if (selectedIds.length === 0 || !isActionValueValid()) {
            event.preventDefault();
            return;
        }

        if (actionSelect.value === 'delete' && !confirm(`Delete ${selectedIds.length} selected file(s)? This cannot be undone.`)) {
            event.preventDefault();
        }
    });

    toggleActionFields();
    updateBulkUi();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
