<?php

include_once("../../lib/server-webidauth/WebIdAuth.php");
include_once("functions.php");


session_start();

if(!isset($_SESSION["webidauth"])) {

  try {

    $_SESSION["webidauth"] = WebIdAuth::authenticate($_SERVER["SSL_CLIENT_CERT"]);

  } catch(Exception $e) {

    // Something went super wrong
    echo $e;
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
      OPTIONAL { ?webid foaf:name ?name. }
      OPTIONAL { ?webid foaf:img ?img. }

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

  <nav class="navbar has-shadow is-transparent">

    <div class="container">
    <div class="navbar-brand">

    </div>

    <div class="navbar-menu">
    <div class="navbar-start">
      <a class="navbar-item" href="">
        WebId Community Viewer
      </a>

    </div>

    <div class="navbar-end">

      <div class="navbar-item">

      <div id="text-search" class="navbar-item" ex:role="facet"
          ex:facetClass="TextSearch"
          ex:expressions=".name">
      </div>

        <?php if($webIdAuth["status"] === WebIdAuth::AUTHENTICATION_SUCCESSFUL) { ?>


          <p class="control">
         <a class="bd-tw-button button" id="open-register-modal" >
           Register
         </a>
       </p>

        <?php } else { ?>
          <p><?=$webIdAuth["message"]?> Learn how to <a href="github.com/webid">create your WebId</a></p>
        <?php } ?>

      </div>
  </div>
  </div>
</div>
</nav>

<div class="modal" id="register-modal">
  <div class="modal-background" id="register-modal-background"></div>
  <div class="modal-card">
    <form action="" method="post" >
    <header class="modal-card-head">
      <p class="modal-card-title">Register for the Community Viewer</p>
      <button class="delete" aria-label="close" id="register-modal-close"></button>
    </header>
    <section class="modal-card-body">
      <!-- Content ... -->
      <form action="" method="post" >

        <label class="checkbox">
          <div class="content">
          <p>
            By registering, you agree to let us save a copy of your WebId for display on this website. Additionaly, your WebId data will be accessible via our SPARQL endpoint at <a href="http://webid.dbpedia.org:8890/sparql">webid.dbpedia.org/sparql</a>
          </p>

        </div>
          <input type="checkbox" name="termsandconditions">
          I agree to leave my data here!
        </label>

    </section>
    <footer class="modal-card-foot">
      <button class="button is-success" name="register">Register</button>
      <button class="button">No, thanks</button>
    </footer>
    </form>
  </div>
</div>

  <div id="people-viewer" class="section">
    <div class="container">


      <div class="card-container" ex:role="view" ex:viewClass="Thumbnail">

        <div class="person-card card" ex:role="lens" style="display: none;">
            <div class="image fill">
             <img ex:src-content=".img" alt="Placeholder image">
            </div>
            <div class="content">
              <p class="title is-6"><span ex:content=".name"></span></p>
              <small><a ex:href-content=".webid">Go to profile</a></small>
            </div>
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

<script type="text/javascript">

$("#open-register-modal").click(function() {
  $("#register-modal").toggleClass("is-Active");
});

$("#register-modal-background").click(function() {
  $("#register-modal").toggleClass("is-Active");
});

$("#register-modal-close").click(function() {
  $("#register-modal").toggleClass("is-Active");
});


</script>

<?php


// print_r($webIdAuth);
 ?>
</html>
