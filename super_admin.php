<?php
session_start();
require 'db_connection.php';

// --- NEW PROMOTION LOGIC (Integrated) ---
if (isset($_POST['promote_grade'])) {
    $current_grade = $_POST['old_grade']; // e.g., 'Grade 3'
    $next_grade = $_POST['new_grade'];    // e.g., 'Grade 4'
    
    $stmt = $pdo->prepare("UPDATE students SET grade = ? WHERE grade = ?");
    $stmt->execute([$next_grade, $current_grade]);
    
    header("Location: super_admin.php?promotion=success");
    exit();
}

// 1. GET TOTAL STATS (Maintain your existing logic)
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$total_schools = 1; 

// 2. CALCULATE STORAGE USAGE (Maintain your existing logic)
$upload_dir = 'uploads/';
$total_size_bytes = 0;
if (is_dir($upload_dir)) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir)) as $file) {
        $total_size_bytes += $file->getSize();
    }
}
$total_size_mb = round($total_size_bytes / 1024 / 1024, 2);
$storage_limit_mb = 1000; 
$storage_percentage = ($total_size_mb / $storage_limit_mb) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin | System Overview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 font-sans">

    <div class="max-w-6xl mx-auto p-10">
        <header class="mb-12">
            <h1 class="text-3xl font-black tracking-tight uppercase">System Command Center</h1>
            <p class="text-slate-400">Monitoring all school portals and server health.</p>
        </header>

        <?php if(isset($_GET['promotion'])): ?>
            <div class="bg-emerald-500/20 border border-emerald-500 text-emerald-400 p-4 rounded-2xl mb-8 font-bold">
                ✓ Students successfully promoted to the next grade!
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-slate-800 p-8 rounded-3xl border border-slate-700">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Total Learners</p>
                <h2 class="text-5xl font-black text-emerald-400"><?= $total_students ?></h2>
            </div>
            <div class="bg-slate-800 p-8 rounded-3xl border border-slate-700">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Total Evidence</p>
                <h2 class="text-5xl font-black text-blue-400"><?= $total_projects ?></h2>
            </div>
            <div class="bg-slate-800 p-8 rounded-3xl border border-slate-700">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Server Storage</p>
                <h2 class="text-5xl font-black <?= $storage_percentage > 80 ? 'text-red-400' : 'text-amber-400' ?>">
                    <?= $total_size_mb ?> <span class="text-xl">MB</span>
                </h2>
                <div class="w-full bg-slate-700 h-2 rounded-full mt-6 overflow-hidden">
                    <div class="bg-emerald-500 h-full" style="width: <?= $storage_percentage ?>%"></div>
                </div>
            </div>
        </div>

        <div class="bg-slate-800 rounded-[2.5rem] p-10 border border-slate-700 mb-8">
            <h3 class="text-xl font-bold mb-2">End of Year Promotion</h3>
            <p class="text-slate-400 mb-6 text-sm">Move an entire class to the next grade level with one click.</p>
            
            <form action="super_admin.php" method="POST" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">From Current Grade</label>
                    <input type="text" name="old_grade" placeholder="e.g. Grade 3" required 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-emerald-500 outline-none">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">To New Grade</label>
                    <input type="text" name="new_grade" placeholder="e.g. Grade 4" required 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-emerald-500 outline-none">
                </div>
                <button type="submit" name="promote_grade" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-8 rounded-xl transition shadow-lg active:scale-95">
                    Promote Class
                </button>
            </form>
        </div>

        <div class="bg-white/5 rounded-[2.5rem] p-10 border border-white/10">
            <h3 class="text-xl font-bold mb-6">Quick Links</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="backup_db.php" class="bg-slate-100 text-slate-900 p-4 rounded-2xl font-bold text-center hover:bg-emerald-400 transition">Backup Database</a>
                <a href="#" class="bg-slate-800 text-white p-4 rounded-2xl font-bold text-center border border-slate-700 hover:bg-slate-700 transition">Reset Teacher PWD</a>
                <a href="#" class="bg-slate-800 text-white p-4 rounded-2xl font-bold text-center border border-slate-700 hover:bg-slate-700 transition">System Logs</a>
                <a href="logout.php" class="bg-red-500/10 text-red-400 p-4 rounded-2xl font-bold text-center border border-red-500/20 hover:bg-red-500 hover:text-white transition">Sign Out</a>
            </div>
        </div>
    </div>

</body>
</html>