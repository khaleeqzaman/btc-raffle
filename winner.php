<?php 
    include $_SERVER['DOCUMENT_ROOT'].'/config.php';
    $entries = '';
    $stmt = $db->prepare('SELECT id FROM bitcoin');
    $stmt->execute();
    $row = $stmt->fetchAll();

    foreach ($row AS $row) { 
        $stmt = $db->prepare('SELECT tickets FROM bitcoin WHERE id = :id');
        $stmt->bindParam(':id', $row['id']);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        while ($count > 0) { 
            $count--;
            $stmt = $db->prepare('SELECT item FROM bitcoin WHERE id = :id');
            $stmt->bindParam(':id', $row['id']);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            $entries .= $result.',';
        }
    }
    $entries = rtrim($entries, ',');
    $arr = explode (",", $entries);
    $random = shuffle($arr);
    $winner = $arr[$random];

    $stmt = $db->prepare('INSERT INTO winners (address) VALUES (:address)');
    $stmt->bindParam(':address', $winner);
    $stmt->execute();

    echo $winner;

    $stmt = $db->prepare('DELETE FROM bitcoin WHERE 1');
    $stmt->execute();

    $stmt = $db->prepare('DELETE FROM transactions WHERE 1');
    $stmt->execute();

