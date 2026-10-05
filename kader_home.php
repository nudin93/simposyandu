<?php
require_once __DIR__ . '/config/init.php';
requireLogin();
$u = function_exists('currentUser') ? currentUser() : null;
if (!$u) $u = ['nama'=>'Kader','role'=>'kader','foto'=>''];
$nama = trim($u['nama'] ?? 'Kader'); if ($nama === '') $nama = 'Kader';
function kh_count($sql){ try { $r = fetchOne($sql); return (int)($r['c'] ?? 0); } catch (Throwable $e) { return 0; } }
$balita = kh_count('SELECT COUNT(*) c FROM balita WHERE status_aktif=1');
$bumil = kh_count('SELECT COUNT(*) c FROM ibu_hamil WHERE status_aktif=1');
$lansia = kh_count('SELECT COUNT(*) c FROM lansia WHERE status_aktif=1');
$bayi = kh_count('SELECT COUNT(*) c FROM bayi WHERE status_aktif=1');
$PT = ['pemeriksaan_balita','pemeriksaan_ibu_hamil','pemeriksaan_bayi','pemeriksaan_lansia'];
function kh_periksa($where){ global $PT; $t = 0; foreach ($PT as $tb){ try { $r = fetchOne('SELECT COUNT(*) c FROM `'.$tb.'` WHERE '.$where); $t += (int)($r['c'] ?? 0); } catch (Throwable $e) {} } return $t; }
$hariIni = kh_periksa('tanggal_pemeriksaan = CURDATE()');
$bulanIni = kh_periksa("tanggal_pemeriksaan >= DATE_FORMAT(CURDATE(),'%Y-%m-01')");
$jadwal = kh_count('SELECT COUNT(*) c FROM jadwal WHERE tanggal_kegiatan >= CURDATE()');
$BL = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
$labels = []; $vals = []; $tot7 = 0;
for ($i = 6; $i >= 0; $i--) { $d = date('Y-m-d', strtotime('-'.$i.' days')); $c = kh_periksa("tanggal_pemeriksaan = '".$d."'"); $labels[] = ((int)date('j', strtotime($d))).' '.$BL[(int)date('n', strtotime($d))]; $vals[] = $c; $tot7 += $c; }
$rata = round($tot7 / 7, 2);
$maxv = max(1, max($vals));
$h = (int)date('G'); $salam = $h < 11 ? 'Selamat pagi' : ($h < 15 ? 'Selamat siang' : ($h < 19 ? 'Selamat sore' : 'Selamat malam'));
$foto = !empty($u['foto']) ? APP_URL.'/uploads/kader/'.rawurlencode($u['foto']) : '';
$__av = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><circle cx='32' cy='32' r='32' fill='%23ccfbf1'/><circle cx='32' cy='24' r='11' fill='%230f766e'/><path d='M10 56c4-12 12-17 22-17s18 5 22 17' fill='%230f766e'/></svg>";
$peng = function_exists('getPengaturan') ? getPengaturan() : [];
$appName = $peng['nama_posyandu'] ?? 'Posyandu';
$KH_DATA = ['balita'=>'Data','ibu_hamil'=>'Ibu Hamil','bayi'=>'Bayi','lansia'=>'Lansia','anak_tk'=>'Anak TK','remaja'=>'Remaja'];
$KH_PERIKSA = ['pemeriksaan_balita'=>'Periksa Balita','pemeriksaan_ibu_hamil'=>'Periksa Bumil','pemeriksaan_bayi'=>'Periksa Bayi','pemeriksaan_lansia'=>'Periksa Lansia','pemeriksaan_anak_tk'=>'Periksa TK','pemeriksaan_remaja'=>'Periksa Remaja'];
$khNavData = ''; $khAddData = ''; $khNavPeriksa = ''; $khAddPeriksa = '';
foreach ($KH_DATA as $k => $lb) { if (function_exists('canViewMenu') && canViewMenu($k)) { $khNavData = $k; break; } }
foreach ($KH_DATA as $k => $lb) { if (function_exists('canEditMenu') && canEditMenu($k)) { $khAddData = $k; break; } }
foreach ($KH_PERIKSA as $k => $lb) { if (function_exists('canViewMenu') && canViewMenu($k)) { $khNavPeriksa = $k; break; } }
foreach ($KH_PERIKSA as $k => $lb) { if (function_exists('canEditMenu') && canEditMenu($k)) { $khAddPeriksa = $k; break; } }
if (!function_exists('canViewMenu')) { $khNavData = 'balita'; $khNavPeriksa = 'pemeriksaan_balita'; }
if (!function_exists('canEditMenu')) { $khAddData = 'balita'; $khAddPeriksa = 'pemeriksaan_balita'; }
$khLaporan = function_exists('canViewMenu') ? canViewMenu('laporan') : true;
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Beranda Kader | <?= htmlspecialchars($appName) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root{--bg:#f1f5f9;--card:#ffffff;--txt:#0f172a;--mut:#64748b;--hero1:#4c1d95;--hero2:#7c3aed;--nav:#ffffff;}
body.dark{--bg:#0f172a;--card:#1e293b;--txt:#f1f5f9;--mut:#94a3b8;--nav:#1e293b;}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--txt);min-height:100vh;}
.wrap{max-width:480px;margin:0 auto;padding-bottom:96px;}
.hero{background:linear-gradient(135deg,var(--hero1),var(--hero2) 60%,#6d28d9);border-radius:0 0 28px 28px;padding:14px 18px 26px;color:#fff;position:relative;overflow:hidden;}
.hero::before,.hero::after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.08);}
.hero::before{width:180px;height:180px;right:-60px;top:-60px;}
.hero::after{width:120px;height:120px;left:-40px;bottom:-50px;}
.topbar{display:flex;align-items:center;gap:14px;position:relative;z-index:2;}
.topbar a{color:#fff;font-size:19px;text-decoration:none;position:relative;}
.topbar .sp{flex:1;}
#bellBadge{position:absolute;top:-7px;right:-9px;background:#ef4444;color:#fff;font-size:10px;min-width:17px;height:17px;line-height:17px;text-align:center;border-radius:9px;padding:0 4px;}
.avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.7);}
.greet{display:flex;align-items:center;gap:12px;margin-top:14px;position:relative;z-index:2;}
.greet h1{font-size:23px;line-height:1.25;}
.greet h1 .yl{color:#fde047;}
.sun{font-size:34px;}
.char{font-size:64px;margin-left:auto;filter:drop-shadow(0 6px 8px rgba(0,0,0,.3));}
.datetime{display:flex;align-items:center;gap:8px;margin-top:10px;font-size:13px;color:#e9d5ff;position:relative;z-index:2;}
.quick{display:flex;gap:10px;margin-top:14px;position:relative;z-index:2;}
.quick a{flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border-radius:14px;font-weight:700;font-size:14px;text-decoration:none;}
.q1{background:#fff;color:#1e293b;}
.q2{background:#facc15;color:#1e293b;}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:16px 14px 0;}
.card{background:var(--card);border-radius:18px;padding:14px;position:relative;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06);}
.card .row1{display:flex;align-items:center;justify-content:space-between;}
.ic{width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;}
.pill{font-size:11px;padding:4px 10px;border-radius:20px;font-weight:600;}
.num{font-size:30px;font-weight:800;margin-top:8px;}
.t1{font-size:13px;font-weight:700;}
.t2{font-size:11px;color:var(--mut);}
.emo{position:absolute;right:8px;bottom:14px;font-size:40px;opacity:.9;}
.bar{height:5px;border-radius:3px;margin-top:10px;}
.chart{background:var(--card);border-radius:18px;margin:12px 14px 0;padding:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);}
.chart h3{font-size:15px;}
.chart .sub{font-size:12px;color:var(--mut);}
.bars{display:flex;align-items:flex-end;gap:6px;height:130px;margin-top:12px;padding-top:8px;}
.bcol{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%;justify-content:flex-end;}
.bval{font-size:10px;font-weight:700;}
.bbar{width:70%;border-radius:6px 6px 3px 3px;background:linear-gradient(180deg,#8b5cf6,#6d28d9);min-height:4px;}
.blab{font-size:9px;color:var(--mut);}
.cfoot{display:flex;gap:10px;margin-top:12px;}
.cbox{flex:1;background:var(--bg);border-radius:12px;padding:10px;display:flex;gap:8px;align-items:center;}
.cbox .ic{width:36px;height:36px;font-size:15px;}
.cbox small{font-size:10px;color:var(--mut);display:block;}
.cbox b{font-size:13px;}
.bottom{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:480px;background:var(--nav);display:flex;padding:8px 4px calc(10px + env(safe-area-inset-bottom));box-shadow:0 -2px 14px rgba(0,0,0,.1);z-index:50;}
.bottom a{flex:1;text-align:center;text-decoration:none;color:var(--mut);font-size:10px;display:flex;flex-direction:column;gap:3px;align-items:center;padding:6px 2px;border-radius:12px;}
.bottom a i{font-size:19px;}
.bottom a.on{background:linear-gradient(135deg,#7c3aed,#4c1d95);color:#fff;}
</style></head><body><div class="wrap">
<div class="hero"><div class="topbar">
<a href="<?= APP_URL ?>/dashboard.php" title="Menu"><i class="fas fa-bars"></i></a><span class="sp"></span>
<a href="#" id="moonBtn" title="Mode gelap"><i class="fas fa-moon"></i></a>
<a href="#" id="khBell" title="Pembaruan"><i class="fas fa-bell"></i><span id="bellBadge" style="display:none">0</span></a>
<?php if ($foto): ?><img src="<?= htmlspecialchars($foto) ?>" class="avatar" alt="foto" onerror="this.style.display='none'"><?php else: ?><img src="<?= $__av ?>" class="avatar" alt="foto"><?php endif; ?>
</div><div class="greet"><span class="sun">🌞</span>
<h1><?= htmlspecialchars($salam) ?>,<br><span class="yl"><?= htmlspecialchars($nama) ?>!</span> 👋</h1>
<span class="char">🧑‍💻</span></div>
<div class="datetime"><i class="far fa-calendar-alt"></i><span id="khDate">-</span><span>•</span><i class="far fa-clock"></i><span id="khClock">-</span></div>
<?php if ($khAddData !== "" || $khAddPeriksa !== ""): ?>
<div class="quick">
<?php if ($khAddData !== ""): ?><a class="q1" href="<?= APP_URL ?>/modules/<?= htmlspecialchars($khAddData) ?>/tambah.php"><i class="fas fa-user-plus"></i>Input Data</a><?php endif; ?>
<?php if ($khAddPeriksa !== ""): ?><a class="q2" href="<?= APP_URL ?>/modules/<?= htmlspecialchars($khAddPeriksa) ?>/tambah.php"><i class="fas fa-stethoscope"></i>Periksa</a><?php endif; ?>
</div>
<?php endif; ?></div>
<div class="grid">
<div class="card"><div class="row1"><span class="ic" style="background:#7c3aed"><i class="fas fa-user-friends"></i></span><span class="pill" style="background:#ede9fe;color:#7c3aed">Hari Ini</span></div><div class="num"><?= (int)$hariIni ?></div><div class="t1">Periksa Hari Ini</div><div class="t2">Pelayanan hari ini</div><span class="emo">👨‍👩‍👧</span><div class="bar" style="background:#7c3aed"></div></div>
<div class="card"><div class="row1"><span class="ic" style="background:#22c55e"><i class="fas fa-calendar-check"></i></span><span class="pill" style="background:#dcfce7;color:#16a34a">Bulan Ini</span></div><div class="num"><?= (int)$bulanIni ?></div><div class="t1">Periksa Bulan Ini</div><div class="t2">Pelayanan bulan ini</div><span class="emo">📅</span><div class="bar" style="background:#22c55e"></div></div>
<div class="card"><div class="row1"><span class="ic" style="background:#ec4899"><i class="fas fa-users"></i></span><span class="pill" style="background:#fce7f3;color:#db2777">Total</span></div><div class="num"><?= (int)$balita ?></div><div class="t1">Total Balita</div><div class="t2"><?= (int)$bumil ?> bumil · <?= (int)$lansia ?> lansia · <?= (int)$bayi ?> bayi</div><span class="emo">👪</span><div class="bar" style="background:#ec4899"></div></div>
<div class="card"><div class="row1"><span class="ic" style="background:#f59e0b"><i class="fas fa-list"></i></span><span class="pill" style="background:#fef3c7;color:#d97706">Jadwal</span></div><div class="num"><?= (int)$jadwal ?></div><div class="t1">Jadwal Mendatang</div><div class="t2">Kegiatan terjadwal</div><span class="emo">🔔</span><div class="bar" style="background:#f59e0b"></div></div>
</div>
<div class="chart"><div class="row1" style="display:flex;justify-content:space-between;align-items:center"><div><h3>📈 Grafik Periksa 7 Hari</h3><div class="sub">Jumlah pelayanan per hari</div></div><span class="pill" style="background:var(--bg);color:var(--mut)">7 Hari Terakhir</span></div>
<div class="bars"><?php foreach ($vals as $xi => $xv): $ph = $maxv > 0 ? round($xv / $maxv * 100) : 0; ?><div class="bcol"><span class="bval"><?= (int)$xv ?></span><div class="bbar" style="height:<?= $ph ?>%"></div><span class="blab"><?= htmlspecialchars($labels[$xi]) ?></span></div><?php endforeach; ?></div>
<div class="cfoot"><div class="cbox"><span class="ic" style="background:#7c3aed"><i class="fas fa-chart-line"></i></span><div><small>Rata-rata per hari</small><b><?= htmlspecialchars(number_format($rata, 2, ",", ".")) ?> periksa</b></div></div><div class="cbox"><div><small>Total 7 Hari</small><b><?= (int)$tot7 ?> periksa</b></div><span class="ic" style="background:#7c3aed;margin-left:auto"><i class="fas fa-users"></i></span></div></div></div>
<nav class="bottom">
<a href="<?= APP_URL ?>/kader_home.php" class="on"><i class="fas fa-home"></i>Beranda</a>
<?php if ($khNavData !== ""): ?><a href="<?= APP_URL ?>/modules/<?= htmlspecialchars($khNavData) ?>/index.php"><i class="fas fa-users"></i>Data</a><?php endif; ?>
<?php if ($khNavPeriksa !== ""): ?><a href="<?= APP_URL ?>/modules/<?= htmlspecialchars($khNavPeriksa) ?>/index.php"><i class="fas fa-list"></i>Periksa</a><?php endif; ?>
<?php if ($khLaporan): ?><a href="<?= APP_URL ?>/modules/laporan/index.php"><i class="fas fa-chart-bar"></i>Laporan</a><?php endif; ?>
<a href="<?= APP_URL ?>/modules/kader/profile.php"><i class="fas fa-cog"></i>Profil</a>
</nav></div>
<script>
try{ if(localStorage.getItem("kh_dark")==="1"){ document.body.classList.add("dark"); } }catch(e){}
document.getElementById("moonBtn").addEventListener("click",function(e){ e.preventDefault(); var on=document.body.classList.toggle("dark"); try{ localStorage.setItem("kh_dark", on?"1":"0"); }catch(e){} });
function khTick(){ try{ var d=new Date(); document.getElementById("khDate").textContent=d.toLocaleDateString("id-ID",{weekday:"long",day:"numeric",month:"long",year:"numeric"}); var p=function(n){return (n<10?"0":"")+n;}; document.getElementById("khClock").textContent=p(d.getHours())+"."+p(d.getMinutes())+"."+p(d.getSeconds()); }catch(e){} }
khTick(); setInterval(khTick,1000);
function khEsc(s){ var d=document.createElement("div"); d.textContent=String(s==null?"":s); return d.innerHTML; }
function khSeen(){ try{ return localStorage.getItem("simposyandu_seen_ver")||""; }catch(e){ return ""; } }
fetch("<?= APP_URL ?>/ajax/whatsnew.php?seen="+encodeURIComponent(khSeen()||"0"),{credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){ if(j&&j.success&&(j.notes||[]).length){ var b=document.getElementById("bellBadge"); if(b){ b.style.display="block"; b.textContent=(j.notes.length>9?"9+":j.notes.length); } window._khNotes=j.notes; window._khVer=j.version; } }).catch(function(){});
document.getElementById("khBell").addEventListener("click",function(e){e.preventDefault();if(!window.Swal)return;var ns=window._khNotes||[];if(!ns.length){Swal.fire("Sudah terbaru","Tidak ada pembaruan.","success");return;}var n=ns[0];var h='<div class="text-start">Tersedia <b>v'+khEsc(n.versi)+'</b>';if(n.judul)h+='<br><b>'+khEsc(n.judul)+'</b>';if(n.items&&n.items.length){h+='<ul class="text-start">';for(var i=0;i<n.items.length;i++){h+='<li>'+khEsc(n.items[i])+'</li>';}h+='</ul>';}h+='</div>';Swal.fire({title:"Perbarui ke v"+n.versi,html:h,width:480,confirmButtonText:"Mengerti"});});
</script></body></html>