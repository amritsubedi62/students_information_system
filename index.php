<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Information System</title>

<style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;

  /* Background Image with overlay */
  background: 
    linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.4)),
    url('stu.jpg');
    
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;

  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;

  animation: fadeIn 0.8s ease-in;
}

/* Fade Animation */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Glass Container */
.container {
  width: 100%;
  max-width: 500px;

  position: relative;

  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);

  padding: 50px 30px;
  border-radius: 20px;

  border: 1px solid rgba(255,255,255,0.25);

  box-shadow: 
    0 10px 40px rgba(0,0,0,0.4),
    inset 0 0 20px rgba(255,255,255,0.1);

  text-align: center;

  animation: slideUp 0.7s ease;
}

/* Soft glass border glow */
.container::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 20px;
  padding: 1px;
  background: linear-gradient(135deg, rgba(255,255,255,0.4), rgba(255,255,255,0.1));
  -webkit-mask: 
    linear-gradient(#fff 0 0) content-box, 
    linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
}

/* Slide Animation */
@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* Heading */
.container h1 {
  color: #ffffff;
  font-size: 30px;
  margin-bottom: 15px;
  text-shadow: 0 2px 10px rgba(0,0,0,0.6);
}

/* Paragraph */
.container p {
  color: rgba(255,255,255,0.85);
  font-size: 16px;
  margin-bottom: 30px;
}

/* Glass Buttons */
.container button {
  width: 45%;
  padding: 12px 0;
  margin: 5px;

  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(12px);

  color: white;

  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 10px;

  font-size: 16px;
  font-weight: bold;

  cursor: pointer;

  transition: all 0.3s ease;

  box-shadow: 0 5px 15px rgba(0,0,0,0.25);
}

/* Hover Effect */
.container button:hover {
  background: rgba(255,255,255,0.3);
  transform: translateY(-4px) scale(1.03);
  box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}

/* Click Effect */
.container button:active {
  transform: scale(0.96);
}

/* Mobile Responsive */
@media(max-width: 480px) {
  .container {
    padding: 40px 20px;
  }

  .container button {
    width: 100%;
    margin: 8px 0;
  }
}
</style>

</head>

<body>

<div class="container">
  <h1>Welcome to Student Information System</h1>
  <p>Manage student records, attendance, and performance efficiently.</p>
  
  <button onclick="window.location.href='login.php'">Login</button>
  <button onclick="window.location.href='signup.php'">Signup</button>
</div>

</body>
</html>