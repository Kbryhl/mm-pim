<?php
/**
 * Database Utility Class
 * Handles database operations
 */

namespace PIM\Utils;

class Database {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Execute a query
     */
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt === false) {
            throw new \Exception("Prepare failed: " . $this->conn->error);
        }

        // Bind parameters if provided
        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new \Exception("Execute failed: " . $stmt->error);
        }

        return $stmt;
    }

    /**
     * Get single row
     */
    public function getRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all rows
     */
    public function getRows($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }

    /**
     * Insert data
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->query($sql, array_values($data));
        
        return $this->conn->insert_id;
    }

    /**
     * Update data
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "$column = ?";
        }
        $set = implode(', ', $set);
        
        $params = array_merge(array_values($data), $whereParams);
        
        $sql = "UPDATE $table SET $set WHERE $where";
        $stmt = $this->query($sql, $params);
        
        return $stmt->affected_rows;
    }

    /**
     * Delete data
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->affected_rows;
    }

    /**
     * Get param types for bind_param
     */
    private function getParamTypes($params) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }

    /**
     * Get connection
     */
    public function getConnection() {
        return $this->conn;
    }
}

?>
