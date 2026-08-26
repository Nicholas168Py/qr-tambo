<?php
/**
 * QR Tambo - Session Handler basado en BD
 * Reemplaza las sesiones de archivos de PHP que no funcionan en InfinityFree.
 * Compatible con PHP 7.4+.
 */

namespace Support;

use PDO;

final class DbSessionHandler implements \SessionHandlerInterface {
    /** @var PDO */
    private $db;
    /** @var string */
    private $table;

    /**
     * @param PDO $db
     * @param string $table
     */
    public function __construct(PDO $db, $table = 'sessions') {
        $this->db = $db;
        $this->table = $table;
        $this->ensureTable();
    }

    private function ensureTable() {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` VARCHAR(128) NOT NULL PRIMARY KEY,
                `data` TEXT NOT NULL,
                `last_access` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\PDOException $e) {
            error_log('[SESSION] ensureTable() falló: ' . $e->getMessage());
        }
    }

    /** @return bool */
    public function open($savePath, $sessionName) {
        return true;
    }

    /** @return bool */
    public function close() {
        return true;
    }

    /** @param string $id @return string */
    public function read($id) {
        try {
            $stmt = $this->db->prepare("SELECT `data` FROM `{$this->table}` WHERE `id` = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['data'] : '';
        } catch (\PDOException $e) {
            error_log('[SESSION] read() falló: ' . $e->getMessage());
            return '';
        }
    }

    /** @param string $id @param string $data @return bool */
    public function write($id, $data) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO `{$this->table}` (`id`, `data`, `last_access`) VALUES (?, ?, NOW()) 
                 ON DUPLICATE KEY UPDATE `data` = VALUES(`data`), `last_access` = NOW()"
            );
            return $stmt->execute([$id, $data]);
        } catch (\PDOException $e) {
            error_log('[SESSION] write() falló: ' . $e->getMessage());
            return false;
        }
    }

    /** @param string $id @return bool */
    public function destroy($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log('[SESSION] destroy() falló: ' . $e->getMessage());
            return false;
        }
    }

    /** @param int $max_lifetime @return int */
    public function gc($max_lifetime) {
        try {
            $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `last_access` < DATE_SUB(NOW(), INTERVAL ? SECOND)");
            $stmt->execute([$max_lifetime]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log('[SESSION] gc() falló: ' . $e->getMessage());
            return 0;
        }
    }
}
