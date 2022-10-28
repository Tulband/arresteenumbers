<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['Submit']))
{
    // code for check server side validation
    if(empty($_SESSION['captcha_code'] ) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) != 0)
    {
        echo "not validated";

    }
    else // Captcha verification is Correct. Final Code Execute here!		
    {
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

        $new_number = intval($last_number) + 1;
        $new_entry = $new_number . ";" . $nickname . ";" . date("Y-m-d H:i:s") . "\n";

        /* file_put_contents("numbers.txt", $new_entry, FILE_APPEND | LOCK_EX) || die("Unable to write to file"); */
        fwrite($handle,$new_entry);
        fflush($handle);
        fclose($handle);

        echo("Jo " . $nickname . ", you will be number " . $new_number . ", remember this!");
    }
}
?>


<html>
<head>
<meta charset="utf-8">
<title>Your arrestee number generator</title>
<link href="./css/style.css" rel="stylesheet">
<script type='text/javascript'>
function refreshCaptcha(){
	var img = document.images['captchaimg'];
	img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
}
</script>
</head>
<body>
<div id="frame0">
    <h1>Your arrestee number generator</h1>
    <p>Only take one number! </p>
</div>
<br>

<form action="" method="post" name="form1" id="form1" >
    <table>
        <tr>
            <td>Action name (not your real name!):</td>
        </tr>
        <tr>
            <td><input type="text" name="name"></td>
        </tr>
        <tr>
            <td>
                Captcha: <img src="captcha.php?rand=<?php echo rand();?>" id='captchaimg'><br>
                <i>Can't read the image? click <a href='javascript:
refreshCaptcha();'>here</a> to refresh.</td></i>
            </td>
        <tr>
            <td>
                <input id="captcha_code" name="captcha_code" type="text">
            </td>
        </tr>
        </tr>
    <br>
    <td><input name="Submit" type="submit" onclick="return validate();" value="Submit" class="button1"></td>
    </table>
</form>
</body>
</html>
