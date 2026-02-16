<?php
// Database Configuration
$host = 'sql311.infinityfree.com';
$db   = 'if0_40895236_cbc_portal';
$user = 'if0_40895236';
$pass = 'ClJf9yaFtK';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = "";

// Processing form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $first_name = htmlspecialchars($_POST['first_name']);
    $second_name = htmlspecialchars($_POST['second_name']);
    $grade = htmlspecialchars($_POST['grade']);
    $stream = htmlspecialchars($_POST['stream']);
    $password = $_POST['password'];

    // Password Hashing for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL with Prepared Statements to prevent SQL Injection 
    $sql = "INSERT INTO students (first_name, second_name, grade, stream, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$first_name, $second_name, $grade, $stream, $hashed_password]);
        $message = "<div class='bg-green-100 text-green-700 p-3 rounded'>Account created successfully! <a href='learner_login.php' class='underline'>Login here</a></div>";
    } catch (Exception $e) {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded'>Error: Could not create account.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Learner Registration | CBC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-lg border border-slate-200">
        <h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Create Learner Account</h2>
        
        <?php echo $message; ?>

        <form action="learner_register.php" method="POST" class="space-y-4 mt-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">First Name</label>
                    <input type="text" name="first_name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Second Name</label>
                    <input type="text" name="second_name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Grade</label>
                <select name="grade" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Grade</option>
                    <option value="Grade 1">Grade 1</option>
                    <option value="Grade 2">Grade 2</option>
                    <option value="Grade 3">Grade 3</option>
                    <option value="Grade 4">Grade 4</option>
                    <option value="Grade 5">Grade 5</option>
                    <option value="Grade 6">Grade 6</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Stream</label>
                <input type="text" name="stream" placeholder="e.g. Blue, North, West" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
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

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                Register Student
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-500">
            Already have an account? <a href="learner_login.php" class="text-blue-600 font-semibold">Log In</a>
        </p>
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