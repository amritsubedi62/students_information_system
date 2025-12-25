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
  background: linear-gradient(135deg, #f0f2f5, #e3e6eb);
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  animation: fadeIn 0.8s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}


.container {
  width: 100%;
  max-width: 500px;
  background: #fff;
  padding: 50px 30px;
  border-radius: 14px;
  box-shadow: 0 12px 30px rgba(0,0,0,0.15);
  text-align: center;
  animation: slideUp 0.6s ease;
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}


.container h1 {
  color: #d32f2f;
  font-size: 28px;
  margin-bottom: 15px;
}

.container p {
  color: #555;
  font-size: 16px;
  margin-bottom: 30px;
}


.container button {
  width: 45%;
  padding: 12px 0;
  margin: 0 5px;
  background-color: #d32f2f;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
}

.container button:hover {
  background-color: #9a0007;
  transform: translateY(-2px);
}
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
  <button onclick="window.location.href='signup.php'">Signup (Parents)</button>
</div>
</body>
</html>
