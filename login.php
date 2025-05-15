<?php
session_start();
 
require 'connect.php';
 
if (isset($_POST['username']) && isset($_POST['password'])) {
    $input_username = trim($_POST['username']);  
    $input_password = $_POST['password'];      
 
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_name = ?");  
    $stmt->execute([$input_username]); 
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if ($user && password_verify($input_password, $user['user_password'])) { 
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
 
        header("Location: upload.php");
        exit();
    } else { 
        $error_message = "Hatalı kullanıcı adı veya şifre!";
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;400;700&display=swap" rel="stylesheet">
    <style>
    body{
      font-family: 'Inter', sans-serif; 
      overflow-x: hidden;
      color: black;
    } 
          body.dark-mode{
    background-color: black;
    color: rgb(255, 255, 255);
  }
  body.dark-mode header{
    background-color: black;
    color: rgb(255, 255, 255);
    border-bottom: 2px solid white;
  }
  
  body.dark-mode .nav-container{
    background-color: black;
    color: rgb(255, 255, 255);
  }
  body.dark-mode .nav-container a{
    background-color: black;
    color: rgb(255, 255, 255);
  } 
  body.dark-mode .nav-container .logo{ 
    color: rgb(255, 255, 255);
  }
  body.dark-mode .nav-container .fa-solid{ 
    color: rgb(255, 255, 255);
  } 
  body.dark-mode footer{ 
    background-color: black;
    color: white;
    border: 1px solid white;
  } 
  body.dark-mode footer i { 
    color: white;
  } 
  body.dark-mode .card { 
    background-color: gray;
    color: white;
  } 
  body.dark-mode footer span{ 
    color: white;
  }  
  body.dark-mode .slogan h1 { 
    color: white;
  } 
  body.dark-mode .slogan #word{ 
    color: white;
  }  
  body.dark-mode   #dark-mode-toggle{
    width: 2rem;
    height: 2rem;
    border: 1px solid white;
    border-radius: 100%;
    font-size: 1.3rem;
    background-color: transparent;
    cursor: pointer;
  }
  body.dark-mode .premium-price .price-card{
    background: linear-gradient(154deg, gray, black);    
    color: white;
    border: 1px solid white;
  }
  body.dark-mode .premium-price .price-card ul li{ 
    color: white; 

  }
  body.dark-mode i{ 
    color: white; 

  }
  
  body.dark-mode .premium-price .price-card:nth-child(2) h4{ 
    color: white; 
  }
  body.dark-mode .premium-price .price-card:nth-child(2) button{ 
    color: white; 
    background-color:rgb(0, 0, 0);

  }
  
  #dark-mode-toggle{
    width: 2rem;
    height: 2rem;
    border: 1px solid;
    border-radius: 100%;
    font-size: 1.3rem;
    background-color: transparent;
    cursor: pointer;
  }
    .hesaplar{
        margin: 0 auto;
        display: flex;
        justify-content: center;
    }
    .hesaplar a{  
      text-decoration: none;
      font-size: 24px;
    }
    .hesaplar i{
      color: black;
      margin: 10px;
    }
    .nav-container{ 
      display: flex;
      justify-content: space-around;
      align-items: center;
      background-color: rgb(255, 255, 255);
      color: white;
      width: 100%; 
      font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
      top: 0;
      left: 0;    
      position: fixed;
      z-index: 1;
    }
    .nav-container ul{
      margin-left: 956px;
      list-style-type: none; 
      display:flex;
      gap: 15px; 
   }
    .nav-container ul a{ 
      text-decoration: none;
      color: rgb(0, 0, 0); 
      padding: 10px; 
      font-size: 18px;
      transition: all 0.7s;
      background-color: rgb(255, 255, 255);
 
   } 
   .container{ 
    padding: 50px;
   }
   .container h3{ 
   }
   
   main{
    display: flex;
    margin: auto 0;
    justify-content: center;
    align-items: center;
    height: 100vh;
   }
   .container{ 
    width: 35%;
   }
  
   form{      
      display: flex;
      flex-direction: column;
      row-gap: 15px;  
   } 
   form #kadi, #pword, #email{
    width: 80%;
    padding: 10px;
    outline: none;
    border: 1px solid rgb(0, 0, 0);
    border-radius: 5px; 
    border: none;
    border-left: 2px solid rgb(211, 211, 211);
    border-bottom: 2px solid rgb(211, 211, 211);
   }
    form #giris-btn{
      background: linear-gradient(450deg, rgb(255, 195, 195),rgb(172, 172, 206),rgb(198, 255, 198), rgb(202, 196, 189));
      width: 100%;
      padding: 10px;
      outline: none;
      border: 1px solid rgb(211, 211, 211);
      border-radius: 5px;
      font-weight: bolder;
      font-size: 18px;
      transition: border 1s ease;
      cursor: pointer;
    }
    form #giris-btn:hover{
      background: linear-gradient(450deg, rgb(250, 208, 208),rgb(193, 193, 250),rgb(148, 255, 148));
      opacity: 0.9;
      border: 1px solid rgb(0, 0, 0);
    }
   form #kadi:focus,#email:focus,#pword:focus{
    box-shadow: 0 0 3px rgb(0, 0, 0.1);
   }
   form input::placeholder{
    font-size: 15px;
    font-family: 'Inter',sans-serif;
   }
   .kadi-icon{
    display: flex;
    position: relative;
   }
   .kadi-icon i{
    top: 10px;
    right: 8px;
   position: absolute;
   }
   .email-icon{
    display: flex;
    position: relative;
   }
   .email-icon i{ 
    top: 10px;
    right: 8px;
    position: absolute;
   }
   .pword-icon{
    display: flex;
    position: relative;
   }
   .pword-icon i{
    position: absolute;
    right: 5px;
    top: 10px;
   } 
    .login-link{
    text-align: center;
   }

   .login-link a{
    color: rgb(7, 7, 255);
    text-decoration: none;
      }
   .login-link a:hover{ 
    color: rgb(7, 7, 255);
    text-decoration: underline;
      }
    
      footer {
    background-color: black;
    color: white;
    width: 100%;  
    padding: 10px;
    position: absolute;  
    bottom: auto;  
    left: 0;
    right: 0;  
    text-align: center; 
    }

    footer a{
    text-decoration: none;
    color: white;

    }
    .footer-nav{
    display: flex;
    justify-content: space-around;

    }
    .footer-nav ul{  list-style-type: none;
    }   
     </style>
</head>

<body>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<header>
<nav class="nav-container">
        
        <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px; margin-left: 3px;"></a>
        <ul> 
            <li><a href="register.php">            
            <i class="fas fa-user-plus icon"></i>
            Kayıt Ol</a></li>
            <li><a href="contact.php">         
            <i class="fa-solid fa-envelope"></i>
            İletişim</a></li>
        </ul>
                         <button id="dark-mode-toggle"> 
         <i class="fa-solid fa-moon"></i>
        </nav> 
</header>

<main>
    <div class="container">
        <h3 align="center" style="font-size: 32px;">Giriş Yap</h3>
        <form action="login.php" method="post">
            <div class="kadi-icon">
                <input type="text" name="username" id="kadi" placeholder="Kullanıcı adınız..." style="width: 100%;" required>
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="pword-icon">
                <input type="password" name="password" id="pword" placeholder="Parolanız..." style="width: 100%;" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required>
                <i class="fa-solid fa-eye-slash" id="togglePassword"></i>
            </div>
            <input type="submit" value="Giriş Yap" id="giris-btn">
            <a href="forgot_password.php" style="color: red; text-decoration: none;">Parolamı Unuttum</a>
            <?php 
            if (isset($error_message)) {
                echo "<p style='color: red; text-align: center;'>$error_message</p>";
            }
            ?>

            <p align="center">Hesabım yok. <a href="register.php">Kayıt ol</a> ya da</p>
        </form>

        <p align="center">Farklı bir hesapla giriş yap</p>
        <div class="hesaplar">
            <a href="#"><i class="fa-brands fa-google"></i></a>
            <a href="#"><i class="fa-brands fa-linkedin"></i></a>
            <a href="#"><i class="fa-brands fa-github"></i></a>
            <a href="#"><i class="fa-brands fa-facebook"></i></a>
        </div>
    </div>
</main>

<footer>  
    <div class="footer-nav"> 
        <ul>
        <a href="#"><h3>HIZLI BAĞLANTILAR</h3></a>
        <li><a href="index.php">Anasayfa</a></li> 
        <li><a href="register.php">Kayıt ol</a></li>  
        <li><a href="contact.php">İletişim</a></li>
        </ul>
        <ul>
        <a href="#"><h3>YASAL BİLGİLER</h3></a>
        <li><a href="">Kullanım Koşulları </a></li>
        <li><a href="">Gizlilik Politikası </a></li>
        <li><a href="">Çerez Politikası</a></li> 
        </ul>
        <ul>
        <a href="#"><h3>SOSYAL MEDYA</h3></a>
        <li><a href="">Facebook </a></li>
        <li><a href="">Twitter</a></li>
        <li><a href="">Instagram</a></li>
        <li><a href="">LinkedIn</a></li>
        </ul>
        <ul>
        <a href="#"><h3>İLETİŞİM BİLGİLERİ </h3></a>
        <li><a href=""><b>Telefon: </b> +90 123 456 789
        </a></li>
        <li><a href=""><b>Email: </b>support@dosyapaylasim.com
        </a></li> 
        </ul>
    </div> 
        <p align="center">Tüm haklar saklıdır. TE-FS &copy2025</p>
    </footer>
<script>
    const togglePassword = document.getElementById("togglePassword");
    const passwordField = document.getElementById("pword");

    togglePassword.addEventListener("click", function () {
        const type = passwordField.type === "password" ? "text" : "password";
        passwordField.type = type;
        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });
    
    document.getElementById('dark-mode-toggle').addEventListener('click',()=>{
  document.body.classList.toggle('dark-mode');
});

</script>

</body>
</html>
