<?php
// session_handler.php
// Menyimpan sesi di database PostgreSQL (Supabase) agar berfungsi di Serverless (Vercel)

class PgSessionHandler implements SessionHandlerInterface {
    private $conn;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string|false {
        $sql = "SELECT session_data FROM sessions WHERE session_id = $1 AND expires_at > NOW()";
        $res = pg_query_params($this->conn, $sql, array($id));
        if ($res && pg_num_rows($res) > 0) {
            $row = pg_fetch_assoc($res);
            return $row['session_data'];
        }
        return "";
    }

    public function write($id, $data): bool {
        // Hapus session lama jika ada
        pg_query_params($this->conn, "DELETE FROM sessions WHERE session_id = $1", array($id));
        
        // Simpan session baru (umur 2 jam)
        $sql = "INSERT INTO sessions (session_id, session_data, expires_at) VALUES ($1, $2, NOW() + INTERVAL '2 hours')";
        $res = pg_query_params($this->conn, $sql, array($id, $data));
        return $res ? true : false;
    }

    public function destroy($id): bool {
        $sql = "DELETE FROM sessions WHERE session_id = $1";
        $res = pg_query_params($this->conn, $sql, array($id));
        return $res ? true : false;
    }

    public function gc($maxlifetime): int|false {
        $sql = "DELETE FROM sessions WHERE expires_at < NOW()";
        pg_query($this->conn, $sql);
        return 1;
    }
}

// Set custom session handler
session_set_save_handler(new PgSessionHandler($conn), true);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
