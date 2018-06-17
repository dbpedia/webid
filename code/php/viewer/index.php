<?php 

require 'header.php';


include_once("../semsol-arc2/ARC2.php");
include_once("../phpseclib/Math/BigInteger.php");
include_once("../phpseclib/Crypt/RSA.php");
include_once("../phpseclib/File/X509.php");
include_once("../webidauth/WebIdAuth.php");


$logged_in = false;
$x509 = new File_X509();
$db = new SQLite3('../data/webid.db');
$webid = new WebIdAuth();
$webid_name = "Unkown";


if($webid->authenticateClient()) {

    $logged_in = true;
    $webid_uri = $webid->getUri();
    $webid_name = $webid->getName();

    if(filter_input(INPUT_POST, 'message') !== null) {

      $message = filter_input(INPUT_POST, 'comment');

      if($message !== '') {
        $db->exec("INSERT INTO comments( webid, message ) VALUES ( '$webid_uri', '$message' )");

        $_POST['message'] = null;
      }
    }
}

$results = $db->query("SELECT * FROM comments");

?>


<div class="section hero is-primary">
    <div class="hero-head">
     <div class="hero-body">
        <div class="container has-text-centered">
          <h1 class="title">
            Hi there, <?=$webid_name?>!
        </h1>
        <h2 class="subtitle">
            Welcome to the Comment Section!
        </h2>
    </div>
</div>
</div>
</div>
<div class="section viewer"> 
    <div class="container">
        <div class="content">

        <!--
        <h3>WebId Information</h3>
        <table class="table is-fullwidth">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>WebId URI</td>
                    <td>safasldjfksdflj</td>
                </tr>
                
            </tbody>
        </table> -->
        <h3>Recent Messages</h3>
        <?php while ($row = $results->fetchArray()) { 
          // Parse the WebId with a TTL parser
          $rowParser = ARC2::getRDFParser();
          $rowParser->parse($row["webid"]);

          // Create an index from the parsed TTL
          $rowIndex = $rowParser->getSimpleIndex();

          ?>
        <div class="box">
          <!--<?=print_r($rowIndex)?>-->
          <div class="content">
            <p>
              <strong><?=$rowIndex[$row["webid"]]["http://xmlns.com/foaf/0.1/name"][0]?></strong> <small><a class="webid-link" href=<?=$row["webid"]?>><?=$row["webid"]?></a></small>
              <br>
              <?=$row["message"]?>
            
          </div>
        </div>
        <?php } ?>

        <?php if($logged_in) { ?>
        <h3>Leave a Message</h3>
        <form action="" method="post" >

            <div class="field">
                <textarea class="textarea " type="text" placeholder="Your message..." autocomplete="off" name='comment'></textarea>
            </div>
            <button class="button is-link" name="message">Leave a Message</button>
        </form>
        <?php } ?>
    </div>
</div>
</div>

<?php require 'footer.php';
