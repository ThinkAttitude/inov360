<?php
// api/grupo_inov/obras/export_zip.php
declare(strict_types=1);
session_start();

/* ===== Auth ===== */
if (empty($_SESSION['is_login']) || empty($_SESSION['user'])) {
    http_response_code(401); header('Content-Type: application/json');
    echo json_encode(["ok"=>false,"code"=>"UNAUTHENTICATED"]); exit;
}

$perms = $_SESSION['user']['permissions'] ?? [];
if (!is_array($perms) || !in_array(8, $perms, true)) { // sht_management
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'FORBIDDEN_PERMISSION']); exit;
}

/* ===== Config ===== */
require_once __DIR__ . '/../../includes/db.php';
$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

const BASE_FILES_DIR  = __DIR__ . '/../../../uploads';
const BASE_PUBLIC_URL = '';

/* ===== Libs ===== */
require_once __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/* ===== Helpers ===== */
function bad_request(string $m){ http_response_code(422); header('Content-Type: application/json'); echo json_encode(["ok"=>false,"code"=>"BAD_REQUEST","message"=>$m]); exit; }
function is_empty(?string $v): bool { return $v === null || trim($v) === ''; }
function expired(?string $dateYmd): bool {
    if (is_empty($dateYmd)) return true;
    $ts = strtotime($dateYmd); if ($ts === false) return true;
    return $ts < strtotime('today');
}
function safe_path(?string $p): ?string {
    if ($p === null) return null;
    $p = trim($p); if ($p === '') return null;
    $p = str_replace('\\', '/', $p);
    $p = preg_replace('#\.\.+#', '', $p);
    if (is_file($p)) return $p;
    $p = preg_replace('#^/?uploads/#', '', $p);
    $abs = rtrim(BASE_FILES_DIR, '/') . 'export_zip.php/' . ltrim($p, '/');
    return is_file($abs) ? $abs : null;
}
function add_if_exists(ZipArchive $zip, ?string $absPath, string $toDir, ?string $originalRel = null): bool {
    if (!$absPath || !is_file($absPath)) return false;
    $name = $originalRel ? basename($originalRel) : basename($absPath);
    return $zip->addFile($absPath, rtrim($toDir, '/') . 'export_zip.php/' .$name);
}

/* ===== Input ===== */
/* Aceita novo PK (?id) e número legado (?id_obra). Um dos dois é obrigatório. */
$obraPk  = (int)($_GET['id'] ?? 0);
$obraNum = (int)($_GET['id_obra'] ?? 0);
if ($obraPk <= 0 && $obraNum <= 0) bad_request('Parâmetro "id" (PK) ou "id_obra" (número) é obrigatório.');

try {
    /* ===== Obra ===== */
    if ($obraPk > 0) {
        $st = $pdo->prepare("SELECT id, id_obra, nome_obra, `do`, encarregado, unidade FROM obra WHERE id=:id LIMIT 1");
        $st->execute([':id'=>$obraPk]);
    } else {
        $st = $pdo->prepare("SELECT id, id_obra, nome_obra, `do`, encarregado, unidade FROM obra WHERE id_obra=:num LIMIT 1");
        $st->execute([':num'=>$obraNum]);
    }
    $obra = $st->fetch(PDO::FETCH_ASSOC);
    if (!$obra) { http_response_code(404); header('Content-Type: application/json'); echo json_encode(["ok"=>false,"code"=>"NOT_FOUND","message"=>"Obra não encontrada."]); exit; }
    $obraPk  = (int)$obra['id'];
    $obraNum = (int)$obra['id_obra'];

    /* ===== Associações + subempreiteiros =====
       Preferimos obra_pk; mantemos fallback para obra_id durante a transição. */
    $a = $pdo->prepare("
        SELECT so.sub_user_id, s.nome_empresa, s.email_contacto, s.nif, s.morada, s.zona_atuacao
          FROM subs_obra so
          JOIN subempreiteiro s ON s.user_id = so.sub_user_id
         WHERE (so.obra_pk = :pk OR so.obra_id = :num)
         ORDER BY s.nome_empresa ASC
    ");
    $a->execute([':pk'=>$obraPk, ':num'=>$obraNum]);
    $subs = $a->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $subIds = array_map(fn($r)=>(int)$r['sub_user_id'], $subs);

    /* ===== Docs empresa ===== */
    $docsBySub = [];
    if ($subIds) {
        $in = implode(',', array_fill(0, count($subIds), '?'));
        $d = $pdo->prepare("
            SELECT user_id, atividade, num_alvara, niss_alvara,
                   sat_comp, sat_n_apolice, sat_validade, sat_mod_seguro,
                   src_comp, src_n_apolice, src_validade,
                   dec_ss, dec_finan, created_at
              FROM doc_empresas
             WHERE user_id IN ($in)
        ");
        $d->execute($subIds);
        foreach ($d->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $docsBySub[(int)$row['user_id']] = $row;
        }
    }

    /* ===== Colaboradores por sub ===== */
    $colsBySub = [];
    if ($subIds) {
        $in = implode(',', array_fill(0, count($subIds), '?'));
        $c = $pdo->prepare("
            SELECT *
              FROM doc_colaboradores
             WHERE sub_user_id IN ($in)
             ORDER BY sub_user_id, nome_colab, id
        ");
        $c->execute($subIds);
        foreach ($c->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $sid = (int)$row['sub_user_id'];
            $colsBySub[$sid][] = $row;
        }
    }

    /* ===== 1) Spreadsheet ===== */
    $ss = new Spreadsheet();

    // Sheet: Obra
    $shObra = $ss->getActiveSheet();
    $shObra->setTitle('Obra');
    $shObra->fromArray([
        ['Campo','Valor'],
        ['ID (PK)',       $obraPk],
        ['Número',        $obraNum],
        ['Nome',          $obra['nome_obra']],
        ['Diretor de Obra',$obra['do']],
        ['Encarregado',   $obra['encarregado']],
        ['Unidade',       $obra['unidade']],
        ['Subemp. Associados', count($subs)]
    ]);

    // Regra docs ok
    $isSubOk = function(array $d): bool {
        $sat_missing = is_empty($d['sat_comp'] ?? null) || is_empty($d['sat_n_apolice'] ?? null) || expired($d['sat_validade'] ?? null) || is_empty($d['sat_mod_seguro'] ?? null);
        $src_missing = is_empty($d['src_comp'] ?? null) || is_empty($d['src_n_apolice'] ?? null) || expired($d['src_validade'] ?? null);
        $dec_missing = is_empty($d['dec_ss'] ?? null) || is_empty($d['dec_finan'] ?? null);
        return !$sat_missing && !$src_missing && !$dec_missing;
    };

    // Sheet: Subempreiteiros
    $shSubs = $ss->createSheet()->setTitle('Subempreiteiros');
    $shSubs->fromArray([[
        'user_id','Empresa','NIF','Email','Morada','Zona Atuação',
        'Atividade','Alvará Nº','NISS Alvará',
        'SAT Comp','SAT Nº','SAT Validade','SAT Modalidade',
        'SRC Comp','SRC Nº','SRC Validade',
        'Dec. SS','Dec. Finanças','Estado'
    ]]);
    $r = 2;
    foreach ($subs as $s) {
        $uid = (int)$s['sub_user_id'];
        $d   = $docsBySub[$uid] ?? [];
        $estado = $d ? ($isSubOk($d) ? 'ok' : 'em_falta') : 'em_falta';

        $shSubs->fromArray([[
            $uid, $s['nome_empresa'], $s['nif'], $s['email_contacto'], $s['morada'], $s['zona_atuacao'],
            $d['atividade'] ?? null, $d['num_alvara'] ?? null, $d['niss_alvara'] ?? null,
            $d['sat_comp'] ?? null, $d['sat_n_apolice'] ?? null, $d['sat_validade'] ?? null, $d['sat_mod_seguro'] ?? null,
            $d['src_comp'] ?? null, $d['src_n_apolice'] ?? null, $d['src_validade'] ?? null,
            (BASE_PUBLIC_URL && !is_empty($d['dec_ss'] ?? null)) ? BASE_PUBLIC_URL.'/'.$d['dec_ss'] : ($d['dec_ss'] ?? null),
            (BASE_PUBLIC_URL && !is_empty($d['dec_finan'] ?? null)) ? BASE_PUBLIC_URL.'/'.$d['dec_finan'] : ($d['dec_finan'] ?? null),
            $estado
        ]], null, "A{$r}");
        $r++;
    }

    // Sheet: Colaboradores
    $shCol = $ss->createSheet()->setTitle('Colaboradores');
    $headers = [
        'sub_user_id','colab_id','Nome','NIF','Categoria','Estado',
        'doc_id_path','cv_ss_path','fam_path','entrega_epi_path',
        'form_esp_path','ficha_trab_path','created_at','updated_at'
    ];
    $shCol->fromArray([$headers]);
    $r = 2;
    foreach ($subs as $s) {
        $uid = (int)$s['sub_user_id'];
        foreach ($colsBySub[$uid] ?? [] as $cRow) {
            $row = [
                $uid,(int)$cRow['id'],
                $cRow['nome_colab'] ?? null,
                $cRow['nif'] ?? null,
                $cRow['categ_prof'] ?? null,
                $cRow['estado'] ?? null,
                $cRow['doc_id_path'] ?? null,
                $cRow['cv_ss_path'] ?? null,
                $cRow['fam_path'] ?? null,
                $cRow['entrega_epi_path'] ?? null,
                $cRow['form_esp_path'] ?? null,
                $cRow['ficha_trab_path'] ?? null,
                $cRow['created_at'] ?? null,
                $cRow['updated_at'] ?? null,
            ];
            if (BASE_PUBLIC_URL) {
                foreach ([6,7,8,9,10,11,12,13,14] as $idx) {
                    if (!is_empty($row[$idx])) $row[$idx] = BASE_PUBLIC_URL.'/'.$row[$idx];
                }
            }
            $shCol->fromArray([$row], null, "A{$r}");
            $r++;
        }
    }

    // guarda xlsx temporário
    $tmpDir = sys_get_temp_dir().'/sht360_export_'.bin2hex(random_bytes(4));
    if (!mkdir($tmpDir) && !is_dir($tmpDir)) throw new RuntimeException('Falha a criar tmp dir.');
    $xlsxPath = "{$tmpDir}/obra.xlsx";
    (new Xlsx($ss))->save($xlsxPath);

    /* ===== 2) ZIP com Excel + ficheiros ===== */
    $zipName = 'Obra_'.$obraNum.'_id'.$obraPk.'_'.date('Ymd_His').'.zip';
    $zipPath = "{$tmpDir}/{$zipName}";
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Não foi possível criar o ZIP.');
    }

    $baseInside = 'Obra_'.$obraNum.'/';
    $zip->addFile($xlsxPath, $baseInside.'obra.xlsx');

    // manifest.json
    $manifest = [
        'obra' => [
            'id_pk' => $obraPk,
            'numero'=> $obraNum,
            'nome'  => $obra['nome_obra'],
            'diretor' => $obra['do'],
            'encarregado' => $obra['encarregado'],
            'unidade' => $obra['unidade'],
        ],
        'exported_at' => date('c'),
        'subempreiteiros' => [],
    ];

    foreach ($subs as $s) {
        $uid = (int)$s['sub_user_id'];
        $empresaDir = $baseInside.'subempreiteiros/'.($s['nif'] ?? $uid).' - '.$s['nome_empresa'].'/';
        $zip->addEmptyDir($empresaDir.'empresa');
        $zip->addEmptyDir($empresaDir.'colaboradores');

        // empresa
        $d = $docsBySub[$uid] ?? [];
        $filesEmpresa = [
            'dec_ss'    => $d['dec_ss']    ?? null,
            'dec_finan' => $d['dec_finan'] ?? null,
            'sat_comp'  => $d['sat_comp']  ?? null,
            'src_comp'  => $d['src_comp']  ?? null,
        ];
        foreach ($filesEmpresa as $k => $rel) {
            $abs = safe_path($rel);
            add_if_exists($zip, $abs, $empresaDir.'empresa', $rel);
        }

        // colaboradores
        $colabs = $colsBySub[$uid] ?? [];
        $manifestColabs = [];
        foreach ($colabs as $cRow) {
            $cid  = (int)$cRow['id'];
            $cdir = $empresaDir.'colaboradores/'.$cid.' - '.($cRow['nome_colab'] ?? 'SemNome').'/';
            $zip->addEmptyDir($cdir);

            $collFiles = [
                'doc_id_path'       => $cRow['doc_id_path']      ?? null,
                'cv_ss_path'        => $cRow['cv_ss_path']       ?? null,
                'fam_path'          => $cRow['fam_path']         ?? null,
                'entrega_epi_path'  => $cRow['entrega_epi_path'] ?? null,
                'form_esp_path'     => $cRow['form_esp_path']    ?? null,
                'ficha_trab_path'   => $cRow['ficha_trab_path']  ?? null,
            ];
            foreach ($collFiles as $colName => $rel) {
                $abs = safe_path($rel);
                add_if_exists($zip, $abs, $cdir, $rel);
            }

            $manifestColabs[] = [
                'id' => $cid,
                'nome' => $cRow['nome_colab'] ?? null,
                'nif'  => $cRow['nif'] ?? null,
                'estado' => $cRow['estado'] ?? null,
            ];
        }

        $manifest['subempreiteiros'][] = [
            'user_id' => $uid,
            'nome'    => $s['nome_empresa'],
            'nif'     => $s['nif'],
            'email'   => $s['email_contacto'],
            'docs_ok' => $d ? $isSubOk($d) : false,
            'colaboradores' => $manifestColabs,
        ];
    }

    $zip->addFromString($baseInside.'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    $zip->close();

    /* ===== 3) Output ===== */
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.$zipName.'"');
    header('Content-Length: '.filesize($zipPath));
    readfile($zipPath);

    // limpeza
    @unlink($xlsxPath);
    @unlink($zipPath);
    @rmdir($tmpDir);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["ok"=>false,"code"=>"SERVER_ERROR","detail"=>$e->getMessage()]);
}
