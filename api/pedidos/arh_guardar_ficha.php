<?php
session_start();
require_once "../includes/db.php";

// Apenas administradores podem guardar alterações
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "admin_rh") {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Acesso negado."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido."]);
    exit;
}

$em_nome = trim($_POST["emergencia_nome"] ?? '');
$em_parentesco = trim($_POST["emergencia_parentesco"] ?? '');
$em_telefone = trim($_POST["emergencia_telefone"] ?? '');


try {
    $conn = db_connect();

    $user_id = intval($_POST["user_id"]);

    $campos = [
        "nome", "email", "telefone", "morada", "codigo_postal", "freguesia",
        "concelho", "distrito", "naturalidade", "habilitacoes", "pai", "mae",
        "estado_civil", "data_nascimento", "pais", "tipo_documento", "numero_documento",
        "emitido_em", "arquivo", "validade_documento", "nif", "numero_seg_social",
        "descontos_fiscais", "reparticao_financas", "regiao", "estado_fiscal",
        "deficiencia", "conjugue_deficiente", "num_dependentes", "num_dependentes_deficientes",
        "pensionista", "data_admissao", "tipo_contrato", "profissao", "categoria",
        "regime", "horas_semana", "salario_base", "subsidio_alimentacao",
        "nib", "ordenado_liquido", "validacao_empresa"
    ];

    // Campos especiais para tratamento
    $dateFields = ["data_nascimento","emitido_em","validade_documento","data_admissao"];
    $numericMoney = ["salario_base","subsidio_alimentacao","ordenado_liquido"];

    // Listas de valores permitidos de acordo com o schema (ENUMs reais)
    $enumAllowed = [
        'estado_civil' => ['Solteiro','Casado','Viuvo','Divorciado','Uniao de Facto','Separado Judicialmente'],
        'tipo_documento' => ['CC','Titulo Residencia','Passaporte'],
        'regime' => ['Tempo Inteiro','Tempo Parcial'],
        'estado_fiscal' => ['Nao Casado','Casado 1 Titular','Casado 2 Titulares'],
        'deficiencia' => ['Nao Deficiente','Deficiente','Defic. F.Armadas']
    ];

    $booleanFields = ['conjugue_deficiente','pensionista'];

    // Função para remover acentos
    function removerAcentos($str){
        $normalize = [
            'Á'=>'A','À'=>'A','Ã'=>'A','Â'=>'A','Ä'=>'A','á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
            'É'=>'E','Ê'=>'E','Ë'=>'E','È'=>'E','é'=>'e','ê'=>'e','ë'=>'e','è'=>'e',
            'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
            'Ó'=>'O','Ò'=>'O','Õ'=>'O','Ô'=>'O','Ö'=>'O','ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
            'Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
            'Ç'=>'C','ç'=>'c'
        ];
        return strtr($str,$normalize);
    }

    function normalizarEnum($campo,$valor){
        $valorTrim = trim($valor);
        $semAcentos = removerAcentos(mb_strtolower($valorTrim));
        switch($campo){
            case 'deficiencia':
                // Mapear várias formas para os três valores do ENUM
                if($semAcentos === '' || in_array($semAcentos,["nenhum","sem","sem deficiencia","nao deficiente","nao","não"])) return 'Nao Deficiente';
                $map = [
                    'deficiente' => 'Deficiente',
                    'defic f armadas' => 'Defic. F.Armadas',
                    'defic f. armadas' => 'Defic. F.Armadas',
                    'defic f armada' => 'Defic. F.Armadas',
                    'deficfa' => 'Defic. F.Armadas',
                    'forcas armadas' => 'Defic. F.Armadas'
                ];
                return $map[$semAcentos] ?? 'Nao Deficiente';
            case 'estado_civil':
                $map = [
                    'solteiro' => 'Solteiro',
                    'casado' => 'Casado',
                    'divorciado' => 'Divorciado',
                    'viuvo' => 'Viuvo',
                    'viuva' => 'Viuvo',
                    'uniao de facto' => 'Uniao de Facto',
                    'separado judicialmente' => 'Separado Judicialmente'
                ];
                return $map[$semAcentos] ?? 'Solteiro';
            case 'tipo_documento':
                $map = [
                    'cc' => 'CC',
                    'cartao cidadao' => 'CC',
                    'cartao cidadão' => 'CC',
                    'titulo residencia' => 'Titulo Residencia',
                    'titulo de residencia' => 'Titulo Residencia',
                    'passaporte' => 'Passaporte'
                ];
                return $map[$semAcentos] ?? 'CC';
            case 'regime':
                $map = [
                    'tempo inteiro' => 'Tempo Inteiro',
                    'full-time' => 'Tempo Inteiro',
                    'full time' => 'Tempo Inteiro',
                    'tempo parcial' => 'Tempo Parcial',
                    'part-time' => 'Tempo Parcial',
                    'part time' => 'Tempo Parcial'
                ];
                return $map[$semAcentos] ?? 'Tempo Inteiro';
            case 'estado_fiscal':
                $map = [
                    'nao casado' => 'Nao Casado',
                    'não casado' => 'Nao Casado',
                    'solteiro' => 'Nao Casado',
                    'casado 1 titular' => 'Casado 1 Titular',
                    'casado um titular' => 'Casado 1 Titular',
                    'casado titular unico' => 'Casado 1 Titular',
                    'casado 2 titulares' => 'Casado 2 Titulares',
                    'casado dois titulares' => 'Casado 2 Titulares'
                ];
                return $map[$semAcentos] ?? 'Nao Casado';
        }
        return $valorTrim;
    }

    // Comprimentos máximos conhecidos (ajuste conforme BD)
    $fieldMaxLength = [
        'tipo_documento' => 30,
        'estado_civil' => 30,
        'tipo_contrato' => 50,
        'regime' => 30,
        'estado_fiscal' => 30,
        'deficiencia' => 30
    ];

    function normalizarData($v){
        if($v === '' || $v === null) return null;
        $v = trim($v);
        // já em formato ISO
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)) return $v;
        // formatos dd/mm/yyyy ou dd-mm-yyyy
        if(preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/',$v,$m)){
            return $m[3].'-'.$m[2].'-'.$m[1];
        }
        return $v; // deixar como está (pode gerar erro e será reportado)
    }

    function normalizarNumero($v){
        if($v === '' || $v === null) return null;
        $v = trim($v);
        
        // Handle different decimal formats correctly
        if(preg_match('/,\d{1,2}$/', $v)) {
            // PT format: comma as decimal separator (e.g., "1.234,56" or "6,5")
            // Remove dots (thousand separators) and convert comma to dot
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } else if(preg_match('/^\d+\.\d{1,2}$/', $v)) {
            // EN format: single dot as decimal separator, no commas (e.g., "8.5", "123.45")
            // Keep as is - it's already correct
        } else {
            // Plain integer or other format
            // Remove spaces and thousand separators but preserve decimal dots
            $v = str_replace([' ', ','], ['', ''], $v);
        }
        
        return is_numeric($v) ? $v : null;
    }

    $set = [];
    $params = [];
    foreach ($campos as $campo) {
        $set[] = "$campo = ?";
        $raw = $_POST[$campo] ?? '';
        if($raw === ''){ $params[] = null; continue; }

        // Normalizar datas
        if(in_array($campo,$dateFields)){
            $params[] = normalizarData($raw);
            continue;
        }
        // Normalizar números monetários
        if(in_array($campo,$numericMoney)){
            $params[] = normalizarNumero($raw);
            continue;
        }
        // Normalizar booleanos (0/1)
        if(in_array($campo,$booleanFields, true)){
            $val = strtolower(trim($raw));
            if(in_array($val,['1','sim','s','yes','y','true'],true)) $raw = 1; else if(in_array($val,['0','nao','não','n','no','false'],true)) $raw = 0; else $raw = ($val===''? null : 0);
            $params[] = $raw; continue;
        }

        // Validar enums
        if(isset($enumAllowed[$campo])){
            $raw = normalizarEnum($campo,$raw);
        }
        // Truncar se exceder comprimento máximo conhecido
        if($raw !== null && isset($fieldMaxLength[$campo]) && mb_strlen($raw) > $fieldMaxLength[$campo]){
            $raw = mb_substr($raw,0,$fieldMaxLength[$campo]);
        }
        $params[] = $raw;
    }

    // Guardar cópia para eventual INSERT
    $valuesForInsert = $params;

    // Verificar se já existe registo
    $checkStmt = $conn->prepare("SELECT 1 FROM colaborador_dados WHERE user_id = ? LIMIT 1");
    $checkStmt->execute([$user_id]);
    $existsRow = (bool)$checkStmt->fetchColumn();

    if($existsRow){
        $params[] = $user_id; // para WHERE
        $sql = "UPDATE colaborador_dados SET " . implode(", ", $set) . " WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    } else {
        // Construir INSERT dinâmico
        $columns = implode(", ", array_merge(['user_id'],$campos));
        $placeholders = rtrim(str_repeat('?,', count($campos)+1),',');
        $insertStmt = $conn->prepare("INSERT INTO colaborador_dados ($columns) VALUES ($placeholders)");
        $insertStmt->execute(array_merge([$user_id], $valuesForInsert));
    }

    // Guardar contacto de emergência (update ou insert)
    $stmt = $conn->prepare("SELECT id FROM contactos_emergencia WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existe = $stmt->fetchColumn();

    if ($existe) {
        $stmt = $conn->prepare("
        UPDATE contactos_emergencia
        SET nome = ?, parentesco = ?, telefone = ?
        WHERE user_id = ?
    ");
        $stmt->execute([$em_nome, $em_parentesco, $em_telefone, $user_id]);
    } else {
        $stmt = $conn->prepare("
        INSERT INTO contactos_emergencia (user_id, nome, parentesco, telefone)
        VALUES (?, ?, ?, ?)
    ");
        $stmt->execute([$user_id, $em_nome, $em_parentesco, $em_telefone]);
    }


    echo json_encode(["success" => true, "message" => "Ficha atualizada com sucesso."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro: " . $e->getMessage()]);
}
