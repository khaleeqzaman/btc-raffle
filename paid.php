<?php
            echo '
                <link rel="stylesheet" type="text/css" href="payment_modal.css">
                <div id="modal1" class="overlay">
                    <div class="modal" id="modal">
                        <a class="cancel" href="">X</a>
                        Update Patment Satus<br><br>
                        <div class="content">
                            <form method="post">
                                <input type="hidden" name="id" value="'.$_GET['id'].'">
                                <input style="width:70%;" type="text" name="pay" placeholder="Enter TXID of Payment">
                                <button type="submit">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
                <script>
                    window.location.hash = "modal1";
                </script>
            ';
?>