<?php
session_start();
require 'db_connection.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// --- IMAGE COMPRESSION FUNCTION ---
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } else {
        return false;
    }
    imagejpeg($image, $destination, $quality);
    return $destination;
}

// 2. FETCH STUDENT DATA
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// 3. UPDATED HANDLE NEW UPLOAD (Image Only - Integrated)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['project_file'])) {
    $reflection = $_POST['reflection'];
    $file = $_FILES['project_file'];
    
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    // RESTRICT TO IMAGES ONLY
    $allowed_images = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_ext, $allowed_images)) {
        header("Location: student_dashboard.php?error=invalid_file");
        exit();
    }

    $file_name = "STU_" . $student_id . "_" . time() . ".jpg"; 
    $target_file = $target_dir . $file_name;
    
    // Compress image to 60% quality
    $success = compressImage($file["tmp_name"], $target_file, 60);

    if ($success) {
        $ins = $pdo->prepare("INSERT INTO projects (student_id, media_url, media_type, reflection) VALUES (?, ?, 'image', ?)");
        $ins->execute([$student_id, $target_file, $reflection]);
        header("Location: student_dashboard.php?upload_success=true");
        exit();
    }
}

// 4. FETCH ALL PROJECTS FOR THE FEED
$p_stmt = $pdo->prepare("SELECT * FROM projects WHERE student_id = ? ORDER BY created_at DESC");
$p_stmt->execute([$student_id]);
$projects = $p_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Portfolio | CBC Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-900">

    <div class="flex min-h-screen">
        <div class="w-64 bg-white border-r border-slate-200 p-6 fixed h-full z-10">
            <div class="mb-10">
                <h2 class="text-xl font-black text-emerald-600 tracking-tighter uppercase">CBC Portal</h2>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Student View</p>
            </div>
            
            <nav class="space-y-1">
                <a href="student_dashboard.php" class="block py-3 px-4 bg-emerald-50 text-emerald-700 rounded-xl font-bold">My Portfolio</a>
                <a href="logout.php" class="block py-3 px-4 text-slate-400 hover:text-red-500 transition font-medium">Sign Out</a>
            </nav>

            <div class="absolute bottom-10 left-6 right-6 p-4 bg-slate-900 rounded-2xl text-white">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Welcome back,</p>
                <p class="text-sm font-bold truncate"><?= htmlspecialchars($student['first_name'] . ' ' . $student['second_name']) ?></p>
                <p class="text-[10px] text-emerald-400 font-bold"><?= $student['grade'] ?> - <?= $student['stream'] ?></p>
            </div>
        </div>

        <div class="flex-1 ml-64 p-10">
            <header class="mb-10 flex justify-between items-end">
                <div>
                    <h1 class="text-4xl font-black text-slate-800 tracking-tight">My Projects</h1>
                    <p class="text-slate-500 font-medium">Manage your work and view teacher evaluations.</p>
                </div>
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-2xl transition shadow-lg shadow-emerald-200 active:scale-95">
                    + Upload Project
                </button>
            </header>

            <?php if(isset($_GET['upload_success'])): ?>
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl mb-10">
                    <span class="font-black uppercase tracking-tight">Submission Received Successfully!</span>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error']) && $_GET['error'] == 'invalid_file'): ?>
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl mb-10">
                    <span class="font-black uppercase tracking-tight">Error: Only image files (JPG, PNG) are allowed.</span>
                </div>
            <?php endif; ?>

            <div class="grid gap-12">
                <?php foreach ($projects as $project): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    
                    <div class="w-full bg-slate-900 flex items-center justify-center relative group" style="min-height: 550px; max-height: 800px;">
                        <img src="<?= $project['media_url'] ?>" 
                             class="max-w-full max-h-full object-contain cursor-zoom-in transition-transform duration-500 group-hover:scale-105"
                             onclick="window.open(this.src, '_blank')">
                    </div>

                    <div class="p-8 border-t border-slate-100">
                        <div class="flex flex-wrap justify-between items-start gap-6 mb-6">
                            <div class="flex-1">
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Student Reflection</h3>
                                <p class="text-lg text-slate-700 leading-relaxed font-medium italic">"<?= htmlspecialchars($project['reflection']) ?>"</p>
                            </div>
                            
                            <div class="shrink-0">
                                <?php if ($project['cbc_level']): ?>
                                    <div class="flex gap-4">
                                        <div class="bg-emerald-50 border border-emerald-100 p-5 rounded-3xl text-center min-w-[120px]">
                                            <p class="text-[10px] text-emerald-400 font-bold uppercase">CBC Level</p>
                                            <p class="text-3xl font-black text-emerald-600"><?= $project['cbc_level'] ?></p>
                                        </div>
                                        <div class="bg-slate-50 border border-slate-100 p-5 rounded-3xl text-center min-w-[120px]">
                                            <p class="text-[10px] text-slate-400 font-bold uppercase">Marks (%)</p>
                                            <p class="text-3xl font-black text-slate-800"><?= $project['marks'] ?></p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-amber-50 text-amber-600 px-6 py-4 rounded-2xl border border-amber-100 flex items-center gap-3 font-bold">
                                        Awaiting Teacher Review
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($project['remarks']): ?>
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Teacher Remarks</h4>
                            <p class="text-slate-600 leading-relaxed"><?= htmlspecialchars($project['remarks']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="uploadModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-6">
        <div class="bg-white w-full max-w-xl rounded-[2.5rem] p-10 shadow-2xl relative">
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="absolute top-6 right-8 text-slate-300 hover:text-slate-600 text-2xl font-bold">✕</button>
            
            <h2 class="text-3xl font-black text-slate-800 mb-2">Submit Work</h2>
            <p class="text-slate-500 mb-8 font-medium">Upload a photo of your project. <span class="text-emerald-600 font-bold">Images only (JPG, PNG).</span></p>

            <form action="student_dashboard.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-400 uppercase ml-1">Evidence Photo</label>
                    <div class="relative group">
                        <input type="file" name="project_file" accept="image/*" required 
                               class="w-full border-2 border-dashed border-slate-200 p-10 rounded-3xl text-center cursor-pointer group-hover:border-emerald-400 group-hover:bg-emerald-50/30 transition">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-400 uppercase ml-1">Your Reflection</label>
                    <textarea name="reflection" rows="3" required class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-5 outline-none focus:border-emerald-500 transition font-medium" placeholder="What did you learn from this project?"></textarea>
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-2xl hover:bg-emerald-600 transition shadow-xl active:scale-95 uppercase tracking-widest">
                    Submit Photo
                </button>
            </form>
        </div>
    </div>

</body>
</html>