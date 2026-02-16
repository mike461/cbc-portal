<?php
session_start();
$error = "";

// Handle Master Code for First-Time Setup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['master_code'])) {
    $entered_code = trim($_POST['master_code']); 
    if ($entered_code === 'cbc@Teacherportal801') {
        $_SESSION['teacher_authorized'] = true;
        session_write_close(); 
        header("Location: teacher_setup.php");
        exit();
    } else {
        $error = "Incorrect Master Access Code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBC Portal | School Gateway</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-5xl w-full">
        <header class="text-center mb-12">
            <h1 class="text-5xl font-extrabold text-slate-900 mb-3 tracking-tight">CBC PORTAL</h1>
            <p class="text-slate-500 font-medium">Competency Based Curriculum Digital Assessment System</p>
        </header>

        <div class="grid md:grid-cols-2 gap-10">
            
            <div class="bg-white p-10 rounded-3xl shadow-xl shadow-blue-100 border border-slate-100">
                <div class="inline-flex p-3 bg-blue-50 rounded-2xl mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <h2 class="text-3xl font-bold text-slate-800 mb-4">Learners</h2>
                <p class="text-slate-500 mb-10 leading-relaxed">Access your portfolio to upload project photos and your personal reflections for teacher review.</p>
                
                <div class="space-y-4">
                    <a href="learner_login.php" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-blue-200">Student Login</a>
                    <a href="learner_register.php" class="block w-full text-center border-2 border-slate-200 text-slate-600 hover:border-blue-600 hover:text-blue-600 font-bold py-4 rounded-2xl transition">New Registration</a>
                </div>
            </div>

            <div class="bg-slate-800 p-10 rounded-3xl shadow-xl shadow-slate-300 text-white">
                <div class="inline-flex p-3 bg-slate-700 rounded-2xl mb-6">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h2 class="text-3xl font-bold mb-4 text-white">Teachers</h2>
                <p class="text-slate-400 mb-10 leading-relaxed">Review submissions, award CBC performance levels, and generate professional PDF reports.</p>
                
                <div class="space-y-6">
                    <a href="teacher_login.php" class="block w-full text-center bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-emerald-900/20">Teacher Account Login</a>
                    
                    <div class="relative py-4 flex items-center">
                        <div class="flex-grow border-t border-slate-700"></div>
                        <span class="flex-shrink mx-4 text-slate-500 text-xs uppercase tracking-widest">Admin Only</span>
                        <div class="flex-grow border-t border-slate-700"></div>
                    </div>

                    <form method="POST" action="index.php" class="space-y-3">
                        <div class="flex gap-2">
                            <input type="password" name="master_code" placeholder="enter password" required
                                class="flex-1 bg-slate-700 border-none rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <button type="submit" class="bg-slate-600 hover:bg-slate-500 px-4 py-3 rounded-xl transition font-bold text-sm">Verify</button>
                        </div>
                        <?php if($error): ?>
                            <p class="text-red-400 text-xs mt-1 italic"><?= $error ?></p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

        </div>

        <footer class="text-center mt-12 text-slate-400 text-sm">
             CBC Digital Portal &bull; Secure Assessment Management
        </footer>
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

