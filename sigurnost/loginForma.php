<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
</head>

<body onload="window.document.loginForma.korisnik.focus()">
<div class="sve">
	<h1>Logiraj se</h1>
	<form name="loginForma" method="post" action="./login.php">
	<h3>Korisničko ime:</h3>
	<input type="text" name="korisnik"><br />
	<h3>Lozinka:</h3>
	<input type="password" name="zaporka"><br />
	<input type="submit" name="sbmt_login" value="Login">
	</form>
</div>
</body>
</html>