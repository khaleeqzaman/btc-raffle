<?php
    include $_SERVER['DOCUMENT_ROOT'].'/config.php';
    $ip = getRealIpAddr();
    include $_SERVER['DOCUMENT_ROOT'].'/blockonomics.php';

    $stmt=$db->prepare("SELECT SUM(tickets) FROM bitcoin");
    $stmt->execute();
    $tickets_total = $stmt->fetchColumn();
    if ($tickets_total == '') { $tickets_total = '0'; }

    $stmt=$db->prepare("SELECT COUNT(*) FROM transactions");
    $stmt->execute();
    $enries_total = $stmt->fetchColumn();

    $pot = ($enries_total * $cost / 2).' BTC';
 
    if(isset($_GET['r'])) {
        $r = strip_tags($_GET['r']);
        $stmt=$db->prepare("SELECT COUNT(*) AS count, ip FROM bitcoin WHERE id = :id ORDER BY ip");
        $stmt->bindParam(':id', $r);
        $stmt->execute();
        $row = $stmt->fetch();
        $valid = $row['count'];
        $visitor_ip = $row['ip'];
    }
    if (isset($valid) && $valid > 0 AND $visitor_ip != $ip) { 
        $stmt=$db->prepare("UPDATE bitcoin SET hits = hits + 1 WHERE id = :id");
        $stmt->bindParam(':id', $r);
        $stmt->execute();
    }
    if (isset($_GET['aff'])) {
        $item = strip_tags($_GET['aff']);
        $stmt = $db->prepare('SELECT COUNT(*) AS cnt, id, tickets, hits FROM bitcoin WHERE item = :item AND status IS NOT NULL ORDER BY id, tickets, hits');
        $stmt->bindParam(':item', $item);
        $stmt->execute();
        $row = $stmt->fetch();
    }

    if(isset($_POST['pay']) && isset($_GET['admin']) && $_GET['admin'] == $adminkey) { 
        $pay_id = strip_tags($_POST['id']);
        $tx = strip_tags($_POST['pay']);
        $stmt=$db->prepare('UPDATE winners SET paid_txid = :txid WHERE id = :id');
        $stmt->bindParam(':id', $pay_id);
        $stmt->bindParam(':txid', $tx);
        $stmt->execute();
        $success = 'Success!';

        echo '
            <script>
               setTimeout(function(){
                   window.location.href = window.location.href.substr(0, window.location.href.indexOf("#")); 
               }, 5000);
            </script>
        ';
    }
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <div id="truetop"></div>
        <div class="top">
            <h1 class="top_content">
                The Bitcoin 50/50 Raffle
            </h1>
        </div>
        <div class="lower_top">
            <form class="lower_top_content" method="POST">
                <?php if(isset($r) && $visitor_ip != $ip) { ?>
                    <input type="hidden" name="sponsor" value="<?php echo $r; ?>">
                <?php } ?>
                <input id="inputbtc" type="text" name="item" placeholder="YOUR BITCOIN ADDRESS">
                <button type=""submit">Buy A Ticket</button>
            </form>
        </div>
        <div class="middle_top">
            <div class="middle_top_content">
                <?php 
                    if (isset($_GET['admin']) && $_GET['admin'] == $adminkey) { ?>
                    <div style="text-align:center;font-size: 15pt;">
                        <strong>Admin Area</strong>
                    </div>
                    <?php if(isset($success)) { echo '<div style="background-color: #4aea4a; padding: 10px; width: 98%; margin: 20px 0; border-radius: 5px;">Success!</div>'; } ?>
                    <button type="submit" id="select_winner">Select Winner</button>
                    <input id="winner" placeholder="Winner address appears here" name="winner" value="" style="width:75%"><br><br>
                    <div style="text-align: center;">
                        <?php 
                            $stmt=$db->prepare("SELECT * FROM winners ORDER BY id DESC LIMIT 5");
                            $stmt->execute();
                            $last_winners = $stmt->fetchAll();
                            foreach ($last_winners AS $last_winners) { 
                                echo $last_winners['address'].' ';
                                if (isset($last_winners['paid_txid'])) {
                                    echo 'paid<br>';
                                } else { 
                                    echo '<span id="p">not paid <a href="" class="set_paid" data-id="'.$last_winners['id'].'" style="color:blue; text-decoration:none">Set Paid</a><span><br>';
                                }
                            } 
                        ?>
                    </div>
                <?php
                    } else if (isset($row['cnt']) AND $row['cnt'] != '') {
                        $stmt = $db->prepare('SELECT COUNT(*) FROM bitcoin WHERE sponsor = :id');
                        $stmt->bindParam(':id', $row['id']);
                        $stmt->execute();
                        $total_referred = $stmt->fetchColumn();
                ?>
                    <div style="text-align:center;font-size: 15pt;">
                        <strong>Referral Address:</strong> 
                        <a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/?r=<?php echo $row['id']; ?>">
                            https://<?php echo $_SERVER['SERVER_NAME']; ?>/?r=<?php echo $row['id']; ?>
                        </a>
                        <div style="text-align: left; margin-top: 30px; margin-left: 25%;">Tickets: 
                            <span style="float:right; margin-right: 35%;"><?php echo $row['tickets']; ?></span>
                        </div>
                        <div style="text-align: left; margin-top: 10px; margin-left: 25%;">Hits: 
                            <span style="float:right; margin-right: 35%;"><?php echo $row['hits']; ?></span>
                        </div>
                        <div style="text-align: left; margin-top: 10px; margin-left: 25%;">Signups: 
                            <span style="float:right; margin-right: 35%;"><?php echo $total_referred; ?></span>
                        </div>
                    </div>
                <?php } else { ?>
                    <strong>Instructions:</strong> To join the bitcoin 50/50 raffle submit your bitcoin address above. After submitting your address you will be presented with a payment address. Send <?php echo $cost; ?> BTC to the provided address. Your payment receipt acts as your first ticket. You can come back and purchase as many tickets as you wish.<br><br><strong>Note:</strong> Only send one payment to the address provided. You must re-enter your wallet address and get a new payment address to purchase a second ticket. Only request a second ticket after your first ticket appears in your account!<br><br><strong>Affiliates:</strong> Your bitcoin address acts as your account info where you can obtain your affiliate link and check how many tickets you have. Each person you refer who buys a ticket is another entry into the raffle you. Afer making a payment you will find your deails at <span style="color:blue;">https://<?php echo $_SERVER['SERVER_NAME']; ?>/?aff=YOUR_BITCOIN_ADDRESS</span><br><br><strong>Winners:</strong> One winner is selected and paid on the first day of each month. The txid from the previous month's winner will be displayed below.
                <?php } ?>
            </div>
        </div>

        <?php
            $stmt=$db->prepare("SELECT paid_txid FROM winners ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $last_winner_txid = $stmt->fetchColumn();
            if ($last_winner_txid == '') {
                $last_winner_txid = 'Last Months Payout: <span style="float:right;">First Run</a>';
            } else {
                $last_winner_txid = 'Last Months Payout: <span style="float:right"><a target="_blank" href="https://live.blockcypher.com/btc/tx/'.$last_winner_txid.'">Click Here</a></span>';
            }
        ?>

        <div class="middle_bottom">
            <div class="middle_bottom_content">
                <strong>This Months Tickets:</strong> <span style="float:right"><?php echo $tickets_total; ?></span></div>
            <div class="middle_bottom_content2">
                <strong>Prize Pot:</strong><span style="float:right"><?php echo $pot; ?></span>
            </div>
            <div class="middle_bottom_content3">
                <?php echo $last_winner_txid; ?>
            </div>
        </div>
        <div class="bottom">
            <h1 class="bottom_content">
                This Months Ticket Purchase Transactions<br><br>
                <?php 
                    $stmt = $db->prepare("SELECT * FROM transactions");
                    $stmt->execute();
                    $transactions = $stmt->fetchAll();
                    foreach ($transactions AS $transactions) { 
                        echo '<a style="font-size:14pt; color:blue;" target="_blank" href="https://live.blockcypher.com/btc/tx/'.$transactions['transaction'].'">'.$transactions['transaction'].'</a><br>';
                    }
                ?>
            </h1>
        </div>
        <script>
            function doIt(e) {
                var e = e || event;
                if (e.keyCode == 32) return false;
            }
            function pasteIt(e) {
                var e = e || event;
                this.value = this.value.replace(/\s/g,'');
            }
            window.onload = function(){
                var inp = document.getElementById("inputbtc");
                inp.onkeydown = doIt;
                inp.oninput = pasteIt
            };
            document.getElementById("select_winner").onclick = function(e){
                e.preventDefault();
                var c = confirm('Warning this will remove all data from current contest excpet previous winners! This includes all addresses and txid\'s!');
                if (c == false) {
                    return false
                } else { 
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'winner.php');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            document.getElementById("winner").value = xhr.responseText;
                        }
                    };
                    xhr.send();
                };
            };
            var set_paid= document.getElementsByClassName("set_paid");
            for (var i=0; i < set_paid.length; i++) {
                set_paid[i].onclick = function(e){
                    e.preventDefault();
                    var id = this.getAttribute('data-id');
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'paid.php?id='+id);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            var myDiv = document.getElementById('truetop');
                            myDiv.innerHTML = xhr.responseText;
                            var myScripts = myDiv.getElementsByTagName("script");
                            if (myScripts.length > 0) {
                                eval(myScripts[0].innerHTML);
                            }
                        }
                    };
                    xhr.send();
                };
            };
        </script>
    </body>
</html>
