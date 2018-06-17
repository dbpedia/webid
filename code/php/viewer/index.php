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

$db->exec('CREATE TABLE IF NOT EXISTS comments(webid TEXT, message TEXT, postdate REAL)');

if($webid->authenticateClient()) {

  $logged_in = true;
  $webid_uri = $webid->getUri();
  $webid_name = $webid->getName();

  if(filter_input(INPUT_POST, 'message') !== null) {

    $message = filter_input(INPUT_POST, 'comment');

    $quotes = array("'");
    $doubles = array("''");

    $message = str_replace($quotes, $doubles, $message);

    if($message !== '') {
      $db->exec("INSERT INTO comments( webid, message, postdate ) VALUES ( '$webid_uri', '$message', julianday('now') )");

      header("location: index.php");
    }
  }
}

$results = $db->query("SELECT webid, message, CAST ((julianday('now') - julianday(postdate)) * 24 * 60 As Integer) AS timeSincePost FROM comments ORDER BY postdate DESC" );

?>


<div class="section hero is-primary">
  <div class="hero-head">
   <div class="hero-body">
    <div class="container has-text-centered">
      <h1 class="title">
        Hi there, <?=$webid_name?>!
      </h1>
      <h2 class="subtitle">
        Welcome to the Message Board!
      </h2>
    </div>
  </div>
</div>
</div>
<div class="section viewer"> 

  <div class="container">

          <?php if($logged_in) { ?>
 
    
        <div class="content">
       
          <form action="" method="post" >

          <div class="field">
            <textarea class="textarea " type="text" placeholder="Your message..." autocomplete="off" name='comment'></textarea>
          </div>
          <button class="button is-info" name="message">Leave a Message</button>
        </form>
        </div>



        
    <?php } ?>

    <?php 

    $count = 0;

    while ($row = $results->fetchArray()) { 

      if($count > 30) {
        break;
      }

      $count++;
          // Parse the WebId with a TTL parser
      $rowParser = ARC2::getRDFParser();
      $rowParser->parse($row["webid"]);

      //$count = $count + 1;
          // Create an index from the parsed TTL
      $rowIndex = $rowParser->getSimpleIndex();

      $rowName = $rowIndex[$row["webid"]]["http://xmlns.com/foaf/0.1/name"][0];
      $rowImg = $rowIndex[$row["webid"]]["http://xmlns.com/foaf/0.1/img"][0];

      if($rowImg == null) {
        $rowImg = "https://bulma.io/images/placeholders/128x128.png";
      }

      // $age = julianday('now') - julianday($row["postdate"]);

     
      ?>
      <div class="box">
        <article class="media">
          <div class="media-left">
            <figure class="image is-64x64">
              <img src=<?=$rowImg?> alt="Image">
            </figure>
          </div>
          <div class="media-content">
            <div class="content">
              <p>
                <strong><?=$rowIndex[$row["webid"]]["http://xmlns.com/foaf/0.1/name"][0]?></strong> <small><a class="webid-link" href=<?=$row["webid"]?>><?=$row["webid"]?></a></small> <small class="is-pulled-right"><?=$row["timeSincePost"]?> Minutes ago</small>
                <br>
                <?=$row["message"]?>
              </div>
            </div>

          </article>
        </div>
        <?php } ?>

        <!--
        <nav class="pagination" role="navigation" aria-label="pagination">
          <a class="pagination-previous" title="This is the first page" disabled>Previous</a>
          <a class="pagination-next">Next page</a>
          <ul class="pagination-list">
            <li>
              <a class="pagination-link is-current" aria-label="Page 1" aria-current="page">1</a>
            </li>
            <li>
              <a class="pagination-link" aria-label="Goto page 2">2</a>
            </li>
            <li>
              <a class="pagination-link" aria-label="Goto page 3">3</a>
            </li>
          </ul>
        </nav>-->
      </div>
     
   </div>

   <?php require 'footer.php';
