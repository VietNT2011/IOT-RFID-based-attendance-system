<?php 

if (isset($_POST['login'])) {

	require 'connectDB.php';

	$Usermail = $_POST['email']; 
	$Userpass = $_POST['pwd']; 

	if (empty($Usermail) || empty($Userpass) ) {
		header("location: login.php?error=emptyfields");
  		exit();
	}
	else if (!filter_var($Usermail,FILTER_VALIDATE_EMAIL)) {
          header("location: login.php?error=invalidEmail");
          exit();
    }
	else{
		$sql = "SELECT * FROM admin WHERE admin_email=?";
		$result = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($result, $sql)) {
			header("location: login.php?error=sqlerror");
  			exit();
		}
		else{
			mysqli_stmt_bind_param($result, "s", $Usermail);
			mysqli_stmt_execute($result);
			$resultl = mysqli_stmt_get_result($result);

			if ($row = mysqli_fetch_assoc($resultl)) {
				$pwdCheck = password_verify($Userpass, $row['admin_pwd']);
				
				if ($pwdCheck == false) {
					header("location: login.php?error=wrongpassword");
  					exit();
				}
				else if ($pwdCheck == true) {

					
                    session_start();
					$_SESSION['Admin-name'] = $row['admin_name'];
					$_SESSION['Admin-email'] = $row['admin_email'];
					header("location: index.php?login=success");
					exit();

	                
				}
			}
			else {
				$sql = "SELECT * FROM users WHERE email=? AND card_uid=?";
				$result = mysqli_stmt_init($conn);
				if (!mysqli_stmt_prepare($result, $sql)) {
					header("location: login.php?error=sqlerror");
					exit();
				} else {
					mysqli_stmt_bind_param($result, "ss", $Usermail, $Userpass);
					mysqli_stmt_execute($result);
					$resultl = mysqli_stmt_get_result($result);
			
					if ($row = mysqli_fetch_assoc($resultl)) {
						// Xác thực thành công, bạn có thể thực hiện các hành động tiếp theo ở đây
						session_start();
						$_SESSION['User-card-uid'] = $row['card_uid']; // Lưu card_uid vào session
						header("location: checkInfoUser.php");
						exit();
					} else {
						// Nếu không tìm thấy người dùng hoặc thông tin không khớp, chuyển hướng về trang đăng nhập với thông báo lỗi
						header("location: login.php?error=nouser");
						exit();
					}
				}
			}
			
		}
	}

	
mysqli_stmt_close($result);    
mysqli_close($conn);
}
else{
  header("location: login.php");
  exit();
}
?>