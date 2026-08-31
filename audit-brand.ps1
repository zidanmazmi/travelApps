$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
$Patterns = @(
    'am\s*wisata',
    'amwisata',
    'haqeem',
    'am-wisata-bogor-logo',
    'am-wisata-bogor-favicon'
)

$Targets = @('app', 'public', 'resources') |
    ForEach-Object { Join-Path $Root $_ } |
    Where-Object { Test-Path $_ }

$Matches = @()
foreach ($Target in $Targets) {
    $Files = Get-ChildItem -Path $Target -Recurse -File -ErrorAction SilentlyContinue |
        Where-Object { $_.FullName -notmatch '[\\/]vendor[\\/]' -and $_.FullName -notmatch '[\\/]writable[\\/]' }

    foreach ($File in $Files) {
        try {
            foreach ($Pattern in $Patterns) {
                $Result = Select-String -Path $File.FullName -Pattern $Pattern -CaseSensitive:$false -ErrorAction SilentlyContinue
                if ($Result) {
                    $Matches += $Result
                }
            }
        } catch {
            # Ignore binary/unreadable files.
        }
    }
}

if ($Matches.Count -gt 0) {
    $Matches | Select-Object Path, LineNumber, Line | Format-Table -AutoSize
    Write-Host "`nFAIL: legacy brand reference masih ditemukan." -ForegroundColor Red
    exit 1
}

Write-Host 'PASS: source app/public/resources bersih dari legacy brand reference.' -ForegroundColor Green
exit 0
