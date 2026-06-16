Set-StrictMode -Off

Write-Host "=== BACKEND CHECK ==="
$base = $PSScriptRoot
if (-not $base) { $base = (Get-Location).Path }
if (-not (Test-Path (Join-Path $base 'backend'))) { Write-Host "backend folder not found at $(Join-Path $base 'backend')"; exit 2 }
Push-Location (Join-Path $base 'backend')
php -v
$job = Start-Job -ScriptBlock { php artisan serve --host=127.0.0.1 --port=8000 }
Start-Sleep -Seconds 6
$out = Receive-Job -Job $job -ErrorAction SilentlyContinue
if ($out) { $out | Select-Object -First 200 | ForEach-Object { Write-Host $_ } } else { Write-Host 'No output captured from artisan job.' }
if ($job.State -ne 'Completed') { Stop-Job -Job $job -Force -ErrorAction SilentlyContinue }
Pop-Location

Write-Host "`n=== FRONTEND CHECK ==="
if (-not (Test-Path (Join-Path $base 'frontend'))) { Write-Host "frontend folder not found at $(Join-Path $base 'frontend')"; exit 3 }
Push-Location (Join-Path $base 'frontend')
node -v
npm -v
$job2 = Start-Job -ScriptBlock { npm run dev }
Start-Sleep -Seconds 8
$out2 = Receive-Job -Job $job2 -ErrorAction SilentlyContinue
if ($out2) { $out2 | Select-Object -First 200 | ForEach-Object { Write-Host $_ } } else { Write-Host 'No output captured from npm job.' }
if ($job2.State -ne 'Completed') { Stop-Job -Job $job2 -Force -ErrorAction SilentlyContinue }

if (-not $out2) {
    Write-Host 'npm run dev produced no output or failed; attempting npm install and retry.'
    npm install
    $job3 = Start-Job -ScriptBlock { npm run dev }
    Start-Sleep -Seconds 8
    $out3 = Receive-Job -Job $job3 -ErrorAction SilentlyContinue
    if ($out3) { $out3 | Select-Object -First 200 | ForEach-Object { Write-Host $_ } } else { Write-Host 'No output captured on retry.' }
    if ($job3.State -ne 'Completed') { Stop-Job -Job $job3 -Force -ErrorAction SilentlyContinue }
}
Pop-Location

Write-Host "`n=== DONE ==="