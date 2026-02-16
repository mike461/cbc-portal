<?php
session_start();
require 'db_connection.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    // Fetch teacher from database
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    $teacher = $stmt->fetch();

    // Verify password
    if ($teacher && password_verify($password, $teacher['password'])) {
        // Grant full teacher access
        $_SESSION['teacher_authorized'] = true;
        $_SESSION['teacher_id'] = $teacher['id'];
        $_SESSION['teacher_name'] = $teacher['username'];
        
        header("Location: teacher_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Login | CBC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-3xl shadow-2xl">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-slate-800">Teacher Login</h2>
            <p class="text-slate-500 mt-2">Enter your credentials to manage assessments</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-center text-sm font-bold">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="teacher_login.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
                <input type="text" name="username" required 
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>

            <div class="relative">
    <input type="password" id="passwordInput" name="password" required 
        class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl p-3 pr-12 outline-none focus:border-emerald-500 focus:bg-white transition font-medium text-slate-700" 
        placeholder="Enter password">
    
    <button type="button" onclick="togglePasswordVisibility()" 
        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-emerald-600 transition">
        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    </button>
</div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-lg transition transform hover:-translate-y-1">
                Access Dashboard
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
            Forgot credentials? Contact System Administrator.
        </div>
    </div>
    <script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        // Swaps to the "Eye with Slash" icon
        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
    } else {
        passwordInput.type = 'password';
        // Swaps back to the "Open Eye" icon
        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
    }
}
</script>
</body>
</html>