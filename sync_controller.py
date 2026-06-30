import sys

src = r'd:\CAPSTONE PROJECT\student-referral-system\app\Http\Controllers\Admin\SeminarController.php'
dst = r'd:\CAPSTONE PROJECT\student-referral-system\app\Http\Controllers\Counselor\SeminarController.php'

with open(src, 'r', encoding='utf-8') as f:
    text = f.read()

text = text.replace(r'namespace App\Http\Controllers\Admin;', r'namespace App\Http\Controllers\Counselor;')
text = text.replace("route('admin.seminars", "route('counselor.seminars")
text = text.replace("view('admin.seminars", "view('counselor.seminars")

with open(dst, 'w', encoding='utf-8') as f:
    f.write(text)
