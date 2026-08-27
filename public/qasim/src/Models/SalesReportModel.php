<?php
require_once __DIR__ . '/../../config/database.php';

class SalesReportModel
{
    private static function costExpr()
    {
        return "
            COALESCE(
                srd.PPRICE_PERITEM / NULLIF(srd.UNITS_PERITEM, 0),
                fb.PPRICE_PERITEM / NULLIF(fb.UNITS_PERITEM, 0),
                s.PURCHASE_PRICE / NULLIF(s.UNITS_PERITEM, 0)
            )
        ";
    }

    public static function getProfitSummary($startDate, $endDate)
    {
        $conn = getDbConnection();
        $costExpr = self::costExpr();

        $sql = "
            SELECT
                FORMAT(t.Trans_date, 'MMM-yyyy', 'EN-US') AS Month,
                SUM(td.quantity) AS TotalQty,
                CAST(SUM(td.amount) AS DECIMAL(12,2)) AS TotalSale,
                CAST(SUM(td.quantity * ISNULL($costExpr, 0)) AS DECIMAL(12,2)) AS TotalCost,
                CAST(SUM(td.amount) - SUM(td.quantity * ISNULL($costExpr, 0)) AS DECIMAL(12,2)) AS TotalProfit
            FROM trans_detail td
            JOIN [Transaction] t ON td.Trans_no = t.Trans_no
            LEFT JOIN ST_STOCKRECEIPTDETAIL srd
                ON srd.STOCK_NUMBER = td.stock_number
                AND srd.Invoice_no = td.Invoice_No
                AND srd.PPRICE_PERITEM IS NOT NULL AND srd.UNITS_PERITEM IS NOT NULL
            OUTER APPLY (
                SELECT TOP 1 f.PPRICE_PERITEM, f.UNITS_PERITEM
                FROM ST_STOCKRECEIPTDETAIL f
                WHERE f.STOCK_NUMBER = td.stock_number
                AND f.PPRICE_PERITEM IS NOT NULL AND f.UNITS_PERITEM IS NOT NULL AND f.UNITS_PERITEM > 0
                ORDER BY f.Invoice_no DESC
            ) fb
            LEFT JOIN Item_Stock s ON s.STOCK_NUMBER = td.stock_number
            WHERE CAST(t.Trans_date AS DATE) BETWEEN ? AND ?
            GROUP BY YEAR(t.Trans_date), MONTH(t.Trans_date), FORMAT(t.Trans_date, 'MMM-yyyy', 'EN-US')
            ORDER BY YEAR(t.Trans_date) DESC, MONTH(t.Trans_date) DESC
        ";
        $stmt = sqlsrv_query($conn, $sql, [$startDate, $endDate]);
        if ($stmt === false) return ['success' => false, 'error' => self::errStr()];
        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $row['TotalQty'] = (int)($row['TotalQty'] ?? 0);
            $row['TotalSale'] = (float)($row['TotalSale'] ?? 0);
            $row['TotalCost'] = (float)($row['TotalCost'] ?? 0);
            $row['TotalProfit'] = (float)($row['TotalProfit'] ?? 0);
            $rows[] = $row;
        }
        sqlsrv_free_stmt($stmt);

        $totalsSql = "
            SELECT
                CAST(SUM(td.amount) AS DECIMAL(12,2)) AS totalSale,
                CAST(SUM(td.quantity * ISNULL($costExpr, 0)) AS DECIMAL(12,2)) AS totalCost,
                CAST(SUM(td.amount) - SUM(td.quantity * ISNULL($costExpr, 0)) AS DECIMAL(12,2)) AS totalProfit
            FROM trans_detail td
            JOIN [Transaction] t ON td.Trans_no = t.Trans_no
            LEFT JOIN ST_STOCKRECEIPTDETAIL srd
                ON srd.STOCK_NUMBER = td.stock_number
                AND srd.Invoice_no = td.Invoice_No
                AND srd.PPRICE_PERITEM IS NOT NULL AND srd.UNITS_PERITEM IS NOT NULL
            OUTER APPLY (
                SELECT TOP 1 f.PPRICE_PERITEM, f.UNITS_PERITEM
                FROM ST_STOCKRECEIPTDETAIL f
                WHERE f.STOCK_NUMBER = td.stock_number
                AND f.PPRICE_PERITEM IS NOT NULL AND f.UNITS_PERITEM IS NOT NULL AND f.UNITS_PERITEM > 0
                ORDER BY f.Invoice_no DESC
            ) fb
            LEFT JOIN Item_Stock s ON s.STOCK_NUMBER = td.stock_number
            WHERE CAST(t.Trans_date AS DATE) BETWEEN ? AND ?
        ";
        $totalsStmt = sqlsrv_query($conn, $totalsSql, [$startDate, $endDate]);
        if ($totalsStmt === false) return ['success' => false, 'error' => self::errStr()];
        $totalsRow = sqlsrv_fetch_array($totalsStmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($totalsStmt);
        sqlsrv_close($conn);

        return [
            'success' => true,
            'data' => $rows,
            'totals' => [
                'totalSale'   => (float)($totalsRow['totalSale'] ?? 0),
                'totalCost'   => (float)($totalsRow['totalCost'] ?? 0),
                'totalProfit' => (float)($totalsRow['totalProfit'] ?? 0),
            ]
        ];
    }

    public static function getProfitPerTransaction($startDate, $endDate)
    {
        $conn = getDbConnection();
        $costExpr = self::costExpr();

        $sql = "
            SELECT
                t.Trans_no,
                t.Trans_date,
                t.Disc_percentage,
                CAST(t.Gross_amount AS DECIMAL(12,2)) AS GrossAmount,
                CAST(t.Gross_amount - t.Trans_amount AS DECIMAL(12,2)) AS DiscAmount,
                CAST(t.Trans_amount AS DECIMAL(12,2)) AS NetAmount,
                CAST(SUM(td.amount) - SUM(td.quantity * ISNULL($costExpr, 0)) AS DECIMAL(12,2)) AS Profit
            FROM trans_detail td
            JOIN [Transaction] t ON td.Trans_no = t.Trans_no
            LEFT JOIN ST_STOCKRECEIPTDETAIL srd
                ON srd.STOCK_NUMBER = td.stock_number
                AND srd.Invoice_no = td.Invoice_No
                AND srd.PPRICE_PERITEM IS NOT NULL AND srd.UNITS_PERITEM IS NOT NULL
            OUTER APPLY (
                SELECT TOP 1 f.PPRICE_PERITEM, f.UNITS_PERITEM
                FROM ST_STOCKRECEIPTDETAIL f
                WHERE f.STOCK_NUMBER = td.stock_number
                AND f.PPRICE_PERITEM IS NOT NULL AND f.UNITS_PERITEM IS NOT NULL AND f.UNITS_PERITEM > 0
                ORDER BY f.Invoice_no DESC
            ) fb
            LEFT JOIN Item_Stock s ON s.STOCK_NUMBER = td.stock_number
            WHERE CAST(t.Trans_date AS DATE) BETWEEN ? AND ?
            GROUP BY t.Trans_no, t.Trans_date, t.Disc_percentage, t.Gross_amount, t.Trans_amount
            ORDER BY t.Trans_date DESC, t.Trans_no DESC
        ";
        $stmt = sqlsrv_query($conn, $sql, [$startDate, $endDate]);
        if ($stmt === false) return ['success' => false, 'error' => self::errStr()];
        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if ($row['Trans_date'] instanceof DateTime) $row['Trans_date'] = $row['Trans_date']->format('Y-m-d');
            $row['Disc_percentage'] = (float)($row['Disc_percentage'] ?? 0);
            $row['GrossAmount'] = (float)($row['GrossAmount'] ?? 0);
            $row['DiscAmount'] = (float)($row['DiscAmount'] ?? 0);
            $row['NetAmount'] = (float)($row['NetAmount'] ?? 0);
            $row['Profit'] = (float)($row['Profit'] ?? 0);
            $rows[] = $row;
        }
        sqlsrv_free_stmt($stmt);

        $totalsSql = "
            SELECT
                CAST(SUM(t.Gross_amount) AS DECIMAL(12,2)) AS gross,
                CAST(SUM(t.Gross_amount - t.Trans_amount) AS DECIMAL(12,2)) AS disc,
                CAST(SUM(t.Trans_amount) AS DECIMAL(12,2)) AS net,
                CAST(SUM(td.amount) - SUM(td.quantity * ISNULL($costExpr, 0)) AS DECIMAL(12,2)) AS profit
            FROM trans_detail td
            JOIN [Transaction] t ON td.Trans_no = t.Trans_no
            LEFT JOIN ST_STOCKRECEIPTDETAIL srd
                ON srd.STOCK_NUMBER = td.stock_number
                AND srd.Invoice_no = td.Invoice_No
                AND srd.PPRICE_PERITEM IS NOT NULL AND srd.UNITS_PERITEM IS NOT NULL
            OUTER APPLY (
                SELECT TOP 1 f.PPRICE_PERITEM, f.UNITS_PERITEM
                FROM ST_STOCKRECEIPTDETAIL f
                WHERE f.STOCK_NUMBER = td.stock_number
                AND f.PPRICE_PERITEM IS NOT NULL AND f.UNITS_PERITEM IS NOT NULL AND f.UNITS_PERITEM > 0
                ORDER BY f.Invoice_no DESC
            ) fb
            LEFT JOIN Item_Stock s ON s.STOCK_NUMBER = td.stock_number
            WHERE CAST(t.Trans_date AS DATE) BETWEEN ? AND ?
        ";
        $totalsStmt = sqlsrv_query($conn, $totalsSql, [$startDate, $endDate]);
        if ($totalsStmt === false) return ['success' => false, 'error' => self::errStr()];
        $totalsRow = sqlsrv_fetch_array($totalsStmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($totalsStmt);
        sqlsrv_close($conn);

        return [
            'success' => true,
            'data' => $rows,
            'totals' => [
                'gross'  => (float)($totalsRow['gross'] ?? 0),
                'disc'   => (float)($totalsRow['disc'] ?? 0),
                'net'    => (float)($totalsRow['net'] ?? 0),
                'profit' => (float)($totalsRow['profit'] ?? 0),
            ]
        ];
    }

    public static function getDaywiseReport($startDate, $endDate)
    {
        $conn = getDbConnection();
        $costExpr = self::costExpr();

        $sql = "
            SELECT
                CAST(t.Trans_date AS DATE) AS Day,
                SUM(t.Gross_amount - t.Trans_amount) AS Discount,
                SUM(CASE WHEN t.Trans_type = 'Sale' OR t.Trans_type IS NULL THEN t.Gross_amount ELSE 0 END) AS SaleAmount,
                SUM(CASE WHEN t.Trans_type = 'Return' THEN t.Gross_amount ELSE 0 END) AS SaleReturn,
                SUM(td.quantity * ISNULL($costExpr, 0)) AS Cost
            FROM [Transaction] t
            LEFT JOIN trans_detail td ON t.Trans_no = td.Trans_no
            LEFT JOIN ST_STOCKRECEIPTDETAIL srd
                ON srd.STOCK_NUMBER = td.stock_number
                AND srd.Invoice_no = td.Invoice_No
                AND srd.PPRICE_PERITEM IS NOT NULL AND srd.UNITS_PERITEM IS NOT NULL
            OUTER APPLY (
                SELECT TOP 1 f.PPRICE_PERITEM, f.UNITS_PERITEM
                FROM ST_STOCKRECEIPTDETAIL f
                WHERE f.STOCK_NUMBER = td.stock_number
                AND f.PPRICE_PERITEM IS NOT NULL AND f.UNITS_PERITEM IS NOT NULL AND f.UNITS_PERITEM > 0
                ORDER BY f.Invoice_no DESC
            ) fb
            LEFT JOIN Item_Stock s ON s.STOCK_NUMBER = td.stock_number
            WHERE CAST(t.Trans_date AS DATE) BETWEEN ? AND ?
            GROUP BY CAST(t.Trans_date AS DATE)
            ORDER BY Day
        ";
        $stmt = sqlsrv_query($conn, $sql, [$startDate, $endDate]);
        if ($stmt === false) return ['success' => false, 'error' => self::errStr()];
        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $day = $row['Day'];
            if ($day instanceof DateTime) $day = $day->format('Y-m-d');
            $netSale = $row['SaleAmount'] - $row['SaleReturn'];
            $profit = $netSale - $row['Cost'];
            $profitPct = ($netSale != 0) ? ($profit / $netSale) * 100 : 0;
            $rows[] = [
                'Day'       => $day,
                'Discount'  => (float)($row['Discount'] ?? 0),
                'SaleAmount'=> (float)($row['SaleAmount'] ?? 0),
                'SaleReturn'=> (float)($row['SaleReturn'] ?? 0),
                'NetSale'   => (float)$netSale,
                'Cost'      => (float)($row['Cost'] ?? 0),
                'Profit'    => (float)$profit,
                'ProfitPct' => (float)$profitPct,
            ];
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        return ['success' => true, 'data' => $rows];
    }

    private static function errStr()
    {
        $e = sqlsrv_errors();
        return $e ? $e[0]['message'] : 'Unknown error';
    }
}