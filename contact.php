 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <style>
  body{
    font-family: 'Inter', sans-serif;  
    overflow-x: hidden;
    color: black;
  } 

  .hesaplar{
    margin: 0 auto;
    display: flex;
    justify-content: center;
  }
  .hesaplar a{  
    text-decoration: none;
    font-size: 24px;
    font-weight: bold;  
  }
  .hesaplar i{
    color: black;
    margin: 10px;
  }
  .nav-container{ 
    display: flex;
    justify-content: space-between; 
    align-items: center;
    background-color: rgb(255, 255, 255);
    color: white;
    width: 100%; 
    font-family: 'Inter', sans-serif;
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
    font-weight: bold; /* Kalın yazı */
    transition: all 0.7s;
    background-color: rgb(255, 255, 255);
  } 

  .container{ 
    padding: 50px;
    margin-top: 50px;
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
      border-radius: 5px; 
      border: none;
    border-top: 2px solid rgb(211, 211, 211);
    border-left: 2px solid rgb(211, 211, 211);

  }
  form #gonder-btn{
    background: linear-gradient(450deg, rgb(248, 170, 170),rgb(184, 184, 243),rgb(180, 214, 180));
    cursor: pointer;
    width: 100.5%;
    padding: 10px;
    outline: none;
    border: 1px solid rgb(211, 211, 211);
    border-radius: 5px;
    font-weight: bolder;
    font-size: 18px;
    transition: all ease 3s;
  }
  form #gonder-btn:hover{
    background: linear-gradient(450deg, rgb(250, 208, 208),rgb(193, 193, 250),rgb(148, 255, 148));
    opacity: 0.9;
    border: 1px solid;
  }
  form #kadi:focus,#email:focus,#pword:focus{
    box-shadow: 0 0 3px rgb(0, 0, 0.1);
  }
  form input::placeholder{
    font-size: 15px;
    font-family: 'Inter', sans-serif; 
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
    font-weight: bold; /* Kalın yazı */
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
  .footer-nav ul{  
    list-style-type: none;
  }
  .sign-up{
    cursor: pointer;
  }
  textarea{
    padding-left: 10px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    outline: none;
    border: none;
    border: 2px solid rgb(211, 211, 211);
    border-radius: 5px;
  }
  textarea::placeholder{  
    font-family: 'Inter', sans-serif;
    color: gray;
    font-size: 15px; 
  }
  textarea:focus{
    box-shadow: 0 0 3px rgb(0, 0, 0.1);
  }
</style>
</head>
<body>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <header>
    <nav class="nav-container">
    <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px; margin-top:0px; margin-left: 16px;"></a>
      <ul>
        <li><a href="register.php" style=" margin-right:20px;">                       
          <i class="fas fa-user-plus icon"></i>
          Üye Ol</a></li> 
      </ul>
    </nav>  
  </header>
  <main>    
    <div class="container">
      <h3 align="center" style="font-size: 32px;">Destek ve Tavsiye İçin Bizimle İletişime Geçin</h3>
      <form action="contact-mail.php" method="post"> 
        <div class="kadi-icon">
      <input type="text" name="kadi" id="kadi" placeholder="adınız..." style="width: 100%;"> <i class="fa-solid fa-user"></i></div>
        <div class="kadi-icon">
      <input type="text" name="email" id="email" placeholder="mail adresiniz..." style="width: 100%;"> <i class="fa-solid fa-envelope"></i></div>
        <div>
        <textarea name="message" id="message" cols="54" rows="9" placeholder="mesajınız..."></textarea>
        </div>
          <input type="submit" value="Gönder" id="gonder-btn">
          <p align="center"> * Tekrar eden mesajlar spama düşecektir *  </p>
        </form>
         
    </div>
  </main>
  <footer>  
    <div class="footer-nav"> 
        <ul>
        <a href="#"><h3>HIZLI BAĞLANTILAR</h3></a>
        <li><a href="index.php">Anasayfa</a></li> 
        <li><a href="register.php">Üye ol</a></li>  
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

</script>
</body>
</html>