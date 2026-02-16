<?php
session_start();
require 'db_connection.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = htmlspecialchars($_POST['first_name']);
    $password = $_POST['password'];

    // Securely fetch the student by their first name
    $stmt = $pdo->prepare("SELECT * FROM students WHERE first_name = ?");
    $stmt->execute([$first_name]);
    $student = $stmt->fetch();

    // Verify if student exists and password matches the hash
    if ($student && password_verify($password, $student['password'])) {
        // Set Session variables to track the logged-in student
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['first_name'] . " " . $student['second_name'];
        $_SESSION['grade'] = $student['grade'];
        
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = "Invalid name or password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Learner Login | CBC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl border border-slate-200">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-slate-800">Student Login</h2>
            <p class="text-slate-500 mt-2">Access your CBC Digital Evidence Portal</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="learner_login.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">First Name</label>
                <input type="text" name="first_name" required 
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
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

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                Login to Dashboard
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <p class="text-slate-500 text-sm">Don't have an account?</p>
            <a href="learner_register.php" class="text-blue-600 font-bold hover:underline">Register as a New Learner</a>
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