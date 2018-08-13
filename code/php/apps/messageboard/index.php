<?php

require 'header.php';
include_once("../../lib/server-webidauth/WebIdAuth.php");
include_once("../../lib/server-webidauth/WebIdDocument.php");

session_start();

$db = new SQLite3('../../data/webid.db');

if(!isset($_SESSION["webidauth"])) {

  try {

    $_SESSION["webidauth"] = WebIdAuth::authenticate($_SERVER["SSL_CLIENT_CERT"]);

  } catch(Exception $e) {

    // Something went super wrong
    echo $e;
  }
}

$db->exec('CREATE TABLE IF NOT EXISTS comments(webid TEXT, message TEXT, postdate REAL)');

$webIdAuth = $_SESSION["webidauth"];
$webIdUri = "";
$webIdName = "Unknown";

if($webIdAuth["status"] === WebIdAuthStatus::AUTH_SUCCESS) {

  $webIdUri = $webIdAuth["x509"]["webIdUri"];
  $webId = new WebIdDocument($webIdUri);
  $webIdName = $webId->getFoafName();

  if(filter_input(INPUT_POST, 'message') !== null) {

    $message = filter_input(INPUT_POST, 'comment');
    $quotes = array("'");
    $doubles = array("''");
    $message = str_replace($quotes, $doubles, $message);

    if($message !== null && preg_match('/.*\S.*/', $message)) {
      $db->exec("INSERT INTO comments( webid, message, postdate ) VALUES ( '$webIdUri', '$message', julianday('now') )");
    }

    // header("location: index.php");
  }


  if(filter_input(INPUT_POST, 'delete') !== null) {

    $delete_id = filter_input(INPUT_POST, 'id');
    $delete_author = filter_input(INPUT_POST, 'author');

    if($delete_author == $webIdUri) {
      $db->exec("DELETE FROM comments WHERE webid='$webIdUri' AND rowid=$delete_id LIMIT 1");
    }

    //header("location: index.php");
  }
}


$results = $db->query("SELECT webid, message, rowid, CAST ((julianday('now') - julianday(postdate)) * 24 * 60 As Integer) AS timeSincePost FROM comments ORDER BY postdate DESC" );

?>


<div class="section hero is-primary">
  <div class="hero-head">
   <div class="hero-body">
    <div class="container has-text-centered">
      <h1 class="title">
        Hi there, <?=$webIdName?>!
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

    <?php if($webIdAuth["status"] === WebIdAuthStatus::AUTH_SUCCESS) { ?>


    <div class="content">

      <form action="" method="post" >

        <div class="field">
          <textarea class="textarea " type="text" placeholder="Your message..." autocomplete="off" name='comment'></textarea>
        </div>
        <button class="button is-info" name="message">Leave a Message</button>
      </form>
    </div>




    <?php } else { ?>
    <div class="content">
      Create a WebId to post on the Message Board! You can learn how to do it <a href="https://github.com/dbpedia/webid">here</a>
    </div>

    <?php } ?>

    <?php

    $count = 0;

    while ($row = $results->fetchArray()) {

      if($count > 30) {
        break;
      }

      $count++;

      $rowData = new WebIdDocument($row["webid"]);


      $rowName = $rowData->getFoafName();
      $rowImg = $rowData->getFoafImg("https://bulma.io/images/placeholders/128x128.png");


      // $age = julianday('now') - julianday($row["postdate"]);

      if(!preg_match('/.*\S.*/', $row["message"])) {
        continue;
      }

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
                <strong><?=$rowData->getFoafName()?></strong> <small><a class="webid-link" href=<?=$row["webid"]?>><?=$row["webid"]?></a></small> <small class="is-pulled-right"><?=$row["timeSincePost"]?> Minutes ago</small>
                <br>
                <?=$row["message"]?>
              </div>
              <?php if($webIdAuth["status"] === WebIdAuthStatus::AUTH_SUCCESS && $row["webid"] === $webIdUri) { ?>

              <form action="" method="post" >
                <input type="hidden" name="id" value="<?=$row['rowid']?>">
                <input type="hidden" name="author" value="<?=$row['webid']?>">
                <button class="button is-info is-pulled-right" name="delete">Delete Comment</button>
              </form>

              <?php } ?>
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
