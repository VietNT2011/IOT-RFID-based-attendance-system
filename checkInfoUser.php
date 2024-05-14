
<!DOCTYPE html>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/checkInfoUser.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Check Logs User</title>
</head>
<body>

    <div class="wrapInput">
        <h1>Check Logs User</h1>
    </div>
    
    <?php

    session_start();
    require 'connectDB.php';

    if(isset($_SESSION['User-card-uid'])) {
        // Lấy giá trị của card_uid từ session
        $card_uid = $_SESSION['User-card-uid'];
         // Chuẩn bị câu truy vấn
         $sql1 = "SELECT * FROM users_logs WHERE card_uid = $card_uid";
         $sql2 = "SELECT * FROM users WHERE card_uid = $card_uid";
 
         // Thực thi truy vấn
         $result1 = $conn->query($sql1);
         $result2 = $conn->query($sql2);
         // Kiểm tra và hiển thị kết quả
 
         $row2 = $result2->fetch_assoc();
 
         if ($row2) {
             // Nếu có dữ liệu, gán vào biến
             $gender = $row2["gender"];
             $email = $row2["email"];
             $serial = $row2['serialnumber'];
             $username = $row2['username'];
             $department = $row2['device_dep'];
         } else {
             // Nếu không có dữ liệu, gán giá trị mặc định hoặc làm gì đó khác
             $gender = ""; // hoặc gán giá trị mặc định khác
             $email = ""; // hoặc gán giá trị mặc định khác
             $serial ="";
             $username = "";
         }

         echo "<div class='row'>";
         echo "         <div class='col-3'>
                    <div class='cardInfo'>
                    <div class='d-flex'>
                        <img style='width:25%; margin: auto;' src='./icons/user.jpg'/>
                    </div>
            
                    <div class='infoUser'>
                        
                        <h3>Username</h3>
                        <div class='Input'>
                        <input disabled class='inpUserName' type='text' value='$username'/>
                        </div>
            
                        <div class='d-inline'>
                            <h3>Phone Number</h3>
                            <h3 style='margin-left:24px'>Gender</h3>
                        </div>
            
                        <div class='d-flex'>
                            <div class='Input'><input disabled  type='text' value='$serial'/></div>
                            <div  class='Input'><input disabled type='text' value='$gender'/></div>
                        </div>
            
                        <h3>Email</h3>
                        <div class='Input'><input disabled type='text' value='$email'/></div>

                        <h3>Department</h3>
                        <div class='Input'><input disabled type='text' value='$department'/></div>
                    </div>
                    <div class='wrapBtnLog'><a class='btn btnLogOut' href='logout.php'>Log out</a></div>
                    </div>
                </div> ";
         
     
         if ($result1->num_rows > 0) {  
             // Bắt đầu bảng HTML
            
             echo "
             <div class='col-9'>
             <table class='container' border='1'>
                     <tr>
                         <th>Date checkin</th>
                         <th>Time in</th>
                         <th>Time out</th>
                         
                     </tr>";
             
             // Lưu tất cả các dòng kết quả từ result1 vào một mảng
            $rows1 = [];
            while ($row = $result1->fetch_assoc()) {
                $rows1[] = $row;
            }

            // Đảo ngược mảng các dòng kết quả
            $rows1 = array_reverse($rows1);

            // Duyệt qua từng hàng dữ liệu đã đảo ngược
            foreach ($rows1 as $row1) {
                // Hiển thị mỗi hàng dữ liệu trong một dòng của bảng HTML
                echo "<tr>";
                echo "<td>" . $row1["checkindate"] . "</td>";
                echo "<td>" . $row1["timein"] . "</td>";
                echo "<td>" . $row1["timeout"] . "</td>";
                echo "</tr>";
            }
         
             // Kết thúc bảng HTML
             echo "</table> </div>";


             echo "</div>"; 
         } 

         else{
            echo "
            <div class='col-9'>
             <table class='container notes'>
             <th>
                <div class='notes'>
                <span class='notes5'> 
                <i class='fa-solid fa-circle-exclamation'></i>
                Không có dữ liệu của User
                </span> 
             </div>
             </th>
             </table> </div>
             ";
         }
       
    } 

       

    // Đóng kết nối
    $conn->close();
    ?>

</body>
</html>
