<?php
    if (isset($_GET['status'])) { $status = strip_tags($_GET['status']); } else {die; }
    if (isset($_GET['txid'])) { $txid = strip_tags($_GET['txid']); } else { die; }
    if (isset($_GET['addr'])) { $addr = strip_tags($_GET['addr']); } else { die; }
    if (isset($_GET['value'])) { $value = strip_tags($_GET['value']) / 100000000; } else { die; }

    if ($status == 1) {
        $check_url = 'https://www.blockchain.com/btc/tx/'.$txid;
        $check = file_get_contents($check_url);

        if (strpos($check, $addr) !== false) {
            include $_SERVER['DOCUMENT_ROOT'].'/config.php';
            $stmt=$db->prepare("UPDATE bitcoin SET status = 'pending' WHERE address = :address AND bitcoin_amount = :amount");
            $stmt->bindParam(':address', $addr);
            $stmt->bindParam(':amount', $value);
            $stmt->execute();
    }
}

if ($status == 2) {
    include $_SERVER['DOCUMENT_ROOT'].'/config.php';
    include $_SERVER['DOCUMENT_ROOT'].'/blockonomics.php';

    $check_url = 'https://www.blockchain.com/btc/tx/'.$txid;
    $check = file_get_contents($check_url);
    if (strpos($check, $txid) !== false) {
        $stmt = $db->prepare("SELECT transaction FROM transactions WHERE transaction = :transaction");
        $stmt->bindParam(':transaction', $txid);
        $stmt->execute();
        $check = $stmt->fetchColumn();
        if($check == '') { 
            if ($value == $cost) {
                $stmt=$db->prepare("INSERT INTO transactions (transaction) VALUE (:transaction)");
                $stmt->bindParam(':transaction', $txid);
                $stmt->execute();
            
                $stmt=$db->prepare("UPDATE bitcoin SET status = 'complete', tickets = tickets + 1 WHERE address = :address AND bitcoin_amount = :amount");
                $stmt->bindParam(':address', $addr);
                $stmt->bindParam(':amount', $value);
                $stmt->execute();

                $stmt=$db->prepare("SELECT sponsor FROM bitcoin WHERE address = :address AND bitcoin_amount = :amount");
                $stmt->bindParam(':address', $addr);
                $stmt->bindParam(':amount', $value);
                $stmt->execute();
                $sponsor = $stmt->fetchColumn();
 
                if (isset($sponsor) AND $sponsor != '') { 
                    $stmt=$db->prepare("UPDATE bitcoin SET tickets = tickets + 1 WHERE id = :sponsor");
                    $stmt->bindParam(':sponsor', $sponsor);
                    $stmt->execute();
                }
            }
        }
    }
}
?>