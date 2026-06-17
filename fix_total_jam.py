import glob

files = glob.glob("/var/www/hris.didimax.online/resources/views/laporan/*.blade.php")
for f in files:
    with open(f, 'r') as file:
        content = file.read()
    
    new_content = content.replace("is_array($totalJamJadwal) ? ($totalJamJadwal[\\'total_jam\\'] ?? 0) : $totalJamJadwal;", "is_array($totalJamJadwal) ? ($totalJamJadwal['total_jam'] ?? 0) : $totalJamJadwal;")
    
    if new_content != content:
        with open(f, 'w') as file:
            file.write(new_content)
        print(f"Fixed {f}")
