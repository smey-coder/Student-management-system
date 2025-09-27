<?php
// functions.php - Shared helpers

function fetch_all($conn, string $sql, string $types = '', array $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $res = $stmt->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function prepare_and_execute($conn, string $sql, string $types = '', array $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    return $stmt;
}

function toKhmerNumber(string $number): string {
    $khmerDigits = ['0'=>'០','1'=>'១','2'=>'២','3'=>'៣','4'=>'៤','5'=>'៥','6'=>'៦','7'=>'៧','8'=>'៨','9'=>'៩'];
    return strtr($number, $khmerDigits);
}
?>
