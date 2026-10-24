<?php
/**
 * ==========================================================
 * SIMPOSYANDU - Cek pembaruan otomatis dari GitHub
 * ==========================================================
 * Endpoint JSON untuk lonceng + popup "Update Terbaru".
 * Tanpa token (repo publik), dengan cache 6 jam agar hemat
 * rate-limit GitHub API (60 req/jam/IP) dan tetap cepat.
 *
 * Response sukses:
 * { success, local_version, remote_version, update_available,
 *   remote_commit: {sha,message,date,url}, commits: [...],
 *   compare_url, checked_at, cached }
 * Gagal/offline: { success:false, offline:true, ...cache lama jika ada }
 */
error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

define('GH_OWNER', 'nudin93');
define('GH_REPO', 'simposyandu');
define('GH_BRANCH', 'main');
define('GH_CACHE_TTL', 6 * 3600);

function gh_cache_file() {
    $cands = [
        __DIR__ . '/../uploads/backup/.github_update_cache.json',
        sys_get_temp_dir() . '/simposyandu_github_update.json',
    ];
    foreach ($cands as $f) {
        $d = dirname($f);
        if (is_dir($d) && is_writable($d)) return $f;
    }
    return $cands[1];
}

function gh_http_get($url, $timeout = 8) {
    // 1) cURL (lebih andal di shared hosting)
    if (function_exists('curl_init')) {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT => 'SIMPosyandu-Update-Checker/1.0',
                CURLOPT_HTTPHEADER => ['Accept: application/vnd.github+json'],
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $out = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($out !== false && $code >= 200 && $code < 300) return $out;
        } catch (Throwable $e) {}
    }
    // 2) file_get_contents fallback
    try {
        $ctx = stream_context_create(['http' => [
            'method' => 'GET',
            'timeout' => $timeout,
            'header' => "User-Agent: SIMPosyandu-Update-Checker/1.0\r\nAccept: application/vnd.github+json\r\n",
            'follow_location' => 1,
        ], 'ssl' => ['verify_peer' => true]]);
        $out = @file_get_contents($url, false, $ctx);
        if ($out !== false && $out !== '') return $out;
    } catch (Throwable $e) {}
    return false;
}

$local = defined('APP_VERSION') ? APP_VERSION : '1.0.4';
$cacheFile = gh_cache_file();
$fresh = (($_GET['fresh'] ?? '') === '1');

// Tanggal rilis versi lokal (dari changelog) untuk deteksi commit baru tanpa bump versi
function gh_local_date($local) {
    try {
        if (function_exists('app_changelog')) {
            $best = '';
            foreach (app_changelog() as $n) {
                $v = (string)($n['versi'] ?? '');
                $d = substr((string)($n['tanggal'] ?? ''), 0, 10);
                if ($v !== '' && version_compare($v, (string)$local, '<=') && $d !== '' && $d > $best) $best = $d;
            }
            if ($best !== '') return $best;
        }
    } catch (Throwable $e) {}
    return '';
}
$localDate = gh_local_date($local);

// Cache masih segar? langsung pakai (tetap bandingkan dengan versi lokal saat ini)
// Dilewati bila ?fresh=1 (mis. saat lonceng diklik — wajib cek live ke GitHub)
try {
    if (!$fresh && is_file($cacheFile) && (time() - (int)@filemtime($cacheFile)) < GH_CACHE_TTL) {
        $c = json_decode((string)@file_get_contents($cacheFile), true);
        if (is_array($c) && !empty($c['remote_version'])) {
            $c['local_version'] = $local;
            $verNew = version_compare((string)$c['remote_version'], (string)$local, '>');
            // Commit GitHub lebih baru dari tanggal rilis lokal = ada update walau versi belum naik
            $dateNew = (!empty($c['remote_commit']['date']) && $localDate !== '' && $c['remote_commit']['date'] > $localDate);
            $c['update_available'] = ($verNew || $dateNew);
            $c['cached'] = true;
            echo json_encode($c, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
} catch (Throwable $e) {}

// 1) Versi remote dari config/version.php di branch main
$remoteVer = '';
$raw = gh_http_get('https://raw.githubusercontent.com/' . GH_OWNER . '/' . GH_REPO . '/' . GH_BRANCH . '/config/version.php');
if (is_string($raw) && $raw !== '') {
    if (preg_match("/define\s*\(\s*'APP_VERSION'\s*,\s*'([^']+)'/", $raw, $m)) {
        $remoteVer = trim($m[1]);
    }
}

// 2) Commit terbaru (pesan + tanggal + link) untuk ditampilkan di notif
$commit = null;
$commits = [];
$api = gh_http_get('https://api.github.com/repos/' . GH_OWNER . '/' . GH_REPO . '/commits?per_page=5&sha=' . GH_BRANCH);
if (is_string($api) && $api !== '') {
    $arr = json_decode($api, true);
    if (is_array($arr)) {
        foreach ($arr as $ci) {
            if (!is_array($ci)) continue;
            $commits[] = [
                'sha' => substr((string)($ci['sha'] ?? ''), 0, 7),
                'message' => (string)(($ci['commit']['message'] ?? '')),
                'date' => substr((string)(($ci['commit']['committer']['date'] ?? ($ci['commit']['author']['date'] ?? ''))), 0, 10),
                'url' => (string)($ci['html_url'] ?? ''),
            ];
        }
        if (!empty($commits[0])) $commit = $commits[0];
    }
}

if ($remoteVer === '' && $commit === null) {
    // Offline / diblokir hosting — kembalikan cache lama bila ada
    $old = null;
    try { $old = is_file($cacheFile) ? json_decode((string)@file_get_contents($cacheFile), true) : null; } catch (Throwable $e) {}
    if (is_array($old) && !empty($old['remote_version'])) {
        $old['local_version'] = $local;
        $old['cached'] = true;
        $old['stale'] = true;
        echo json_encode($old, JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(['success' => false, 'offline' => true, 'local_version' => $local], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($remoteVer === '' && $commit !== null) $remoteVer = $local; // versi tak terbaca, tapi commit dapat — anggap tidak ada info versi
// Commit baru sejak cek terakhir (walau versi belum naik) = tetap update
$shaChanged = false;
try {
    if (is_file($cacheFile)) {
        $prev = json_decode((string)@file_get_contents($cacheFile), true);
        $prevSha = (string)(($prev['remote_commit']['sha'] ?? ''));
        $newSha = (string)(($commit['sha'] ?? ''));
        if ($prevSha !== '' && $newSha !== '' && $newSha !== $prevSha) $shaChanged = true;
    }
} catch (Throwable $e) {}
$verNew = ($remoteVer !== '' && version_compare($remoteVer, (string)$local, '>'));
$dateNew = ($commit !== null && !empty($commit['date']) && $localDate !== '' && $commit['date'] > $localDate);
$reason = $verNew ? 'version' : (($shaChanged || $dateNew) ? 'commit' : '');
$resp = [
    'success' => true,
    'local_version' => $local,
    'remote_version' => $remoteVer,
    'update_available' => ($verNew || $shaChanged || $dateNew),
    'update_reason' => $reason,
    'remote_commit' => $commit,
    'commits' => $commits,
    'compare_url' => 'https://github.com/' . GH_OWNER . '/' . GH_REPO . '/commits/' . GH_BRANCH,
    'repo_url' => 'https://github.com/' . GH_OWNER . '/' . GH_REPO,
    'checked_at' => date('c'),
    'cached' => false,
];
try { @file_put_contents($cacheFile, json_encode($resp, JSON_UNESCAPED_UNICODE)); } catch (Throwable $e) {}
echo json_encode($resp, JSON_UNESCAPED_UNICODE);
