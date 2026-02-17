<?php
class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Get all records from a table
    public function getAll() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->query($sql);
    }
    
    // Get a single record by ID
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = {$id} LIMIT 1";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Insert a new record
    public function insert($data) {
        $fields = array_keys($data);
        $values = array_values($data);
        
        // Escape and quote string values
        foreach ($values as &$value) {
            if (is_string($value)) {
                $value = "'" . $this->db->getConnection()->real_escape_string($value) . "'";
            }
        }
        
        $fieldsStr = implode(',', $fields);
        $valuesStr = implode(',', $values);
        
        $sql = "INSERT INTO {$this->table} ({$fieldsStr}) VALUES ({$valuesStr})";
        return $this->db->query($sql);
    }
    
    // Update a record
    public function update($id, $data) {
        $updates = [];
        
        foreach ($data as $field => $value) {
            if (is_string($value)) {
                $value = "'" . $this->db->getConnection()->real_escape_string($value) . "'";
            }
            $updates[] = "{$field} = {$value}";
        }
        
        $updatesStr = implode(',', $updates);
        
        $sql = "UPDATE {$this->table} SET {$updatesStr} WHERE id = {$id}";
        return $this->db->query($sql);
    }
    
    // Delete a record
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = {$id}";
        return $this->db->query($sql);
    }
}
?> 