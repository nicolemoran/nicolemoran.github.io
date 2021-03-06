<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
      <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
     <meta name="description" content="The website for Wildlife Center volunteers">
    <meta name="keywords" content="wildlife, volunteer, virginia">
    <meta name="author" content="Shanice McCormick and Nicole Moran">
    
    <title>Login | Wildlife Center Volunteers</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Caveat+Brush" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Arimo|Caveat+Brush" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="row">
      <div class="col-sm-3">
      <!--Spacer-->
      </div> 
      <div class="col-xs-12 col-sm-6 vellum">
        <div class="row">
        <div class="col-sm-6">
          <h3>WILDLIFE CENTER OF VIRGINIA</h3>
          <img src="images/nature.png" alt="Logo" class="logo img-responsive">
        </div>
      </div><!--End row--> 

      <div class="row">
        <div class="col-sm-2">
            <!--Spacer-->
        </div>
        <div class="col-sm-8">
            <h1 class="h1">Sign In</h1>
            <form action="login.php" method="post">
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                <input id="name" type="text" class="form-control" name="email" placeholder="E-mail">
              </div>
              <br>
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input id="password" type="password" class="form-control" name="password" placeholder="Password">
              </div>
                <?php
                require ('SQLConnection.php');
                $newSQL = new SQLConnection();
                $conn = new mysqli($newSQL->getServerName(), $newSQL->getUserName(), $newSQL->getPassword(), $newSQL->getDB());

                if (isset($_POST["login"]))
                {
                    session_start();
                    if (!empty($_POST['email']) and !empty($_POST['password'])) {
                        $email = $_POST['email'];
                        $password = $_POST['password'];
                        echo $email . "<br/>" . $password . "</br>";

                        //gets hash to compare to
                        $query = "SELECT passwd FROM login where email = ?";

                        //Prepares and sends the query
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("s",$email);
                        if ($stmt->execute()) {

                            //Gets the results and captures them
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $hash = $row['passwd'];
                            }
                        }
                        //initializes hash if no password found
                        if (!isset($hash)) {
                            $hash = 0;
                        }
                        //checks hash
                        $correct = password_verify($password, $hash);

                        if ($correct) {
                            echo "<br/>" . $hash . "<br/>";

                            //determine user type
                            $query = "select personid, teamLeadid, adminid from login where email = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("s",$email);
                            $stmt->execute();

                            if ($stmt->execute()) {
                                $result = $stmt->get_result();

                                while ($row = $result->fetch_assoc()) {
                                    $person = $row["personid"];
                                    $teamLead = $row["teamLeadid"];
                                    $admin = $row["adminid"];
                                }
                            }

                            $query = "";
                            if ($person != '') {
                                $query = "SELECT p.personid, permissionLevel FROM person p inner join login l on p.personid = l.personid where email = ? and passwd = ?";
                            } elseif ($teamLead != '') {
                                $query = "SELECT t.teamleadid, permissionLevel FROM teamlead t inner join login l on t.teamleadid = l.teamleadid where email = ? and passwd = ?";
                            } elseif ($admin != '') {
                                $query = "SELECT a.adminid, permissionLevel FROM administrator a inner join login l on a.adminid = l.adminid where email = ? and passwd = ?";
                            } else {
                                $query = "SELECT p.personid, permissionLevel FROM person p inner join login l on p.personid = l.personid where email = ? and passwd = ?";
                            }

                            echo $query . "</br>";
                            //$query = "SELECT p.personid FROM person p inner join login l on p.personid = l.personid where email = ? and passwd = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("ss",$email,$hash);
                            $stmt->execute();

                            $result = $stmt->get_result();
                            $count = mysqli_num_rows($result);
                            echo "Count:".$count;

                            //If the posted values are equal to the database values, then session will be created for the user.
                            if ($count == 1) {
                                while ($row = $result->fetch_assoc()) {
                                    if ($person != '') {
                                        $_SESSION['personid'] = $row['personid'];
                                        $_SESSION['permission'] = $row['permissionLevel'];
                                    } elseif ($teamLead != '') {
                                        $_SESSION['personid'] = $row['teamleadid'];
                                        $_SESSION['permission'] = $row['permissionLevel'];
                                    } elseif ($admin != '') {
                                        $_SESSION['personid'] = $row['adminid'];
                                        $_SESSION['permission'] = $row['permissionLevel'];
                                    } else {
                                        $_SESSION['personid'] = $row['personid'];
                                        $_SESSION['permission'] = $row['permissionLevel'];
                                    }
                                }

                            } else {
                                $invalidLogin = "Incorrect Login"; //this doesn't display anwhere
                            }

                            if ($admin != '' || $teamLead != '') {
                                if (isset($_SESSION['personid']) && isset($_SESSION['permission'])) {
                                    header("Location: /adminhome.php"); //takes user to homepage after login
                                }
                            } else {
                                if (isset($_SESSION['personid']) && isset($_SESSION['permission'])) {
                                    header("Location: /home.php"); //takes user to homepage after login
                                }
                            }
                            /*if (isset($_SESSION['personid']) && isset($_SESSION['permission'])) {
                                header("Location: /home.php"); //takes user to homepage after login
                            }*/
                        }
                    }
                }

                ?>
                <a href=http://DropDataBass.app/ForgotPasswordPrompt.php class="back">Forgot your password?</a></br>
                <a href="home.php"><input type="submit" name="login" class="btn btn-default" value ="Sign In"></a>
                <a href="apply-main.php"> <button type="button" class="btn btn-default">Sign Up </button></a>
                <a href="clockTime.php"> <button type="button" class="btn btn-default">Clock Time </button></a>
            </form>
        </div><!--End column-->
      </div><!--End row-->

    </div> <!--End column-->
  </div><!--End rowr-->

    <!-- Footer -->
    <footer class="w3-container w3-padding-64 w3-center w3-xlarge">
        <i class="fa fa-facebook-official w3-hover-opacity" onclick="window.location='https://www.facebook.com/wildlifecenter/'"></i>
        <i class="fa fa-instagram w3-hover-opacity" onclick="window.location='https://www.instagram.com/explore/locations/292750036/'"></i>
        <i class="fa fa-youtube w3-hover-opacity" onclick="window.location='https://www.youtube.com/user/WildlifeCenterVA'"></i>
        <i class="fa fa-twitter w3-hover-opacity" onclick="window.location='https://twitter.com/WCVtweets'"></i>
        <i class="fa fa-linkedin w3-hover-opacity" onclick="window.location='https://www.linkedin.com/company/wildlife-center-of-va'"></i>
        <p class="w3-medium">Visit us at <a href="http://wildlifecenter.org/" target="_blank">WildLifeCenter.org</a></p>
        <p class="w3-medium">?? 2017 The Wildlife Center of Virginia. All Rights Reserved.</p>
    </footer>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>