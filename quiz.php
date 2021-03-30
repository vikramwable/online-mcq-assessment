<?php
session_start();
error_reporting(1);
include("database.php");
extract($_POST);
extract($_GET);
extract($_SESSION);
if(isset($subid) && isset($testid))
{
$_SESSION[sid]=$subid;
$_SESSION[tid]=$testid;
header("location:quiz.php");
}
if(!isset($_SESSION[sid]) || !isset($_SESSION[tid]))
{
	header("location: index.php");
}

$rs1=mysqli_query($con,"select * from mst_result where test_id=$tid and login='$login'",$cn) or die(mysqli_error());
$row1= mysqli_fetch_row($rs1);
if ($row1) {
	header("location: result.php");
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Online Quiz</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="quiz.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php
include("header.php");
?>
<script language="JavaScript">
	 var countdown = 1 * 60 * 1000;
	 var timerId = setInterval(quizTimer, 1000);
	 function quizTimer(){
		 countdown -= 1000;
		 var min = Math.floor(countdown / (60 * 1000));
		 var sec = Math.floor((countdown - (min * 60 * 1000)) / 1000);
		 if (countdown <= 0) {
			 var element =document.getElementById("get-next");
			 if (element) {
				} else {
					var element =document.getElementById("get-result");
				}
				element.click();
		 } else {
		 document.getElementById("demo").innerHTML = (min + " : " + sec);
		 }
	 }
</script>
<p id="demo" style="padding: 5px 10px;font-weight: bold;color: red;text-align: center;margin: 0 auto;width: 10%;">01.00</p>

<?php
$query="select * from mst_question";
$rs=mysqli_query($con,"select * from mst_question where test_id=$tid",$cn) or die(mysqli_error());
$result1 = mysqli_num_rows($rs);

if($submit=='Next Question')
{
		mysqli_data_seek($rs,$_SESSION[qn]);
		$row= mysqli_fetch_row($rs);
		mysqli_query($con,"insert into mst_useranswer(sess_id, test_id, que_des, ans1,ans2,ans3,ans4,true_ans,your_ans) values ('".session_id()."', $tid,'$row[2]','$row[3]','$row[4]','$row[5]', '$row[6]','$row[7]','$ans')") or die(mysqli_error());
		if($ans==$row[7])
		{
					$_SESSION[trueans]=$_SESSION[trueans]+1;
		}
		$_SESSION[qn]=$_SESSION[qn]+1;
}
else if($submit=='Get Result')
{
		?>
		<script>
			clearInterval(timerId);
			document.getElementById("demo").innerHTML = "";
		</script>
		<?php
		mysqli_data_seek($rs,$_SESSION[qn]);
		$row= mysqli_fetch_row($rs);
		mysqli_query($con,"insert into mst_useranswer(sess_id, test_id, que_des, ans1,ans2,ans3,ans4,true_ans,your_ans) values ('".session_id()."', $tid,'$row[2]','$row[3]','$row[4]','$row[5]', '$row[6]','$row[7]','$ans')") or die(mysqli_error());
		if($ans==$row[7])
		{
					$_SESSION[trueans]=$_SESSION[trueans]+1;
		}
		echo "<h1 class=head1> Result</h1>";
		if ($_SESSION[qn] < $result1) {
			$_SESSION[qn]=$_SESSION[qn]+1;
		}
		$anstrueCount = $_SESSION[trueans] ? $_SESSION[trueans] : 0;

		echo "<Table align=center><tr class=tot><td>Total Questions<td> $_SESSION[qn]";
		echo "<tr class=tans><td>Correct Answers<td>".$anstrueCount;
		$w=$_SESSION[qn]-$_SESSION[trueans];
		$ansFalseCount = $w ? $w : 0;
		echo "<tr class=fans><td>Wrong Answers<td> ". $ansFalseCount;
		echo "</table>";
		$date = date("Y/m/d");
		mysqli_query($con,"insert into mst_result(login,test_id,test_date,score) values('$login',$tid,CAST('". $date ."' AS DATE),$anstrueCount)") or die(mysqli_error());
		echo "<h1 align=center><a href=review.php> Review Question</a> </h1>";
		unset($_SESSION[qn]);
		unset($_SESSION[sid]);
		unset($_SESSION[tid]);
		unset($_SESSION[trueans]);
		exit;
}
$rs=mysqli_query($con,"select * from mst_question where test_id=$tid",$cn) or die(mysqli_error());
if($_SESSION[qn]>mysqli_num_rows($rs)-1)
{
unset($_SESSION[qn]);
echo "<h1 class=head1>Some Error  Occured</h1>";
session_destroy();
echo "Please <a href=index.php> Start Again</a>";

exit;
}
mysqli_data_seek($rs,$_SESSION[qn]);
$row= mysqli_fetch_row($rs);
echo "<form name=myfm method=post action=quiz.php>";
echo "<table width=100%> <tr> <td width=30>&nbsp;<td> <table border=0>";
$n=$_SESSION[qn]+1;
echo "<tR><td><pre><span class=style2>Question ".  $n .": $row[2]</style>";
echo "<tr><td class=style8><label><input type=radio name=ans value=1>$row[3]</label>";
echo "<tr><td class=style8><label><input type=radio name=ans value=2>$row[4]</label>";
echo "<tr><td class=style8><label><input type=radio name=ans value=3>$row[5]</label>";
echo "<tr><td class=style8><label><input type=radio name=ans value=4>$row[6]</label>";

if($_SESSION[qn]<mysqli_num_rows($rs)-1)
echo "<tr><td><input id='get-next' type=submit name=submit value='Next Question' style='background-color: #b1b2b68c;padding: 10px 30px;border-radius: 15px;margin-top: 20px;border: 1px solid;outline: none;'></form>";
else
echo "<tr><td><input id='get-result' type=submit name=submit value='Get Result' style='background-color: #b1b2b68c;padding: 10px 30px;border-radius: 15px;margin-top: 20px;border: 1px solid;outline: none;'></form>";
echo "</table></table>";
?>
</body>
</html>
