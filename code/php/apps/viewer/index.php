<?php

include_once("../../WebIdAuth2.php");
include_once("functions.php");


if(!isset($_SESSION["webidauth"])) {

  try {

    $_SESSION["webidauth"] = WebIdAuth::authenticate($_SERVER["SSL_CLIENT_CERT"]);

  } catch(Exception $e) {

    // Something went super wrong

  }
}

$webIdAuth = $_SESSION["webidauth"];

if(isset($_POST["register"]) && isset($_POST["termsandconditions"])) {

  if($webIdAuth["status"] === WebIdAuth::AUTHENTICATION_SUCCESSFUL) {

    $webIdUri = $webIdAuth["x509"]["webIdUri"];

    registerWebId($webIdUri);

    $query = "prefix foaf: <http://xmlns.com/foaf/0.1/>

      select ?doc ?webid ?name ?img where {

      ?doc a foaf:PersonalProfileDocument.
      ?doc foaf:maker ?webid.
      ?webid foaf:name ?name.
      ?webid foaf:img ?img

    }";

    $exhibitJson = generateExhibitJson($query);

    file_put_contents('people.js', $exhibitJson);

  } else {
    echo print_r($webIdAuth);
  }
}

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
  <title>WebId Messages</title>
  <link rel="stylesheet" href="css/bulma/css/bulma.css"/>
  <link rel="stylesheet" href="css/viewer.css"/>
  <link href="people.js" type="application/json" rel="exhibit-data" />

  <script type="text/javascript" src="https://api.simile-widgets.org/exhibit/3.0.0rc1/exhibit-api.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
  <script defer src="https://use.fontawesome.com/releases/v5.0.7/js/all.js"></script>
</head>
<body>

  <?php if($webIdAuth["status"] === WebIdAuth::AUTHENTICATION_SUCCESSFUL) { ?>

    <div class="section">
      <div class="container">
        <form action="" method="post" >

          <label class="checkbox">
            <input type="checkbox" name="termsandconditions">
            I agree to leave my data here!
          </label>
          <button class="button is-info" name="register">Register</button>
        </form>
      </div>
    </div>

  <?php } else { ?>
    <p>A valid WebId is required to register. Learn how to <a href="github.com/webid">create your WebId</a></p>
  <?php } ?>

  <div class="section">
    <div class="container">

      <div><p>Search</p></div>
      <div class="text-search" ex:role="facet"
        ex:facetClass="TextSearch"
        ex:expressions=".label">
      </div>

      <div ex:role="view" ex:viewClass="Thumbnail">

        <div class="person-card" ex:role="lens" style="display: none;">
            <div class="image fill">
              <img ex:src-content=".img" alt="Placeholder image">
            </div>
            <p class="title is-6"><span ex:content=".name"></span></p>
            <small><a ex:href-content=".webid">Go to profile</a></small>
        </div>

      </div>
    </div>
  </div>

  <footer class="footer">
    <div class="container">
      <div class="content has-text-centered">
        <p>
          <strong>Congratulations!</strong> You have reached the footer.
        </p>
      </div>
    </div>
  </footer>
</body>

</html>
