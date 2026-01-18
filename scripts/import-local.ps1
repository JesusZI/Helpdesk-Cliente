param()

Write-Host "Starting docker-compose (build if needed) ..."
docker-compose up -d --build

Write-Host "Waiting for MySQL to initialize (sleep 8s)..."
Start-Sleep -s 8

$sqlPath = Join-Path (Get-Location) 'helpdesk (6).sql'
if (-Not (Test-Path $sqlPath)) {
    Write-Error "SQL dump not found at $sqlPath"
    exit 1
}

Write-Host "Importing $sqlPath into container 'db' (database: helpdesk) ..."
Get-Content -Raw -Path $sqlPath | docker-compose exec -T db mysql -u root -prootpassword helpdesk

if ($LASTEXITCODE -eq 0) {
    Write-Host "Import completed successfully."
} else {
    Write-Error "Import finished with exit code $LASTEXITCODE"
}
