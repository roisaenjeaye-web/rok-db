@echo off
title RoK Joki - Server Online (Ngrok & Cloudflare)
color 0A
cls
echo ====================================================================
echo                   RoK JOKI - SERVER ONLINE (SATSET)
echo ====================================================================
echo.
echo [1] Menghubungkan Laravel lokal kamu ke server Ngrok...
echo [2] Tunggu sebentar (sekitar 2-3 detik)...
echo.
echo Link HTTPS online akan aktif di bawah.
echo Bagikan link tersebut ke Therta agar bisa joki bareng!
echo.
echo [CATATAN]: Jangan tutup jendela hitam ini selama joki berlangsung.
echo ====================================================================
echo.

.\ngrok.exe http 8000

echo.
echo Server terputus. Tekan tombol apa saja untuk keluar.
pause >nul
