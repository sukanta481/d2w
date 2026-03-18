<?php
require_once __DIR__ . '/../includes/auth.php';
$auth->requireLogin();

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->connect();

$successMessage = '';
$errorMessage = '';

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

function normalizeImportKey($value) {
    $value = trim((string)$value);
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '_', $value);
    return trim($value, '_');
}

function mapImportHeaders(array $headers) {
    $map = [];
    foreach ($headers as $index => $header) {
        $map[$index] = normalizeImportKey($header);
    }
    return $map;
}

function buildImportedRows(array $headers, array $rawRows) {
    $mappedHeaders = mapImportHeaders($headers);
    $rows = [];

    foreach ($rawRows as $row) {
        $assoc = [];
        foreach ($mappedHeaders as $index => $field) {
            if ($field === '') {
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
                $value = $type === 's' ? ($sharedStrings[(int)$rawValue] ?? '') : $rawValue;
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
        if (trim((string)$value) !== '') {
            return false;
        }
    }
    return true;
}

function importStatusValue($value) {
    $value = normalizeImportKey($value);
    return $value === 'inactive' ? 'inactive' : 'active';
}

function getMastersImportTemplates() {
    return [
        'banks' => [
            ['bank_name', 'status'],
            ['State Bank of India', 'active'],
        ],
        'branches' => [
            ['bank_name', 'branch_name', 'status'],
            ['State Bank of India', 'Park Street Branch', 'active'],
        ],
        'sources' => [
            ['source_name', 'phone', 'status'],
            ['Ravi Sharma', '9876543210', 'active'],
        ],
        'payment_modes' => [
            ['mode_name', 'status'],
            ['Cash', 'active'],
        ],
        'accounts' => [
            ['account_name', 'bank_name', 'account_number', 'ifsc_code', 'status'],
            ['Main Account', 'State Bank of India', '1234567890', 'SBIN0001234', 'active'],
        ],
    ];
}

function mastersImportTemplate($type) {
    $templates = getMastersImportTemplates();
    if (!isset($templates[$type])) {
        throw new Exception('Invalid import template type.');
    }

    $stream = fopen('php://temp', 'r+');
    foreach ($templates[$type] as $row) {
        fputcsv($stream, $row);
    }
    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    return $csv;
}

if (isset($_GET['download_template'])) {
    $type = normalizeImportKey($_GET['download_template']);
    $templates = getMastersImportTemplates();

    if (!isset($templates[$type])) {
        http_response_code(404);
        exit('Invalid template type.');
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inspection_masters_' . $type . '_template.csv');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo mastersImportTemplate($type);
    exit;
}

if (isset($_GET['download']) && $_GET['download'] === 'inspection-masters-export') {
    $type = normalizeImportKey($_GET['type'] ?? 'all');

    if ($type === 'banks') {
        $rows = $db->query("SELECT id, bank_name, status, created_at, updated_at FROM inspection_banks ORDER BY bank_name")->fetchAll();
        downloadCsvResponse('inspection_banks_export.csv', ['id', 'bank_name', 'status', 'created_at', 'updated_at'], $rows);
    }

    if ($type === 'branches') {
        $rows = $db->query("SELECT b.id, ib.bank_name, b.branch_name, b.status, b.created_at, b.updated_at FROM inspection_branches b JOIN inspection_banks ib ON b.bank_id = ib.id ORDER BY ib.bank_name, b.branch_name")->fetchAll();
        downloadCsvResponse('inspection_branches_export.csv', ['id', 'bank_name', 'branch_name', 'status', 'created_at', 'updated_at'], $rows);
    }

    if ($type === 'sources') {
        $rows = $db->query("SELECT id, source_name, phone, status, created_at, updated_at FROM inspection_sources ORDER BY source_name")->fetchAll();
        downloadCsvResponse('inspection_sources_export.csv', ['id', 'source_name', 'phone', 'status', 'created_at', 'updated_at'], $rows);
    }

    if ($type === 'payment_modes') {
        $rows = $db->query("SELECT id, mode_name, status, created_at, updated_at FROM inspection_payment_modes ORDER BY mode_name")->fetchAll();
        downloadCsvResponse('inspection_payment_modes_export.csv', ['id', 'mode_name', 'status', 'created_at', 'updated_at'], $rows);
    }

    if ($type === 'accounts') {
        $rows = $db->query("SELECT id, account_name, bank_name, account_number, ifsc_code, status, created_at, updated_at FROM inspection_my_accounts ORDER BY account_name")->fetchAll();
        downloadCsvResponse('inspection_accounts_export.csv', ['id', 'account_name', 'bank_name', 'account_number', 'ifsc_code', 'status', 'created_at', 'updated_at'], $rows);
    }

    $exportRows = [];
    $banks = $db->query("SELECT * FROM inspection_banks ORDER BY bank_name")->fetchAll();
    foreach ($banks as $row) {
        $exportRows[] = ['master_type' => 'bank', 'id' => $row['id'], 'name' => $row['bank_name'], 'parent' => '', 'phone' => '', 'account_number' => '', 'ifsc_code' => '', 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']];
    }
    $branches = $db->query("SELECT b.*, ib.bank_name FROM inspection_branches b JOIN inspection_banks ib ON b.bank_id = ib.id ORDER BY ib.bank_name, b.branch_name")->fetchAll();
    foreach ($branches as $row) {
        $exportRows[] = ['master_type' => 'branch', 'id' => $row['id'], 'name' => $row['branch_name'], 'parent' => $row['bank_name'], 'phone' => '', 'account_number' => '', 'ifsc_code' => '', 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']];
    }
    $sources = $db->query("SELECT * FROM inspection_sources ORDER BY source_name")->fetchAll();
    foreach ($sources as $row) {
        $exportRows[] = ['master_type' => 'source', 'id' => $row['id'], 'name' => $row['source_name'], 'parent' => '', 'phone' => $row['phone'], 'account_number' => '', 'ifsc_code' => '', 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']];
    }
    $paymentModes = $db->query("SELECT * FROM inspection_payment_modes ORDER BY mode_name")->fetchAll();
    foreach ($paymentModes as $row) {
        $exportRows[] = ['master_type' => 'payment_mode', 'id' => $row['id'], 'name' => $row['mode_name'], 'parent' => '', 'phone' => '', 'account_number' => '', 'ifsc_code' => '', 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']];
    }
    $accounts = $db->query("SELECT * FROM inspection_my_accounts ORDER BY account_name")->fetchAll();
    foreach ($accounts as $row) {
        $exportRows[] = ['master_type' => 'account', 'id' => $row['id'], 'name' => $row['account_name'], 'parent' => $row['bank_name'], 'phone' => '', 'account_number' => $row['account_number'], 'ifsc_code' => $row['ifsc_code'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']];
    }

    downloadCsvResponse('inspection_masters_export.csv', ['master_type', 'id', 'name', 'parent', 'phone', 'account_number', 'ifsc_code', 'status', 'created_at', 'updated_at'], $exportRows);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_import_masters'])) {
    try {
        $importType = $_POST['import_type'] ?? '';
        if (empty($_FILES['import_file']['tmp_name']) || !is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            throw new Exception('Please choose a CSV or XLSX file to import.');
        }

        $rows = readImportedSpreadsheet($_FILES['import_file']['tmp_name'], $_FILES['import_file']['name'] ?? '');
        if (empty($rows)) {
            throw new Exception('The uploaded sheet is empty.');
        }

        $db->beginTransaction();
        $importedCount = 0;
        $skippedCount = 0;
        $rowErrors = [];

        if ($importType === 'banks') {
            $stmt = $db->prepare("INSERT INTO inspection_banks (bank_name, status) VALUES (:name, :status)");
            foreach ($rows as $index => $row) {
                if (isImportRowBlank($row)) { $skippedCount++; continue; }
                try {
                    $name = trim((string)($row['bank_name'] ?? ''));
                    if ($name === '') {
                        throw new Exception('bank_name is required.');
                    }
                    $stmt->execute([':name' => $name, ':status' => importStatusValue($row['status'] ?? 'active')]);
                    $importedCount++;
                } catch (Exception $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . $e->getMessage();
                } catch (PDOException $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . (strpos($e->getMessage(), 'Duplicate') !== false ? 'Bank already exists.' : $e->getMessage());
                }
            }
        } elseif ($importType === 'branches') {
            $bankRows = $db->query("SELECT id, bank_name FROM inspection_banks")->fetchAll();
            $banksByName = [];
            foreach ($bankRows as $bank) {
                $banksByName[normalizeImportKey($bank['bank_name'])] = (int)$bank['id'];
            }
            $stmt = $db->prepare("INSERT INTO inspection_branches (bank_id, branch_name, status) VALUES (:bank_id, :name, :status)");
            foreach ($rows as $index => $row) {
                if (isImportRowBlank($row)) { $skippedCount++; continue; }
                try {
                    $bankName = trim((string)($row['bank_name'] ?? ''));
                    $branchName = trim((string)($row['branch_name'] ?? ''));
                    if ($bankName === '' || $branchName === '') {
                        throw new Exception('bank_name and branch_name are required.');
                    }
                    $bankKey = normalizeImportKey($bankName);
                    if (!isset($banksByName[$bankKey])) {
                        throw new Exception("Unknown bank '{$bankName}'.");
                    }
                    $stmt->execute([
                        ':bank_id' => $banksByName[$bankKey],
                        ':name' => $branchName,
                        ':status' => importStatusValue($row['status'] ?? 'active')
                    ]);
                    $importedCount++;
                } catch (Exception $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . $e->getMessage();
                } catch (PDOException $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . (strpos($e->getMessage(), 'Duplicate') !== false ? 'Branch already exists for this bank.' : $e->getMessage());
                }
            }
        } elseif ($importType === 'sources') {
            $stmt = $db->prepare("INSERT INTO inspection_sources (source_name, phone, status) VALUES (:name, :phone, :status)");
            foreach ($rows as $index => $row) {
                if (isImportRowBlank($row)) { $skippedCount++; continue; }
                try {
                    $name = trim((string)($row['source_name'] ?? ''));
                    if ($name === '') {
                        throw new Exception('source_name is required.');
                    }
                    $stmt->execute([
                        ':name' => $name,
                        ':phone' => trim((string)($row['phone'] ?? '')) ?: null,
                        ':status' => importStatusValue($row['status'] ?? 'active')
                    ]);
                    $importedCount++;
                } catch (Exception $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . $e->getMessage();
                } catch (PDOException $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . (strpos($e->getMessage(), 'Duplicate') !== false ? 'Source already exists.' : $e->getMessage());
                }
            }
        } elseif ($importType === 'payment_modes') {
            $stmt = $db->prepare("INSERT INTO inspection_payment_modes (mode_name, status) VALUES (:name, :status)");
            foreach ($rows as $index => $row) {
                if (isImportRowBlank($row)) { $skippedCount++; continue; }
                try {
                    $name = trim((string)($row['mode_name'] ?? ''));
                    if ($name === '') {
                        throw new Exception('mode_name is required.');
                    }
                    $stmt->execute([':name' => $name, ':status' => importStatusValue($row['status'] ?? 'active')]);
                    $importedCount++;
                } catch (Exception $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . $e->getMessage();
                } catch (PDOException $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . (strpos($e->getMessage(), 'Duplicate') !== false ? 'Payment mode already exists.' : $e->getMessage());
                }
            }
        } elseif ($importType === 'accounts') {
            $stmt = $db->prepare("INSERT INTO inspection_my_accounts (account_name, bank_name, account_number, ifsc_code, status) VALUES (:name, :bank, :acno, :ifsc, :status)");
            foreach ($rows as $index => $row) {
                if (isImportRowBlank($row)) { $skippedCount++; continue; }
                try {
                    $name = trim((string)($row['account_name'] ?? ''));
                    $bankName = trim((string)($row['bank_name'] ?? ''));
                    $accountNumber = trim((string)($row['account_number'] ?? ''));
                    if ($name === '' || $bankName === '' || $accountNumber === '') {
                        throw new Exception('account_name, bank_name and account_number are required.');
                    }
                    $stmt->execute([
                        ':name' => $name,
                        ':bank' => $bankName,
                        ':acno' => $accountNumber,
                        ':ifsc' => trim((string)($row['ifsc_code'] ?? '')) ?: null,
                        ':status' => importStatusValue($row['status'] ?? 'active')
                    ]);
                    $importedCount++;
                } catch (Exception $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . $e->getMessage();
                } catch (PDOException $e) {
                    $rowErrors[] = 'Row ' . ($index + 2) . ': ' . $e->getMessage();
                }
            }
        } else {
            throw new Exception('Please select a valid master type to import.');
        }

        if (!empty($rowErrors)) {
            $db->rollBack();
            $errorMessage = 'Bulk import failed. ' . implode(' ', array_slice($rowErrors, 0, 5));
            if (count($rowErrors) > 5) {
                $errorMessage .= ' More rows also failed.';
            }
        } else {
            $db->commit();
            $auth->logActivity($auth->getUserId(), 'create', null, null, "Bulk imported {$importedCount} {$importType}");
            $successMessage = "Imported {$importedCount} {$importType} successfully.";
            if ($skippedCount > 0) {
                $successMessage .= " Skipped {$skippedCount} blank row(s).";
            }
        }
    } catch (Exception $e) {
        if ($db && $db->inTransaction()) {
            $db->rollBack();
        }
        $errorMessage = 'Import error: ' . $e->getMessage();
    }
}

// ===== BANKS CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bank'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_banks (bank_name) VALUES (:name)");
        $stmt->execute([':name' => trim($_POST['bank_name'])]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_banks', $db->lastInsertId(), "Added bank: " . $_POST['bank_name']);
        $successMessage = "Bank added successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Bank name already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bank'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_banks SET bank_name = :name, status = :status WHERE id = :id");
        $stmt->execute([':name' => trim($_POST['bank_name']), ':status' => $_POST['status'], ':id' => $_POST['bank_id']]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_banks', $_POST['bank_id'], "Updated bank");
        $successMessage = "Bank updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Bank name already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_bank'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_banks WHERE id = :id");
        $stmt->execute([':id' => $_POST['bank_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_banks', $_POST['bank_id'], "Deleted bank");
        $successMessage = "Bank deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Cannot delete: this bank is used in inspection files or has branches!"
            : "Error: " . $e->getMessage();
    }
}

// ===== BRANCHES CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_branch'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_branches (bank_id, branch_name) VALUES (:bank_id, :name)");
        $stmt->execute([':bank_id' => $_POST['bank_id'], ':name' => trim($_POST['branch_name'])]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_branches', $db->lastInsertId(), "Added branch: " . $_POST['branch_name']);
        $successMessage = "Branch added successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Branch already exists for this bank!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_branch'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_branches SET bank_id = :bank_id, branch_name = :name, status = :status WHERE id = :id");
        $stmt->execute([':bank_id' => $_POST['bank_id'], ':name' => trim($_POST['branch_name']), ':status' => $_POST['status'], ':id' => $_POST['branch_id']]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_branches', $_POST['branch_id'], "Updated branch");
        $successMessage = "Branch updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Branch already exists for this bank!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_branch'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_branches WHERE id = :id");
        $stmt->execute([':id' => $_POST['branch_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_branches', $_POST['branch_id'], "Deleted branch");
        $successMessage = "Branch deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Cannot delete: this branch is used in inspection files!"
            : "Error: " . $e->getMessage();
    }
}

// ===== SOURCES CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_source'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_sources (source_name, phone) VALUES (:name, :phone)");
        $stmt->execute([':name' => trim($_POST['source_name']), ':phone' => trim($_POST['phone']) ?: null]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_sources', $db->lastInsertId(), "Added source: " . $_POST['source_name']);
        $successMessage = "Source added successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Source name already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_source'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_sources SET source_name = :name, phone = :phone, status = :status WHERE id = :id");
        $stmt->execute([':name' => trim($_POST['source_name']), ':phone' => trim($_POST['phone']) ?: null, ':status' => $_POST['status'], ':id' => $_POST['source_id']]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_sources', $_POST['source_id'], "Updated source");
        $successMessage = "Source updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Source name already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_source'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_sources WHERE id = :id");
        $stmt->execute([':id' => $_POST['source_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_sources', $_POST['source_id'], "Deleted source");
        $successMessage = "Source deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Cannot delete: this source is used in inspection files!"
            : "Error: " . $e->getMessage();
    }
}

// ===== PAYMENT MODES CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment_mode'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_payment_modes (mode_name) VALUES (:name)");
        $stmt->execute([':name' => trim($_POST['mode_name'])]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_payment_modes', $db->lastInsertId(), "Added payment mode: " . $_POST['mode_name']);
        $successMessage = "Payment mode added successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Payment mode already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_mode'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_payment_modes SET mode_name = :name, status = :status WHERE id = :id");
        $stmt->execute([':name' => trim($_POST['mode_name']), ':status' => $_POST['status'], ':id' => $_POST['mode_id']]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_payment_modes', $_POST['mode_id'], "Updated payment mode");
        $successMessage = "Payment mode updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'Duplicate') !== false ? "Payment mode already exists!" : "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_payment_mode'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_payment_modes WHERE id = :id");
        $stmt->execute([':id' => $_POST['mode_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_payment_modes', $_POST['mode_id'], "Deleted payment mode");
        $successMessage = "Payment mode deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Cannot delete: this payment mode is used in inspection files!"
            : "Error: " . $e->getMessage();
    }
}

// ===== MY ACCOUNTS CRUD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_account'])) {
    try {
        $stmt = $db->prepare("INSERT INTO inspection_my_accounts (account_name, bank_name, account_number, ifsc_code) VALUES (:name, :bank, :acno, :ifsc)");
        $stmt->execute([
            ':name' => trim($_POST['account_name']),
            ':bank' => trim($_POST['account_bank_name']),
            ':acno' => trim($_POST['account_number']),
            ':ifsc' => trim($_POST['ifsc_code']) ?: null
        ]);
        $auth->logActivity($auth->getUserId(), 'create', 'inspection_my_accounts', $db->lastInsertId(), "Added account: " . $_POST['account_name']);
        $successMessage = "Account added successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    try {
        $stmt = $db->prepare("UPDATE inspection_my_accounts SET account_name = :name, bank_name = :bank, account_number = :acno, ifsc_code = :ifsc, status = :status WHERE id = :id");
        $stmt->execute([
            ':name' => trim($_POST['account_name']),
            ':bank' => trim($_POST['account_bank_name']),
            ':acno' => trim($_POST['account_number']),
            ':ifsc' => trim($_POST['ifsc_code']) ?: null,
            ':status' => $_POST['status'],
            ':id' => $_POST['account_id']
        ]);
        $auth->logActivity($auth->getUserId(), 'update', 'inspection_my_accounts', $_POST['account_id'], "Updated account");
        $successMessage = "Account updated successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        $stmt = $db->prepare("DELETE FROM inspection_my_accounts WHERE id = :id");
        $stmt->execute([':id' => $_POST['account_id']]);
        $auth->logActivity($auth->getUserId(), 'delete', 'inspection_my_accounts', $_POST['account_id'], "Deleted account");
        $successMessage = "Account deleted successfully!";
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// ===== FETCH ALL DATA =====
try {
    $banks = $db->query("SELECT * FROM inspection_banks ORDER BY bank_name")->fetchAll();
    $branches = $db->query("SELECT b.*, ib.bank_name FROM inspection_branches b JOIN inspection_banks ib ON b.bank_id = ib.id ORDER BY ib.bank_name, b.branch_name")->fetchAll();
    $sources = $db->query("SELECT * FROM inspection_sources ORDER BY source_name")->fetchAll();
    $paymentModes = $db->query("SELECT * FROM inspection_payment_modes ORDER BY mode_name")->fetchAll();
    $accounts = $db->query("SELECT * FROM inspection_my_accounts ORDER BY account_name")->fetchAll();
} catch(PDOException $e) {
    $banks = $branches = $sources = $paymentModes = $accounts = [];
    error_log("Masters fetch error: " . $e->getMessage());
}

$pageTitle = 'Inspection Masters';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Inspection Masters</h1>
            <p class="page-subtitle">Manage banks, branches, sources, payment modes & accounts</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download me-2"></i>Download
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="masters.php?download=inspection-masters-export&type=all">All Masters</a></li>
                    <li><a class="dropdown-item" href="masters.php?download=inspection-masters-export&type=banks">Banks</a></li>
                    <li><a class="dropdown-item" href="masters.php?download=inspection-masters-export&type=branches">Branches</a></li>
                    <li><a class="dropdown-item" href="masters.php?download=inspection-masters-export&type=sources">Sources</a></li>
                    <li><a class="dropdown-item" href="masters.php?download=inspection-masters-export&type=payment_modes">Payment Modes</a></li>
                    <li><a class="dropdown-item" href="masters.php?download=inspection-masters-export&type=accounts">My Accounts</a></li>
                </ul>
            </div>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkImportMastersModal">
                <i class="fas fa-file-import me-2"></i>Bulk Import
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
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="content-card">
        <div class="mb-4">
            <input type="text" class="form-control table-search" data-table="mastersActiveTable" id="mastersSearch" placeholder="Search in current tab...">
        </div>

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-banks"><i class="fas fa-university me-1"></i>Banks</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-branches"><i class="fas fa-code-branch me-1"></i>Branches</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-sources"><i class="fas fa-user-friends me-1"></i>Sources</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payment-modes"><i class="fas fa-credit-card me-1"></i>Payment Modes</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-accounts"><i class="fas fa-piggy-bank me-1"></i>My Accounts</a></li>
        </ul>

        <div class="tab-content">

            <!-- ===== BANKS TAB ===== -->
            <div class="tab-pane fade show active" id="tab-banks">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Banks</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBankModal">
                        <i class="fas fa-plus me-1"></i>Add Bank
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="table-banks">
                        <thead><tr><th>#</th><th>Bank Name</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($banks)): ?>
                                <?php foreach ($banks as $i => $bank): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($bank['bank_name']); ?></strong></td>
                                        <td><span class="badge bg-<?php echo $bank['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($bank['status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editBankModal<?php echo $bank['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteBankModal<?php echo $bank['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <!-- Edit Bank Modal -->
                                    <div class="modal fade" id="editBankModal<?php echo $bank['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <div class="modal-header"><h5 class="modal-title">Edit Bank</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <input type="hidden" name="bank_id" value="<?php echo $bank['id']; ?>">
                                                <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($bank['bank_name']); ?>" required></div>
                                                <div class="mb-3"><label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" <?php echo $bank['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo $bank['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_bank" class="btn btn-primary">Update</button></div>
                                        </form></div></div>
                                    </div>
                                    <!-- Delete Bank Modal -->
                                    <div class="modal fade" id="deleteBankModal<?php echo $bank['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <input type="hidden" name="delete_bank" value="1"><input type="hidden" name="bank_id" value="<?php echo $bank['id']; ?>">
                                            <div class="modal-header"><h5 class="modal-title">Delete Bank</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body"><p>Delete <strong><?php echo htmlspecialchars($bank['bank_name']); ?></strong>?</p><p class="text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Cannot delete if used in files or has branches.</p></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form></div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-university fa-2x mb-2 d-block"></i>No banks added yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== BRANCHES TAB ===== -->
            <div class="tab-pane fade" id="tab-branches">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Branches</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                        <i class="fas fa-plus me-1"></i>Add Branch
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="table-branches">
                        <thead><tr><th>#</th><th>Bank</th><th>Branch Name</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($branches)): ?>
                                <?php foreach ($branches as $i => $branch): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($branch['bank_name']); ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($branch['branch_name']); ?></strong></td>
                                        <td><span class="badge bg-<?php echo $branch['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($branch['status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editBranchModal<?php echo $branch['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteBranchModal<?php echo $branch['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <!-- Edit Branch Modal -->
                                    <div class="modal fade" id="editBranchModal<?php echo $branch['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <div class="modal-header"><h5 class="modal-title">Edit Branch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <input type="hidden" name="branch_id" value="<?php echo $branch['id']; ?>">
                                                <div class="mb-3"><label class="form-label">Bank</label>
                                                    <select name="bank_id" class="form-select" required>
                                                        <?php foreach ($banks as $b): ?>
                                                            <option value="<?php echo $b['id']; ?>" <?php echo $branch['bank_id'] == $b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['bank_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3"><label class="form-label">Branch Name</label><input type="text" name="branch_name" class="form-control" value="<?php echo htmlspecialchars($branch['branch_name']); ?>" required></div>
                                                <div class="mb-3"><label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" <?php echo $branch['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo $branch['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_branch" class="btn btn-primary">Update</button></div>
                                        </form></div></div>
                                    </div>
                                    <!-- Delete Branch Modal -->
                                    <div class="modal fade" id="deleteBranchModal<?php echo $branch['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <input type="hidden" name="delete_branch" value="1"><input type="hidden" name="branch_id" value="<?php echo $branch['id']; ?>">
                                            <div class="modal-header"><h5 class="modal-title">Delete Branch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body"><p>Delete branch <strong><?php echo htmlspecialchars($branch['branch_name']); ?></strong>?</p><p class="text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Cannot delete if used in files.</p></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form></div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-code-branch fa-2x mb-2 d-block"></i>No branches added yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== SOURCES TAB ===== -->
            <div class="tab-pane fade" id="tab-sources">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Sources</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSourceModal">
                        <i class="fas fa-plus me-1"></i>Add Source
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="table-sources">
                        <thead><tr><th>#</th><th>Source Name</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($sources)): ?>
                                <?php foreach ($sources as $i => $source): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($source['source_name']); ?></strong></td>
                                        <td><?php echo $source['phone'] ? htmlspecialchars($source['phone']) : '<span class="text-muted">-</span>'; ?></td>
                                        <td><span class="badge bg-<?php echo $source['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($source['status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editSourceModal<?php echo $source['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteSourceModal<?php echo $source['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <!-- Edit Source Modal -->
                                    <div class="modal fade" id="editSourceModal<?php echo $source['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <div class="modal-header"><h5 class="modal-title">Edit Source</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <input type="hidden" name="source_id" value="<?php echo $source['id']; ?>">
                                                <div class="mb-3"><label class="form-label">Source Name</label><input type="text" name="source_name" class="form-control" value="<?php echo htmlspecialchars($source['source_name']); ?>" required></div>
                                                <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($source['phone'] ?? ''); ?>"></div>
                                                <div class="mb-3"><label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" <?php echo $source['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo $source['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_source" class="btn btn-primary">Update</button></div>
                                        </form></div></div>
                                    </div>
                                    <!-- Delete Source Modal -->
                                    <div class="modal fade" id="deleteSourceModal<?php echo $source['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <input type="hidden" name="delete_source" value="1"><input type="hidden" name="source_id" value="<?php echo $source['id']; ?>">
                                            <div class="modal-header"><h5 class="modal-title">Delete Source</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body"><p>Delete source <strong><?php echo htmlspecialchars($source['source_name']); ?></strong>?</p><p class="text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Cannot delete if used in files.</p></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form></div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-user-friends fa-2x mb-2 d-block"></i>No sources added yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== PAYMENT MODES TAB ===== -->
            <div class="tab-pane fade" id="tab-payment-modes">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Payment Modes</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModeModal">
                        <i class="fas fa-plus me-1"></i>Add Payment Mode
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="table-payment-modes">
                        <thead><tr><th>#</th><th>Mode Name</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($paymentModes)): ?>
                                <?php foreach ($paymentModes as $i => $mode): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($mode['mode_name']); ?></strong></td>
                                        <td><span class="badge bg-<?php echo $mode['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($mode['status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editModeModal<?php echo $mode['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteModeModal<?php echo $mode['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <!-- Edit Mode Modal -->
                                    <div class="modal fade" id="editModeModal<?php echo $mode['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <div class="modal-header"><h5 class="modal-title">Edit Payment Mode</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <input type="hidden" name="mode_id" value="<?php echo $mode['id']; ?>">
                                                <div class="mb-3"><label class="form-label">Mode Name</label><input type="text" name="mode_name" class="form-control" value="<?php echo htmlspecialchars($mode['mode_name']); ?>" required></div>
                                                <div class="mb-3"><label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" <?php echo $mode['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo $mode['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_payment_mode" class="btn btn-primary">Update</button></div>
                                        </form></div></div>
                                    </div>
                                    <!-- Delete Mode Modal -->
                                    <div class="modal fade" id="deleteModeModal<?php echo $mode['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <input type="hidden" name="delete_payment_mode" value="1"><input type="hidden" name="mode_id" value="<?php echo $mode['id']; ?>">
                                            <div class="modal-header"><h5 class="modal-title">Delete Payment Mode</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body"><p>Delete <strong><?php echo htmlspecialchars($mode['mode_name']); ?></strong>?</p><p class="text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Cannot delete if used in files.</p></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form></div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-credit-card fa-2x mb-2 d-block"></i>No payment modes added yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== MY ACCOUNTS TAB ===== -->
            <div class="tab-pane fade" id="tab-accounts">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">My Bank Accounts</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                        <i class="fas fa-plus me-1"></i>Add Account
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="table-accounts">
                        <thead><tr><th>#</th><th>Account Name</th><th>Bank</th><th>Account No.</th><th>IFSC</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($accounts)): ?>
                                <?php foreach ($accounts as $i => $acc): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($acc['account_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($acc['bank_name']); ?></td>
                                        <td><code><?php echo htmlspecialchars($acc['account_number']); ?></code></td>
                                        <td><?php echo $acc['ifsc_code'] ? htmlspecialchars($acc['ifsc_code']) : '<span class="text-muted">-</span>'; ?></td>
                                        <td><span class="badge bg-<?php echo $acc['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($acc['status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editAccountModal<?php echo $acc['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal<?php echo $acc['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <!-- Edit Account Modal -->
                                    <div class="modal fade" id="editAccountModal<?php echo $acc['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <div class="modal-header"><h5 class="modal-title">Edit Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <input type="hidden" name="account_id" value="<?php echo $acc['id']; ?>">
                                                <div class="mb-3"><label class="form-label">Account Name</label><input type="text" name="account_name" class="form-control" value="<?php echo htmlspecialchars($acc['account_name']); ?>" required></div>
                                                <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="account_bank_name" class="form-control" value="<?php echo htmlspecialchars($acc['bank_name']); ?>" required></div>
                                                <div class="mb-3"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control" value="<?php echo htmlspecialchars($acc['account_number']); ?>" required></div>
                                                <div class="mb-3"><label class="form-label">IFSC Code</label><input type="text" name="ifsc_code" class="form-control" value="<?php echo htmlspecialchars($acc['ifsc_code'] ?? ''); ?>"></div>
                                                <div class="mb-3"><label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" <?php echo $acc['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo $acc['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_account" class="btn btn-primary">Update</button></div>
                                        </form></div></div>
                                    </div>
                                    <!-- Delete Account Modal -->
                                    <div class="modal fade" id="deleteAccountModal<?php echo $acc['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                            <input type="hidden" name="delete_account" value="1"><input type="hidden" name="account_id" value="<?php echo $acc['id']; ?>">
                                            <div class="modal-header"><h5 class="modal-title">Delete Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body"><p>Delete account <strong><?php echo htmlspecialchars($acc['account_name']); ?></strong>?</p><p class="text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i>This action cannot be undone.</p></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div>
                                        </form></div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-piggy-bank fa-2x mb-2 d-block"></i>No accounts added yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /tab-content -->
    </div><!-- /content-card -->
</div><!-- /admin-content -->

<div class="modal fade" id="bulkImportMastersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Bulk Import Masters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Supported files:</strong> CSV and XLSX
                        <br>
                        <small>Branches must reference an existing bank by `bank_name`.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Import Type</label>
                        <select name="import_type" class="form-select" id="mastersImportType" required>
                            <option value="">Select type</option>
                            <option value="banks">Banks</option>
                            <option value="branches">Branches</option>
                            <option value="sources">Sources</option>
                            <option value="payment_modes">Payment Modes</option>
                            <option value="accounts">My Accounts</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Sheet</label>
                        <input type="file" name="import_file" class="form-control" accept=".csv,.xlsx" required>
                    </div>
                    <div class="small text-muted mb-3">
                        Optional `status` column supports `active` or `inactive`.
                    </div>
                    <div class="d-grid gap-2">
                        <a href="masters.php?download_template=banks" class="btn btn-sm btn-light border text-start">
                            <i class="fas fa-download me-1"></i>Download Banks Template
                        </a>
                        <a href="masters.php?download_template=branches" class="btn btn-sm btn-light border text-start">
                            <i class="fas fa-download me-1"></i>Download Branches Template
                        </a>
                        <a href="masters.php?download_template=sources" class="btn btn-sm btn-light border text-start">
                            <i class="fas fa-download me-1"></i>Download Sources Template
                        </a>
                        <a href="masters.php?download_template=payment_modes" class="btn btn-sm btn-light border text-start">
                            <i class="fas fa-download me-1"></i>Download Payment Modes Template
                        </a>
                        <a href="masters.php?download_template=accounts" class="btn btn-sm btn-light border text-start">
                            <i class="fas fa-download me-1"></i>Download Accounts Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="bulk_import_masters" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Bank Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title">Add Bank</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" placeholder="e.g. State Bank of India" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_bank" class="btn btn-primary">Add Bank</button></div>
    </form></div></div>
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title">Add Branch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Bank</label>
                <select name="bank_id" class="form-select" required>
                    <option value="">Select Bank</option>
                    <?php foreach ($banks as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['bank_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Branch Name</label><input type="text" name="branch_name" class="form-control" placeholder="e.g. Park Street Branch" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_branch" class="btn btn-primary">Add Branch</button></div>
    </form></div></div>
</div>

<!-- Add Source Modal -->
<div class="modal fade" id="addSourceModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title">Add Source</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Source Name</label><input type="text" name="source_name" class="form-control" placeholder="e.g. Ravi Sharma" required></div>
            <div class="mb-3"><label class="form-label">Phone <small class="text-muted">(optional)</small></label><input type="text" name="phone" class="form-control" placeholder="e.g. 9876543210"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_source" class="btn btn-primary">Add Source</button></div>
    </form></div></div>
</div>

<!-- Add Payment Mode Modal -->
<div class="modal fade" id="addPaymentModeModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title">Add Payment Mode</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Mode Name</label><input type="text" name="mode_name" class="form-control" placeholder="e.g. Google Pay" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_payment_mode" class="btn btn-primary">Add Mode</button></div>
    </form></div></div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title">Add Bank Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Account Name</label><input type="text" name="account_name" class="form-control" placeholder="e.g. SBI Salary Account" required></div>
            <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="account_bank_name" class="form-control" placeholder="e.g. State Bank of India" required></div>
            <div class="mb-3"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control" placeholder="e.g. 1234567890" required></div>
            <div class="mb-3"><label class="form-label">IFSC Code <small class="text-muted">(optional)</small></label><input type="text" name="ifsc_code" class="form-control" placeholder="e.g. SBIN0001234"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_account" class="btn btn-primary">Add Account</button></div>
    </form></div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('mastersSearch');
    if (!searchInput) return;

    function updateSearchTarget() {
        const activePane = document.querySelector('.tab-pane.active.show');
        const activeTable = activePane ? activePane.querySelector('table.data-table') : null;
        searchInput.setAttribute('data-table', activeTable ? activeTable.id : '');
        searchInput.dispatchEvent(new Event('keyup'));
    }

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', updateSearchTarget);
    });

    updateSearchTarget();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
