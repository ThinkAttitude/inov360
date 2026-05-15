# Sweep: replace `echo json_encode(['success' => false, 'error' => 'CODE'])` patterns
# with `json_error('CODE', <status>, [extra])` and inject require_once for the
# responses.php helper. Idempotent: skips files already migrated.

$ErrorActionPreference = 'Stop'

$apiRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\backend\api')).Path
$helperRel = 'lib/helper/responses.php'

# HTTP status inference from code (default 200)
$statusFor = @{
    'UNAUTHENTICATED'         = 401
    'FORBIDDEN'               = 403
    'FORBIDDEN_PERMISSION'    = 403
    'SERVER_ERROR'            = 500
    'INTERNAL_ERROR'          = 500
    'DB_ERROR'                = 500
    'NOT_FOUND'               = 404
    'USER_NOT_FOUND'          = 404
    'PROFILE_NOT_FOUND'       = 404
    'REQUEST_NOT_FOUND'       = 404
    'PERIOD_NOT_FOUND'        = 404
    'OBRA_NOT_FOUND'          = 404
    'SUB_NOT_FOUND'           = 404
    'MONTH_NOT_FOUND'         = 404
    'PERMISSION_NOT_FOUND'    = 404
    'METHOD_NOT_ALLOWED'      = 405
    'CONFLICT'                = 409
    'PERIOD_CLOSED'           = 409
    'PERIOD_LOCKED'           = 409
    'NOT_SUBMITTED'           = 409
    'ALREADY_SUBMITTED'       = 409
    'RESP_SUB_CONFLICT'       = 409
}

# Pattern: echo json_encode([ 'ok'|'success' => false, 'error'|'code' => 'CODE' [, extras] ]); [exit;]
$pattern = "(?ms)echo\s+json_encode\(\s*\[\s*['""](?:ok|success)['""]\s*=>\s*false\s*,\s*['""](?:error|code)['""]\s*=>\s*['""]([A-Z0-9_]+)['""]\s*(,\s*([^\]]*?))?\]\s*(?:,\s*JSON_[A-Z_]+(?:\s*\|\s*JSON_[A-Z_]+)*)?\s*\)\s*;\s*(?:exit\s*;)?"

function Get-RequireRelPath($file) {
    $fileDir = [System.IO.Path]::GetDirectoryName($file)
    $helperFull = (Resolve-Path (Join-Path $apiRoot $helperRel)).Path
    # Build a relative __DIR__ . '/...' path
    $apiFull = (Resolve-Path $apiRoot).Path
    $relFromApi = $helperFull.Substring($apiFull.Length).Replace('\','/')
    $relFromFile = ''
    $depth = ($fileDir.Substring($apiFull.Length).Trim('\').Split('\') | Where-Object { $_ -ne '' }).Count
    for ($i = 0; $i -lt $depth; $i++) { $relFromFile += '../' }
    return "__DIR__ . '/" + $relFromFile + $relFromApi.TrimStart('/') + "'"
}

$files = Get-ChildItem -Path $apiRoot -Recurse -Filter '*.php' | Where-Object { $_.FullName -notmatch '\\lib\\helper\\' }
$changedCount = 0

foreach ($f in $files) {
    $content = [System.IO.File]::ReadAllText($f.FullName)
    if ([string]::IsNullOrEmpty($content)) { continue }
    if ($content -notmatch "json_encode\(\s*\[\s*['""](?:ok|success)['""]\s*=>\s*false") { continue }

    $original = $content

    $content = [regex]::Replace($content, $pattern, {
        param($m)
        $code = $m.Groups[1].Value
        $extra = if ($m.Groups[2].Success) { $m.Groups[3].Value.Trim() } else { '' }
        $status = if ($statusFor.ContainsKey($code)) { $statusFor[$code] } else { 200 }

        $args = "'" + $code + "'"
        if ($status -ne 200 -or $extra) { $args += ", $status" }
        if ($extra) {
            $args += ", [$extra]"
        }
        return "json_error($args);"
    })

    if ($content -ne $original) {
        # Detect original line ending (prefer to preserve)
        $useCrlf = $original.Contains("`r`n")
        $nl = if ($useCrlf) { "`r`n" } else { "`n" }
        $req = Get-RequireRelPath $f.FullName
        $reqLine = "require_once $req;"

        if ($content -notmatch [regex]::Escape("/lib/helper/responses.php")) {
            # Split into lines to inject cleanly
            $lines = $content -split "(`r`n|`n)"
            # $lines alternates: content, separator, content, separator...
            # Find the last require_once line index (within the top of file)
            $insertAfter = -1
            for ($i = 0; $i -lt [Math]::Min($lines.Length, 60); $i += 2) {
                if ($lines[$i] -match '^\s*<\?php') { $insertAfter = $i }
                if ($lines[$i] -match '^\s*require(_once)?\s+') { $insertAfter = $i }
            }
            if ($insertAfter -ge 0) {
                # Insert reqLine + separator AFTER the matched line's separator (i.e. at index insertAfter + 2)
                $insertIdx = $insertAfter + 2
                if ($insertIdx -gt $lines.Length) { $insertIdx = $lines.Length }
                $before = $lines[0..($insertIdx - 1)]
                if ($insertIdx -lt $lines.Length) {
                    $after = $lines[$insertIdx..($lines.Length - 1)]
                } else {
                    $after = @()
                }
                $lines = @($before) + @($reqLine, $nl) + @($after)
                $content = ($lines -join '')
            }
        }

        [System.IO.File]::WriteAllText($f.FullName, $content)
        $changedCount++
        Write-Host "MIGRATED: $($f.FullName.Substring($apiRoot.Length + 1))"
    }
}

Write-Host ""
Write-Host "Total endpoints migrated: $changedCount"
