<?php
// Anoosha's original config/db.php pointed at her own PC's SQL Server
// (DESKTOP-DVB1KBB\SQLEXPRESS, SQL-auth login "phpuser") via a ../../.env
// that was never actually included with her files -- unreachable from this
// machine either way. Since her screens were built against the same schema
// as this project, not a genuinely separate database, this just reuses our
// real, already-working connection (session-aware Water/MedStock switching,
// login gate, UTF-8 charset) instead of a second, parallel one. $conn ends
// up defined here exactly like it would from her own file.
require_once __DIR__ . '/../../includes/db.php';
