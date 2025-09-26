<?php
// functions.php - Shared helpers

function fetch_all($conn, $query, $types = '', $params = []) {
    $stmt = $conn->prepare($query);
    if (!$stmt) return false;
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) return false;
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function prepare_and_execute($conn, $sql, $types = '', $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    if ($types) {
        $refs = [];
        foreach ($params as $k => $v) $refs[$k] = &$params[$k];
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
    $stmt->execute();
    return $stmt;
}

function toKhmerNumber($number) {
    $khmerDigits = ['0'=>'០','1'=>'១','2'=>'២','3'=>'៣','4'=>'៤','5'=>'៥','6'=>'៦','7'=>'៧','8'=>'៨','9'=>'៩'];
    return strtr($number, $khmerDigits);
}
?>
