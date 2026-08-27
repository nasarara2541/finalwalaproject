<?php
require_once __DIR__ . '/../../config/database.php';

class PurchaseOrderModel
{
    public static function getSuppliers()
    {
        $conn = getDbConnection();
        $stmt = sqlsrv_query($conn, "SELECT SUPPLIER_CODE, SUPPLIER_NAME FROM ST_Supplier ORDER BY SUPPLIER_NAME");
        if ($stmt === false) return ['success' => false, 'error' => self::errStr()];
        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return ['success' => true, 'data' => $rows];
    }

    public static function getNextTransNo()
    {
        $conn = getDbConnection();
        $stmt = sqlsrv_query($conn, "SELECT ISNULL(MAX(Trans_no), 0) + 1 AS next_no FROM ST_STOCKRECEIPT");
        if ($stmt === false) return ['success' => false, 'error' => self::errStr()];
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return ['success' => true, 'next_trans_no' => (int)$row['next_no']];
    }

    public static function saveHeader(array $h)
    {
        $conn = getDbConnection();
        if (!sqlsrv_begin_transaction($conn)) {
            return ['success' => false, 'error' => 'Transaction failed.'];
        }

        try {
            $invoiceNo = (int)($h['invoice_no'] ?? 0);
            if ($invoiceNo <= 0) {
                throw new Exception('Invalid or missing Invoice number.');
            }

            $checkSql = "SELECT COUNT(*) AS cnt FROM ST_STOCKRECEIPT WHERE Invoice_no = ?";
            $checkStmt = sqlsrv_query($conn, $checkSql, [$invoiceNo]);
            if ($checkStmt === false) throw new Exception('Duplicate check failed.');
            $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
            if ($row && (int)$row['cnt'] > 0) {
                throw new Exception("Invoice #{$invoiceNo} already exists.");
            }
            $sql = "
                INSERT INTO ST_STOCKRECEIPT
                    (Invoice_no, INVOICE_DATE, SUPPLIER_CODE, RECEIVED_DATE,
                     TOTAL_AMOUNT, STATUS, RECEIVED_BY, User_id,
                     FLAT_DISCOUNT, FLAT_GST, IS_LOOSE_PURCHASE,
                     TOTAL_PACKS, TOTAL_ITEMS, TOTAL_UNITS, TOTAL_BONUS)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $params = [
                $invoiceNo,
                $h['invoice_date'],
                $h['supplier_code'],
                $h['received_date'],
                (float)$h['total_amount'],
                $h['status'] ?? 'Y',
                $h['received_by'] ?? 'admin',
                $h['user_id'] ?? 'admin',
                (float)($h['flat_discount'] ?? 0),
                (float)($h['flat_gst'] ?? 0),
                (!empty($h['is_loose_purchase'])) ? '1' : '0',
                (int)($h['total_packs'] ?? 0),
                (int)($h['total_items'] ?? 0),
                (int)($h['total_units'] ?? 0),
                (int)($h['total_bonus'] ?? 0),
            ];

            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) throw new Exception('Header insert failed: ' . self::errStr());
            $idStmt = sqlsrv_query($conn, "SELECT Trans_no FROM ST_STOCKRECEIPT WHERE Invoice_no = ?", [$invoiceNo]);
            $transNo = 0;
            if ($idStmt !== false) {
                $idRow = sqlsrv_fetch_array($idStmt, SQLSRV_FETCH_ASSOC);
                $transNo = $idRow ? (int)$idRow['Trans_no'] : 0;
            }
            sqlsrv_commit($conn);
            return ['success' => true, 'invoice_no' => $invoiceNo, 'trans_no' => $transNo];
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function saveDetails(int $invoiceNo, array $items)
    {
        $conn = getDbConnection();
        if (!sqlsrv_begin_transaction($conn)) {
            return ['success' => false, 'error' => 'Transaction failed.'];
        }
        try {
            $detailSql = "
                INSERT INTO ST_STOCKRECEIPTDETAIL
                    (Invoice_no, STOCK_NUMBER, ITEMS_RECEIVED, ITEMS_AVAILABLE, EXPIRY_DATE, BATCH_NO,
                     UNITS_PERITEM, WPRICE_PERITEM, PPRICE_PERITEM, PRICE_PERITEM, Price_PerUnit, PPrice_PerUnit,
                     BONUS_QTY, GROUP_NAME, Tax_Percentage, Tax_amount, Record_date, Update_Status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), 'Updated')
            ";
            $updateStockSql = "UPDATE Item_Stock SET QTY_INHAND = QTY_INHAND + ? WHERE STOCK_NUMBER = ?";

            foreach ($items as $item) {
                $units = (int)($item['units_per_item'] ?? 1);
                if ($units < 1) $units = 1;

                $purchasePerPack = (float)$item['purchase_price'];
                $salePerPack     = (float)$item['sale_price'];
                $purchasePerUnit = $purchasePerPack / $units;
                $salePerUnit     = $salePerPack / $units;
                $bonusQty        = (int)($item['bonus_qty'] ?? 0);
                $totalReceived   = (int)$item['qty_received'] + $bonusQty;
                $availableQty    = $totalReceived * $units;

                $gstPct = (float)($item['gst'] ?? 0);
                $taxAmount = round($salePerPack * $gstPct / 100, 2);

                $dParams = [
                    $invoiceNo,
                    (int)$item['stock_number'],
                    (int)$item['qty_received'],
                    $availableQty,
                    $item['expiry_date'],
                    $item['batch_no'],
                    $units,
                    null,
                    $purchasePerPack,
                    $salePerPack,
                    $salePerUnit,
                    $purchasePerUnit,
                    $bonusQty,
                    $item['group'] ?? null,
                    $gstPct,
                    $taxAmount,
                ];

                $dStmt = sqlsrv_query($conn, $detailSql, $dParams);
                if ($dStmt === false) throw new Exception('Detail insert failed: ' . self::errStr());

                $uStmt = sqlsrv_query($conn, $updateStockSql, [$availableQty, (int)$item['stock_number']]);
                if ($uStmt === false) throw new Exception('Stock update failed: ' . self::errStr());
                if (sqlsrv_rows_affected($uStmt) === 0) {
                    throw new Exception("Stock update matched 0 rows for stock# {$item['stock_number']}.");
                }
            }

            sqlsrv_commit($conn);
            return ['success' => true];
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function updateStock(int $invoiceNo, array $header, array $items)
    {
        $conn = getDbConnection();
        if (!sqlsrv_begin_transaction($conn)) {
            return ['success' => false, 'error' => 'Could not begin transaction.'];
        }
        try {
            $chkSql = "SELECT Invoice_no FROM ST_STOCKRECEIPT WHERE Invoice_no = ?";
            $chkStmt = sqlsrv_query($conn, $chkSql, [$invoiceNo]);
            if ($chkStmt === false || !sqlsrv_fetch_array($chkStmt, SQLSRV_FETCH_ASSOC)) {
                throw new Exception("Invoice #{$invoiceNo} not found.");
            }

            $hSql = "
                UPDATE ST_STOCKRECEIPT SET
                    INVOICE_DATE  = ?,
                    SUPPLIER_CODE = ?,
                    TOTAL_AMOUNT  = ?,
                    STATUS        = ?,
                    RECEIVED_BY   = ?,
                    FLAT_DISCOUNT = ?,
                    FLAT_GST      = ?,
                    IS_LOOSE_PURCHASE = ?,
                    TOTAL_PACKS   = ?,
                    TOTAL_ITEMS   = ?,
                    TOTAL_UNITS   = ?,
                    TOTAL_BONUS   = ?
                WHERE Invoice_no = ?
            ";
            $hStmt = sqlsrv_query($conn, $hSql, [
                $header['invoice_date'],
                $header['supplier_code'],
                $header['total_amount'],
                $header['status'] ?? 'Y',
                $header['received_by'] ?? 'admin',
                (float)($header['flat_discount'] ?? 0),
                (float)($header['flat_gst'] ?? 0),
                (!empty($header['is_loose_purchase'])) ? '1' : '0',
                (int)($header['total_packs'] ?? 0),
                (int)($header['total_items'] ?? 0),
                (int)($header['total_units'] ?? 0),
                (int)($header['total_bonus'] ?? 0),
                $invoiceNo,
            ]);
            if ($hStmt === false) throw new Exception('Header update failed: ' . self::errStr());

            $oldStmt = sqlsrv_query(
                $conn,
                "SELECT STOCK_NUMBER, ITEMS_RECEIVED, UNITS_PERITEM, BONUS_QTY FROM ST_STOCKRECEIPTDETAIL WHERE Invoice_no = ?",
                [$invoiceNo]
            );
            if ($oldStmt === false) throw new Exception('Could not fetch old details: ' . self::errStr());
            $oldRows = [];
            while ($row = sqlsrv_fetch_array($oldStmt, SQLSRV_FETCH_ASSOC)) {
                $oldRows[] = $row;
            }

            foreach ($oldRows as $old) {
                $totalReceived = (int)$old['ITEMS_RECEIVED'] + (int)$old['BONUS_QTY'];
                $oldBottles = $totalReceived * (int)$old['UNITS_PERITEM'];
                $revStmt = sqlsrv_query(
                    $conn,
                    "UPDATE Item_Stock SET QTY_INHAND = QTY_INHAND - ? WHERE STOCK_NUMBER = ?",
                    [$oldBottles, (int)$old['STOCK_NUMBER']]
                );
                if ($revStmt === false) {
                    throw new Exception('Stock reversal failed for stock# ' . $old['STOCK_NUMBER'] . ': ' . self::errStr());
                }
            }

            $delStmt = sqlsrv_query($conn, "DELETE FROM ST_STOCKRECEIPTDETAIL WHERE Invoice_no = ?", [$invoiceNo]);
            if ($delStmt === false) throw new Exception('Could not delete old details: ' . self::errStr());

            $insSql = "
                INSERT INTO ST_STOCKRECEIPTDETAIL
                    (Invoice_no, STOCK_NUMBER, ITEMS_RECEIVED, ITEMS_AVAILABLE, EXPIRY_DATE, BATCH_NO,
                     UNITS_PERITEM, WPRICE_PERITEM, PPRICE_PERITEM, PRICE_PERITEM, Price_PerUnit, PPrice_PerUnit,
                     BONUS_QTY, GROUP_NAME, Tax_Percentage, Tax_amount, Record_date, Update_Status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), 'Updated')
            ";

            foreach ($items as $item) {
                $units = (int)($item['units_per_item'] ?? 1);
                if ($units < 1) $units = 1;

                $purchasePerPack = (float)$item['purchase_price'];
                $salePerPack     = (float)$item['sale_price'];
                $purchasePerUnit = $purchasePerPack / $units;
                $salePerUnit     = $salePerPack / $units;

                $qtyReceived = (int)$item['qty_received'];
                $bonusQty    = (int)($item['bonus_qty'] ?? 0);
                $totalReceived = $qtyReceived + $bonusQty;
                $availableQty  = $totalReceived * $units;

                $gstPct = (float)($item['gst'] ?? 0);
                $taxAmount = round($salePerPack * $gstPct / 100, 2);

                $insParams = [
                    $invoiceNo,
                    (int)$item['stock_number'],
                    $qtyReceived,
                    $availableQty,
                    $item['expiry_date'] ?? null,
                    $item['batch_no'] ?? '',
                    $units,
                    null,
                    $purchasePerPack,
                    $salePerPack,
                    $salePerUnit,
                    $purchasePerUnit,
                    $bonusQty,
                    $item['group'] ?? null,
                    $gstPct,
                    $taxAmount,
                ];
                $insStmt = sqlsrv_query($conn, $insSql, $insParams);
                if ($insStmt === false) {
                    throw new Exception('Detail insert failed for stock# ' . $item['stock_number'] . ': ' . self::errStr());
                }

                $updStmt = sqlsrv_query(
                    $conn,
                    "UPDATE Item_Stock SET QTY_INHAND = QTY_INHAND + ? WHERE STOCK_NUMBER = ?",
                    [$availableQty, (int)$item['stock_number']]
                );
                if ($updStmt === false) {
                    throw new Exception('Stock update failed for stock# ' . $item['stock_number'] . ': ' . self::errStr());
                }
            }

            sqlsrv_commit($conn);
            return ['success' => true, 'invoice_no' => $invoiceNo];
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function getInvoice(int $invoiceNo)
    {
        $conn = getDbConnection();
        $hSql = "
        SELECT Invoice_no, INVOICE_DATE, SUPPLIER_CODE, RECEIVED_DATE, TOTAL_AMOUNT, STATUS, RECEIVED_BY, 
                FLAT_DISCOUNT, FLAT_GST, IS_LOOSE_PURCHASE, Trans_no, TOTAL_PACKS, TOTAL_ITEMS, TOTAL_UNITS, TOTAL_BONUS
        FROM ST_STOCKRECEIPT WHERE Invoice_no = ?
    ";
        $hStmt = sqlsrv_query($conn, $hSql, [$invoiceNo]);
        if ($hStmt === false) {
            return ['success' => false, 'error' => self::errStr()];
        }
        $header = sqlsrv_fetch_array($hStmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($hStmt);
        if (!$header) {
            sqlsrv_close($conn);
            return ['success' => false, 'error' => 'Invoice not found.'];
        }

        if ($header['INVOICE_DATE'] instanceof DateTime) {
            $header['INVOICE_DATE'] = $header['INVOICE_DATE']->format('Y-m-d');
        }
        if ($header['RECEIVED_DATE'] instanceof DateTime) {
            $header['RECEIVED_DATE'] = $header['RECEIVED_DATE']->format('Y-m-d');
        }
        $header['TOTAL_AMOUNT'] = (float)$header['TOTAL_AMOUNT'];
        $header['FLAT_DISCOUNT'] = (float)$header['FLAT_DISCOUNT'];
        $header['FLAT_GST'] = (float)$header['FLAT_GST'];
        $header['Trans_no'] = (int)$header['Trans_no'];
        $header['TOTAL_PACKS'] = (int)$header['TOTAL_PACKS'];
        $header['TOTAL_ITEMS'] = (int)$header['TOTAL_ITEMS'];
        $header['TOTAL_UNITS'] = (int)$header['TOTAL_UNITS'];
        $header['TOTAL_BONUS'] = (int)$header['TOTAL_BONUS'];

        $dSql = "
        SELECT d.*, s.ITEM_NAME, s.BRAND_NAME, s.ITEM_TYPE FROM ST_STOCKRECEIPTDETAIL d
        LEFT JOIN Item_Stock s ON s.STOCK_NUMBER = d.STOCK_NUMBER WHERE d.Invoice_no = ?
    ";
        $dStmt = sqlsrv_query($conn, $dSql, [$invoiceNo]);
        if ($dStmt === false) {
            return ['success' => false, 'error' => self::errStr()];
        }

        $items = [];
        while ($row = sqlsrv_fetch_array($dStmt, SQLSRV_FETCH_ASSOC)) {
            if ($row['EXPIRY_DATE'] instanceof DateTime) {
                $row['EXPIRY_DATE'] = $row['EXPIRY_DATE']->format('Y-m-d');
            }
            $row['ITEMS_RECEIVED'] = (int)$row['ITEMS_RECEIVED'];
            $row['ITEMS_AVAILABLE'] = (int)$row['ITEMS_AVAILABLE'];
            $row['UNITS_PERITEM'] = (int)$row['UNITS_PERITEM'];
            $row['PPRICE_PERITEM'] = (float)$row['PPRICE_PERITEM'];
            $row['PRICE_PERITEM'] = (float)$row['PRICE_PERITEM'];
            $row['BONUS_QTY'] = (int)($row['BONUS_QTY'] ?? 0);
            $row['Tax_Percentage'] = (float)($row['Tax_Percentage'] ?? 0);
            $row['Tax_amount'] = (float)($row['Tax_amount'] ?? 0);
            $items[] = $row;
        }
        sqlsrv_free_stmt($dStmt);
        sqlsrv_close($conn);

        return [
            'success' => true,
            'header' => $header,
            'items'  => $items,
        ];
    }

    public static function getInvoices(string $search = '')
    {
        $conn = getDbConnection();
        if ($search !== '') {
            $sql = "SELECT TOP 50 Invoice_no, Trans_no, INVOICE_DATE, SUPPLIER_CODE, TOTAL_AMOUNT
                    FROM ST_STOCKRECEIPT WHERE CAST(Invoice_no AS VARCHAR) LIKE ?
                    ORDER BY Invoice_no DESC";
            $stmt = sqlsrv_query($conn, $sql, ['%' . $search . '%']);
        } else {
            $sql = "SELECT TOP 50 Invoice_no, Trans_no, INVOICE_DATE, SUPPLIER_CODE, TOTAL_AMOUNT
                    FROM ST_STOCKRECEIPT ORDER BY Invoice_no DESC";
            $stmt = sqlsrv_query($conn, $sql);
        }
        if ($stmt === false) return ['success' => false, 'error' => self::errStr()];

        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return ['success' => true, 'data' => $rows];
    }

    private static function errStr()
    {
        $e = sqlsrv_errors();
        return $e ? $e[0]['message'] : 'Unknown error';
    }
}