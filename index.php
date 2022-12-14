<?php

session_start();

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$validated = false;

if (isset($_POST['Submit']))
{
    // check the captcha
    if (empty($_SESSION['captcha_code'] ) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) != 0)
    {
        $message = "The captcha was not correct. Please try again!";
    }
    else // Captcha verification is Correct. Final Code Execute here!
    {
        $validated = true;

        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $nickname = $_POST['name'];
        $nickname = str_replace(";",":",$nickname);
        //$nickname = "a";

        if ($nickname)
        {
            $table = array();
            $handle = fopen("numbers.txt", "c+");

            // will block untill the lock is acquired
            flock($handle, LOCK_EX);

            while(($line=fgets($handle))!==false)
            {
                $line = preg_replace("/\r|\n/", "", $line);
                if ($line) { array_push($table,$line); }
            }
        }


        /* $table = file("numbers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); */

        $values = array_values($table);

        $last_entry = end($values);
        $last_entry_split = explode(";", $last_entry);
        $last_number = $last_entry_split[0];

        if ($last_entry_split[1] == $nickname)
        {
            $message = "Something went wrong, please try again.";
            $validated = false;
        }
        else
        {
            $new_number = intval($last_number) + 1;

            // skip some numbers because they were already handed out
            if ($new_number == 1000)
            {
                $new_number = 1313;
            }

            $new_entry = $new_number . ";" . $nickname . ";" . date("Y-m-d H:i:s") . "\n";

            /* file_put_contents("numbers.txt", $new_entry, FILE_APPEND | LOCK_EX) || die("Unable to write to file"); */
            fwrite($handle,$new_entry);
            fflush($handle);
            fclose($handle);

            $message=  "Jo " . $nickname . ", you will be number " . $new_number . ", remember this!";
        }
    }
}
?>


<html>

    <head>
        <meta charset="utf-8">
        <title>Arrestee number generator</title>
        <link href="./css/style.css" rel="stylesheet">
        <script src="js/script.js"></script>
        <script src="css/style.css"></script>
    </head>

    <body>
        <!--<div class='an_header'>
            <h1>Arrestee number generator</h1>
        </div> --!>
        <div class="an_whole">
        <div class="an_message">

<?php
if (isset($_POST['Submit']))
{
    if (!$validated)
    {
        echo "<div class='an_errormessage'>" . $message . "</div>";
    }
    else
    {
        echo "<div>" . $message . "</div<";
        header('Location: out.php?name='.$nickname.'&number='.$new_number);
    }
}
?>
        </div>
        <div class ='an_form'>
<?php
if (!$validated)
{
?>
        <form action="" method="post" name="an_form" id="an_form" >
            <table>
                <tr>
                    <td colspan='2'><h1>Arrestee number generator</h1></td>
                </tr>

                <tr>
                    <td>Action name (not your real name!):</td>
                    <td><input type="text" name="name"></td>
                </tr>
                <tr>
                    <td colspan='2' class='an_tdcenter'>
                        <img src="captcha.php?rand=<?php echo rand();?>" id='captchaimg'><br>
                    </td>
                </tr>
                <tr>
                    <td>
                        What is the code in the image above?
                    </td>
                    <td>
                        <input width='100%' id="captcha_code" name="captcha_code" type="text">
                    </td>
                </tr>
                <tr>
                    <td class='an_tdcenter' colspan='2'>
                        <i>Can't read the image? click <a href='javascript:
        refreshCaptcha();'>here</a> to refresh.</td></i>
                    </td>
                </tr>
            <tr>
                <td class='an_tdcenter' colspan='2'><input name="Submit" type="submit" onclick="return validate();" value="Request number" class="button1"></td>
            </tr>
            </table>
        </form>
<?php
}
?>
    </div>
    </div>
    </body>
</html>
