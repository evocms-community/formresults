<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\{Style, Border, Fill, Alignment};
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FormResults
{
    const VERSION = '0.2.0';

    private $corePath;
    private $params = [];
    private $lang = null;
    private $evo;
    private $table;

    public function __construct($params = [])
    {
        $this->evo   = EvolutionCMS();
        $this->table = $this->evo->getFullTablename('form_results');

        $this->params = $params;
        $this->params['config_path'] = rtrim(realpath(__DIR__ . '/../../config/'), '/') . '/';

        $this->corePath = rtrim(realpath(__DIR__ . '/../'), '/') . '/';
    }

    public function processRequest()
    {
        $forms = $this->loadForms();

        if (empty($forms)) {
            echo $this->render('not_found', [
                'path' => $this->params['config_path'],
            ]);
            return;
        }

        if (!empty($_GET['rid']) && is_numeric($_GET['rid'])) {
            echo $this->renderFormResult($forms, $_GET['rid']);
            return;
        }

        if (isset($_GET['type']) && is_string($_GET['type']) && isset($forms[ $_GET['type'] ])) {
            $form = $forms[ $_GET['type'] ];

            if (!empty($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'export': {
                        echo $this->export($form);
                        break;
                    }

                    case 'data': {
                        echo json_encode($this->getData($form, $_GET), JSON_UNESCAPED_UNICODE);
                        break;
                    }

                    case 'delete': {
                        if (!empty($_REQUEST['result_id']) && is_numeric($_REQUEST['result_id'])) {
                            echo json_encode([
                                'status' => $this->deleteResult($_REQUEST['result_id']),
                            ], JSON_UNESCAPED_UNICODE);
                        }
                        break;
                    }

                    case 'deleteall': {
                        echo json_encode([
                            'status' => $this->deleteResults($_GET['type']),
                        ], JSON_UNESCAPED_UNICODE);
                        break;
                    }
                }

                return;
            }

            echo $this->renderAllFormResults($form);
            return;
        }

        echo $this->renderForms($forms);
    }

    public function loadForms()
    {
        $forms = [];
        $hasRoles = false;

        foreach (glob($this->params['config_path'] . '*.php') as $file) {
            $data = include $file;

            if (!empty($data) && is_array($data)) {
                $alias = pathinfo($file, PATHINFO_FILENAME);
                $data['alias'] = $alias;
                $forms[$alias] = $data;

                if (isset($data['role']) || isset($data['roles'])) {
                    $hasRoles = true;
                }
            }
        }

        if ($hasRoles) {
            $user_id = $this->evo->getLoginUserID('mgr');
            $user = $this->evo->getUserInfo($user_id);

            if ($user['role'] != 1) {
                $forms = array_filter($forms, function($form) use ($user) {
                    if (isset($form['role']) && $form['role'] != $user['role']) {
                        return false;
                    }

                    if (isset($form['roles'])) {
                        if (!is_array($form['roles'])) {
                            $form['roles'] = array_map('trim', explode(',', $form['roles']));
                        }

                        if (!in_array($user['role'], $form['roles'])) {
                            return false;
                        }
                    }

                    return true;
                });
            }
        }

        uasort($forms, function($a, $b) {
            return $a['caption'] > $b['caption'];
        });

        return $forms;
    }

    private function prepareResults($form, $results)
    {
        foreach ($results as $i => &$result) {
            foreach (['fields', 'files'] as $field) {
                $result[$field] = json_decode($result[$field], true);
            }

            if (!empty($result['files']) && is_array($result['files'])) {
                foreach ($result['files'] as $name => $file) {
                    $result["file_$name"] = '<a href="/' . $file['path'] . '" target="_blank">' . htmlspecialchars($file['name']) . '</a>';
                }
            }

            foreach ($form['fields'] as $field => &$options) {
                if (!isset($options['type'])) {
                    $options['type'] = 'text';
                }

                if ($options['type'] == 'file') {
                    $value = $result['files'][$field] ?: null;
                } else {
                    $value = $result['fields'][$field] ?: null;
                }

                if (isset($options['prepare']) && is_callable($options['prepare'])) {
                    $value = call_user_func($options['prepare'], $value, $result, $this->evo);
                }

                if (!is_array($value)) {
                    $value = [$value];
                }

                if (isset($options['elements']) && is_array($options['elements'])) {
                    $value = array_map(function($val) use ($options) {
                        if (isset($options['elements'][$val])) {
                            return $options['elements'][$val];
                        }

                        return $val;
                    }, $value);
                }

                $value = implode(', ', $value);

                if ($options['type'] == 'file') {
                    $result['file_' . $field] = $value;
                } else {
                    $result['fields'][$field] = $value;
                }
            }

            $result = array_merge($result, $result['fields']);
        }

        unset($result, $options);

        return $results;
    }

    public function getModuleId()
    {
        return $this->evo->db->getValue($this->evo->db->select('id', $this->evo->getFullTablename('site_modules'), "name = 'FormResults'"));
    }

    private function renderForms($forms)
    {

        $query = $this->evo->db->query("SELECT `form_id`, COUNT(`id`) AS `results_total`, MAX(`created_at`) AS `last_result` FROM " . $this->table . " GROUP BY `form_id`");

        while ($row = $this->evo->db->getRow($query)) {
            if (isset($forms[ $row['form_id'] ])) {
                $forms[ $row['form_id'] ] += $row;
            }
        }

        return $this->render('forms_list', [
            'forms' => $forms,
        ]);
    }

    private function renderAllFormResults($form)
    {
        $results = $this->getData($form);

        $columns = [
            [
                'id'       => 'id',
                'header'   => ['#'],
                'width'    => 50,
                'template' => '<center>#id#<center>',
                'sort'     => 'string',
            ], [
                'id'     => 'created_at',
                'header' => [
                    'Дата добавления',
                    [
                        'content' => 'textFilter',
                    ],
                ],
                'width' => 145,
                'sort'  => 'string',
            ],
        ];

        foreach ($form['fields'] as $field => $options) {
            if (!empty($options['onlyfull'])) {
                continue;
            }

            $column = [
                'id' => $field,
                'header' => [
                    $options['caption'],
                ],
                'fillspace' => true,
                'adjust' => true,
            ];

            if (!empty($options['type']) && $options['type'] == 'file') {
                $column['id'] = 'file_' . $field;
            } else {
                $column['header'][] = [
                    'content' => 'textFilter',
                ];
            }

            foreach (['sort', 'width'] as $option) {
                if (isset($options[$option])) {
                    $column[$option] = $options[$option];
                }
            }

            $columns[] = $column;
        }

        $columns[] = [
            'id'     => 'id',
            'header' => [
                '<center><span class="webix_icon fa-trash-o"></span></center>',
            ],
            'width' => 50,
            'template'  => '<center><a href="#" class="btn btn-sm btn-danger fa fa-trash" onclick="removeResult(event, #id#)"></a></center>',
        ];

        return $this->render('form_results', [
            'form'    => $form,
            'columns' => $columns,
            'results' => $results,
        ]);
    }

    public function renderFormResult($forms, $result_id)
    {
        $query = $this->evo->db->select('*', $this->table, "`id` = '" . $this->evo->db->escape($result_id) . "'");

        $row = $this->evo->db->getRow($query);

        if (empty($row) || empty($forms[ $row['form_id'] ])) {
            $this->evo->sendErrorPage(true);
        }

        $form = $forms[ $row['form_id'] ];
        $rows = $this->prepareResults($form, [$row]);
        $row  = $rows[0];

        return $this->render('form_result', [
            'form'   => $form,
            'result' => $row,
        ]);
    }

    public function export($form)
    {
        $query = $this->evo->db->select('*', $this->table, "`form_id` = '{$form['alias']}'", "created_at DESC, id DESC");

        $results = $this->prepareResults($form, $this->evo->db->makeArray($query));

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', 'Дата/время');

        $col = 67;

        foreach ($form['fields'] as $field => $options) {
            $sheet->setCellValue(chr($col++) . '1', $options['caption']);
        }

        foreach ($results as $row => $result) {
            $col = 67;
            $row += 2;

            $sheet->setCellValue('A' . $row, $result['id']);
            $sheet->setCellValue('B' . $row, $result['created_at']);

            foreach ($form['fields'] as $field => $options) {
                if (!empty($options['type']) && $options['type'] == 'file') {
                    $value = $result['files'][$field]['name'] ?: '';
                } else {
                    $value = $result['fields'][$field] ?: '';
                }

                $sheet->setCellValue(chr($col++) . $row, $value);
            }
        }

        $shared = new Style();
        $shared->applyFromArray([
            'borders' => [
                'top'    => ['borderStyle' => Border::BORDER_THIN],
                'left'   => ['borderStyle' => Border::BORDER_THIN],
                'bottom' => ['borderStyle' => Border::BORDER_THIN],
                'right'  => ['borderStyle' => Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFD9D9D9'],
            ],
        ]);

        $columns = count($form['fields']) + 1;
        $sheet->duplicateStyle($shared, 'A1:' . chr(65 + $columns) . '1');

        $style = $sheet->getStyle('A1:A' . $row);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $style = $sheet->getStyle('A1');
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(7);

        for ($x = 1; $x <= $columns; $x++) {
            $sheet->getColumnDimension(chr(65 + $x))->setWidth(20);
        }

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $form['caption'] . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function deleteResult($id)
    {
        return $this->evo->db->delete($this->table, "`id` = '" . intval($id) . "'");
    }

    public function deleteResults($form_id)
    {
        return $this->evo->db->delete($this->table, "`form_id` = '" . $this->evo->db->escape($form_id) . "'");
    }

    public function catchFormResult($data, $FL)
    {
        $id = $this->evo->db->insert([
            'form_id'    => $data['formid'],
            'fields'     => json_encode($data, JSON_UNESCAPED_UNICODE),
            'created_at' => date("Y-m-d H:i:s"),
        ], $this->table);

        $files = [];
        $raw = $FL->getFormData('files');

        if (!empty($raw)) {
            $folder = 'assets/files/formresults';
            $path = MODX_BASE_PATH . $folder;

            if (!file_exists($path)) {
                mkdir($path);
            }

            foreach ($raw as $field => $file) {
                $pathinfo = pathinfo($file['name']);
                $name = $id . '_' . md5($file['name']) . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                move_uploaded_file($file['tmp_name'], $path . '/' . $name);

                $files[$field] = [
                    'name' => $file['name'],
                    'path' => $folder . '/' . $name,
                ];
            }

            $this->evo->db->update([
                'files' => json_encode($files, JSON_UNESCAPED_UNICODE),
            ], $this->table, "`id`= '$id'");
        }

        return true;
    }

    public function getData($form, $request = [])
    {
        $query = $this->evo->db->select('*', $this->table, "`form_id` = '{$form['alias']}'", "created_at DESC, id DESC");
        return $this->prepareResults($form, $this->evo->db->makeArray($query));
    }

    public function loadLang()
    {
        if ($this->lang === null) {
            $userlang = $this->evo->getConfig('manager_language');
            $_lang = [];

            $aliases = [
                'bg' => 'bulgarian',
                'zh' => 'chinese',
                'cs' => 'czech',
                'da' => 'danish',
                'en' => 'english',
                'fi' => 'finnish',
                'fr' => 'francais-utf8',
                'de' => 'german',
                'he' => 'hebrew',
                'it' => 'italian',
                'jp' => 'japanese-utf8',
                'nl' => 'nederlands-utf8',
                'no' => 'norsk',
                'fa' => 'persian',
                'pl' => 'polish-utf8',
                'pt' => 'portuguese-br-utf8',
                'ru' => 'russian-UTF8',
                'es' => 'spanish-utf8',
                'sv' => 'svenska-utf8',
                'uk' => 'ukrainian'
            ];

            if (isset($aliases[$userlang])) {
                include EVO_CORE_PATH . 'lang/' . $userlang . '/global.php';
                $userlang = $aliases[$userlang];
            } else {
                include MODX_MANAGER_PATH . 'includes/lang/' . $userlang . '.inc.php';
            }

            foreach ([$userlang, 'english'] as $l) {
                if (is_readable($this->corePath . 'lang/' . $l . '/formresults.inc.php')) {
                    $lang = include $this->corePath . 'lang/' . $l . '/formresults.inc.php';
                    break;
                }
            }

            $this->lang = array_merge($_lang, $lang);
        }

        return $this->lang;
    }

    private function render($template, $data = [])
    {
        global $content, $_style, $lastInstallTime, $modx_lang_attribute;
        $content['richtext'] = 1;

        if (!isset($_COOKIE['MODX_themeMode'])) {
            $_COOKIE['MODX_themeMode'] = '';
        }

        $modx = $this->evo;
        $managerPath = $this->evo->getManagerPath();
        $version = self::VERSION;
        $_lang  = $this->loadLang();
        $params = $this->params;
        $moduleUrl = 'index.php?a=112&id=' . $this->getModuleId();
        $canExport = class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet');

        extract($data);
        setlocale(LC_NUMERIC, 'C');

        ob_start();

        include_once MODX_MANAGER_PATH . 'includes/header.inc.php';
        include $this->corePath . 'templates/header.tpl';
        include $this->corePath . 'templates/' . $template . '.tpl';
        include $this->corePath . 'templates/footer.tpl';
        include_once MODX_MANAGER_PATH . 'includes/footer.inc.php';

        return ob_get_clean();
    }
}
