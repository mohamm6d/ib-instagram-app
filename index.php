<?php
require_once dirname(__FILE__) . '/kernel.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram App - Hashtag Search</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
</head>
<body>
<div class="container">

<?php
//Initialize the app
$instagramApp = new InstagramHashtagSearch($_ENV['INSTAGRAM_APIKEY'], $_ENV['INSTAGRAM_APISECRET'], $_ENV['CALLBACK_URL']);

if (!empty($_GET['code'])) {

// grab OAuth callback code
    $code = $_GET['code'];
    $instagramApp->generateAccessToken($code);
    echo $instagramApp->ShowMessage('Access token has been generated :)');
}

if (!empty($_POST['api']) && !empty($_POST['hashtag'])) {

    $instagramApp->searchByHashtag($_POST['hashtag']);

} elseif (!empty($_POST['graphql']) && !empty($_POST['hashtag'])) {

    $instagramApp->searchByGraphql($_POST['hashtag']);

}
if (!empty($_POST['hashtag'])) {

    $fileName = $instagramApp->writeToFile();

    if ($fileName !== '') {
        echo $instagramApp->ShowMessage('All media with related hashtag has been stored into file. You may download the file by clicking <a href="' . $fileName . '">here</a>');
    } else {
        echo $instagramApp->ShowMessage('There was an error while downloading media and saving into file.');
    }
}

?>
</div>
<div class="hero py-4 text-center bg-dark text-white">
    <div class="container">
        <h1>Search given hashtag on Instagram & Save to file<h1>
            <small>Version 1.0</small>
    </div>
</div>
<div class="container">
<div class="mt-4 row">
    <div class="col-md-6">
        <div class="card h-100">
            <h4 class="card-header">Download with Instagram API</h4>
                <div class="card-body">
                    This API version is deprecated from 29th Jun 2020
                    <form action="" method="post">
                    <a class="btn btn-primary rounded-0" href='<?php echo $instagramApp->getLoginUrl() ?>'>Login with Instagram</a><hr>
                    <?php
if (isset($_SESSION['access_token'])) {
    ?>
                    <br>
                    <input type="hidden" name="api" value="1">
                    <label for="hashtag">Enter Hashtag</label>
                    <input type="text" value="<?php echo (!empty($_POST['hashtag']))  ? $_POST['hashtag'] : '' ?>" name="hashtag" id="hashtag" class=" mb-2 form-control">
                    <button type="submit" class="btn btn-outline-dark rounded-0">Search Now</button>

                    <?php
}
else{
    echo "First need to login and generate an access token!";
}

?>

                    </form>
                </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <h4 class="card-header">
                Download with Instagram Graphql
            </h4>
            <div class="card-body">
                <form action="" method="post">
                    <input type="hidden" name="graphql" value="1">
                    <label for="hashtag">Enter Hashtag</label>
                    <input type="text" value="<?php echo (!empty($_POST['hashtag'])) ? $_POST['hashtag'] : '' ?>"  name="hashtag" id="hashtag" class=" mb-2 form-control">
                    <button type="submit" class="btn btn-outline-dark rounded-0">Search Now</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>
</html>




