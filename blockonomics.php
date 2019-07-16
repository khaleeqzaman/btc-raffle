<?php
    // your bloconomics api key
    $api_key = '';
    // the first 6 character of your wallet key from blockenomics. These are he first 6 characters after xpub, ypub, or zpub.
    $wallet_match = '';
    // Cost per raffle ticket in bitdoin
    $cost = '';
    // select an admin secret key for accessing your admin page. To access admin visit http://yoururl.com/?admin=your_admin_key
    $adminkey = '';



    $url = 'https://www.blockonomics.co/api/new_address?match_account='.$wallet_match;
    if(isset($_POST['item']) && $_POST['item'] != '') {
        $options = array(
            'http' => array(
                'header'  => 'Authorization: Bearer '.$api_key,
                'method'  => 'POST',
                'content' => ''
            )
        );
        $context = stream_context_create($options);
        $contents = file_get_contents($url, false, $context);
        $object = json_decode($contents);
        $addr = $object->address;

        $item = $_POST['item'];

        $btc_cost = $cost;
        $qr_amount = $cost;
        $stmt=$db->prepare("SELECT * FROM bitcoin WHERE item = :item");
        $stmt->bindParam(':item', $item);
        $stmt->execute();
        $row = $stmt->fetch();

        if (isset($_POST['sponsor'])) { $sponsor = $_POST['sponsor']; } else { $sponsor = NULL; }

        if (!isset($row['status']) || $row['status']  != 'pending') { 
            if ($row['id'] == '') {
                $stmt=$db->prepare("INSERT INTO bitcoin (address, bitcoin_amount, item, sponsor, ip) VALUES (:address, :bitcoin_amount, :item, :sponsor, :ip)");
                $stmt->bindParam(':address', $addr);
                $stmt->bindParam(':item', $item);
                $stmt->bindParam(':sponsor', $sponsor);
                $stmt->bindParam(':bitcoin_amount', $btc_cost);
                $stmt->bindParam(':ip', $ip);
                $stmt->execute();
            } else {
                $stmt=$db->prepare("UPDATE bitcoin SET address =:address, bitcoin_amount = :bitcoin_amount, sponsor = :sponsor, status = NULL WHERE item = :item");
                $stmt->bindParam(':address', $addr);
                $stmt->bindParam(':item', $item);
                $stmt->bindParam(':sponsor', $_POST['sponsor']);
                $stmt->bindParam(':bitcoin_amount', $btc_cost);
                $stmt->execute();
            }
            $qr = 'https://chart.googleapis.com/chart?chs=225x225&chld=L|2&cht=qr&chl=bitcoin:'.$addr.'?amount='.$qr_amount.'%26label=vmpayday.com%26message='.$item.'%20membership%20payment';
            echo '
                <link rel="stylesheet" type="text/css" href="payment_modal.css">
                <div id="modal1" class="overlay">
                    <div class="modal" id="modal">
                        <a class="cancel" href="">X</a>
                        '.$modal_header.'
                        <div class="content">
                            <img src='.$qr.' style="float:left;">
                            <p style="margin-top:0px; float:right;">'.$qr_amount.' BTC<br><span id="r" style="font-weight:900; display:block; max-width:150px; margin-top:5px"></span></p>
                            <a style="text-align:center;float:right; color:white; width:225px; background-color:orange; padding:10px; border: 1px solid black; border-radius:5px; text-decoration:none;" href="bitcoin:'.$addr.'?amount='.$btc_cost.'&label=Bitcoin 50/50 Raffle&message=1 Raffle Ticket">Open In Your Deskop Wallet</a>
                            <a style="text-align:center; float:right; color:white; background-color:#92c3ef; padding:10px; border: 1px solid black; border-radius:5px; text-decoration:none; margin-top:10px; width:225px;" href="#modal1" onclick="copy(\''.$addr.'\')" id="copy">Copy Bitcoin Address</a>
                        </div>
                        <div style="text-align:center;">* You must send the displayed BTC amount to the provided address within the given time frame. <strong>DO NOT SEND FROM EXCHANGES!</strong></div>
                    </div>
                </div>
                <script>
                    window.location.hash = "modal1";
                    var start=Date.now(),r=document.getElementById("r");
                    (function f(){
                        var diff=Date.now()-start,ns=(((6e5-diff)/1000)>>0),m=(ns/60)>>0,s=ns-m*60;
                        r.innerHTML="Order Closes in<br> "+m+":"+((""+s).length>1?"":"0")+s+" Minutes";
                        if(diff>(6e5)){start=Date.now()}
                        setTimeout(f,1000);
                    })();
                    function copy(that){
                        var inp =document.createElement(\'input\');
                        document.body.appendChild(inp)
                        inp.value = that
                        inp.select();
                        document.execCommand(\'copy\',false);
                        inp.remove();
                        document.getElementById("copy").innerHTML = \'Successfully Copied\';
                    }
                    var handle = setInterval(function(){
                        var xhr = new XMLHttpRequest();
                        xhr.open(\'GET\', \'order_complete.php?addr='.$addr.'&amount='.$btc_cost.'\');
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                clearInterval(handle);
                                document.getElementById("modal").innerHTML = xhr.responseText;
                            }
                        };
                        xhr.send();
                    }, 3000);
                </script>
            ';
        } else { 
            echo '
                <link rel="stylesheet" type="text/css" href="payment_modal.css">
                <div id="modal1" class="overlay">
                    <div class="modal" id="modal">
                        <a class="cancel" href="">X</a>
                        '.$modal_header.'
                        <div class="content" style="padding:10px;">
                            <strong>ERROR!</strong> <br><br>
                            Please wait until your cuurrent transaction completes or use a different wallet address! If you did not send '.$cost.' BTC please send payment to:<br><br> <span style="color:red;">'.$row['address'].'</span>.
                        </div>                            
                    </div>
                </div>
                <script>
                    window.location.hash = "modal1";
                </script>
            ';
        }
    }