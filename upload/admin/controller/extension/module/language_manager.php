<?php
/**
 * Language Manager - Admin Controller
 *
 * Provides the module UI under Extensions > Modules > Language Manager.
 * Supported actions:
 *  index         – main listing of installed languages + preset selector
 *  install       – install the module extension record
 *  uninstall     – uninstall the module extension record
 *  add           – add/sync one or more languages selected from presets
 *  enable        – set status = 1 for a language
 *  disable       – set status = 0 for a language
 *  scan          – AJAX: return coverage report for a language
 *  scaffold      – AJAX: create missing files for a language
 *  sync_keys     – AJAX: append/overwrite missing translation keys
 *
 * @package  LanguageManager
 */
class ControllerExtensionModuleLanguageManager extends Controller {

    private $error = [];
    private $languageDataKeys = [
        'heading_title',
        'text_home',
        'text_extension',
        'text_installed_languages',
        'text_add_language',
        'text_reference_language',
        'text_reference_help',
        'text_enabled',
        'text_disabled',
        'text_installed',
        'text_not_installed',
        'text_no_results',
        'text_select_all',
        'text_deselect_all',
        'text_confirm_scaffold',
        'text_confirm_sync_keys',
        'text_confirm_sync_keys_override',
        'text_action_result',
        'text_action_success',
        'text_scan_result',
        'text_scan_missing_files',
        'text_scan_missing_keys',
        'text_scan_ok',
        'text_scan_loading',
        'text_files_scaffolded',
        'text_keys_processed',
        'text_ajax_error',
        'column_name',
        'column_code',
        'column_directory',
        'column_locale',
        'column_status',
        'column_action',
        'button_add',
        'button_enable',
        'button_disable',
        'button_scan',
        'button_scaffold',
        'button_sync_keys',
        'button_sync_keys_override',
        'error_permission',
        'error_no_selection',
        'error_invalid_reference',
        'error_invalid_language',
        'error_partial',
    ];

    // ── Install / Uninstall ──────────────────────────────────────────────────

    public function install() {
        $this->load->model('extension/module/language_manager');
        $this->model_extension_module_language_manager->install();
    }

    public function uninstall() {
        $this->load->model('extension/module/language_manager');
        $this->model_extension_module_language_manager->uninstall();
    }

    // ── Main Index ───────────────────────────────────────────────────────────

    public function index() {
        $this->load->language('extension/module/language_manager');
        $this->load->model('extension/module/language_manager');

        $this->document->setTitle($this->language->get('heading_title'));

        $data = $this->_buildCommon();
        $data += $this->getLanguageData();

        // ── Installed languages ──────────────────────────────────────────────
        $installed   = $this->model_extension_module_language_manager->getAllLanguages();
        $presets     = $this->model_extension_module_language_manager->getPresets();
        $installedDirs = array_column($installed, 'directory');

        $data['languages'] = [];
        foreach ($installed as $lang) {
            $data['languages'][] = [
                'language_id' => $lang['language_id'],
                'name'        => $lang['name'],
                'code'        => $lang['code'],
                'directory'   => $lang['directory'],
                'locale'      => $lang['locale'],
                'status'      => $lang['status'],
                'url_enable'  => $this->url->link('extension/module/language_manager/enable', 'user_token=' . $this->session->data['user_token'] . '&language_id=' . $lang['language_id'], true),
                'url_disable' => $this->url->link('extension/module/language_manager/disable', 'user_token=' . $this->session->data['user_token'] . '&language_id=' . $lang['language_id'], true),
                'url_scan'    => $this->url->link('extension/module/language_manager/scan', 'user_token=' . $this->session->data['user_token'] . '&directory=' . $lang['directory'], true),
                'url_scaffold' => $this->url->link('extension/module/language_manager/scaffold', 'user_token=' . $this->session->data['user_token'] . '&directory=' . $lang['directory'], true),
                'url_sync_keys' => $this->url->link('extension/module/language_manager/sync_keys', 'user_token=' . $this->session->data['user_token'] . '&directory=' . $lang['directory'], true),
            ];
        }

        // ── Available presets not yet installed ──────────────────────────────
        $data['presets'] = [];
        foreach ($presets as $dir => $preset) {
            $data['presets'][$dir] = [
                'directory'   => $dir,
                'name'        => $preset['name'],
                'native_name' => $preset['native_name'],
                'installed'   => in_array($dir, $installedDirs),
            ];
        }

        // ── Form action URLs ─────────────────────────────────────────────────
        $data['url_add'] = $this->url->link('extension/module/language_manager/add', 'user_token=' . $this->session->data['user_token'], true);

        // ── Flash messages ───────────────────────────────────────────────────
        if (isset($this->session->data['success_messages'])) {
            $data['success_messages'] = (array)$this->session->data['success_messages'];
            unset($this->session->data['success_messages']);
        } else {
            $data['success_messages'] = [];
        }
        if ($this->error) {
            $existingErrors = isset($data['error_warnings']) ? $data['error_warnings'] : [];
            $escapedErrors = [];

            foreach ($this->error as $error) {
                $escapedErrors[] = $this->escapeMessage($error);
            }

            $data['error_warnings'] = array_merge($existingErrors, $escapedErrors);
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/language_manager', $data));
    }

    // ── Add languages ────────────────────────────────────────────────────────

    public function add() {
        $this->load->language('extension/module/language_manager');
        $this->load->model('extension/module/language_manager');

        if (!$this->_hasPermission()) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/module/language_manager', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }

        $selected = isset($this->request->post['selected']) ? array_unique((array)$this->request->post['selected']) : [];
        $reference = $this->getValidReference(isset($this->request->post['reference']) ? $this->request->post['reference'] : 'en-gb');

        if (empty($selected)) {
            $this->session->data['error_warning'] = $this->language->get('error_no_selection');
            $this->response->redirect($this->url->link('extension/module/language_manager', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }

        if ($reference === null) {
            $this->session->data['error_warning'] = $this->language->get('error_invalid_reference');
            $this->response->redirect($this->url->link('extension/module/language_manager', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }

        $log     = [];
        $hasError = false;

        foreach ($selected as $directory) {
            $directory = $this->normalizeLanguageDirectory($directory);
            $preset = $directory ? $this->model_extension_module_language_manager->getPreset($directory) : null;
            if (!$preset) {
                $log[] = $this->escapeMessage(sprintf($this->language->get('text_log_preset_missing'), $directory));
                $hasError = true;
                continue;
            }

            // 1. Sync DB record.
            $result = $this->model_extension_module_language_manager->syncLanguageRecord($directory, $preset);
            $log[] = $this->escapeMessage(sprintf($this->language->get('text_log_db_' . $result['action']), $preset['name']));

            // 2. Scaffold missing files.
            foreach (['admin', 'catalog'] as $area) {
                $scaffold = $this->model_extension_module_language_manager->scaffoldMissingFiles($area, $directory, $reference);
                if ($scaffold['created']) {
                    $log[] = $this->escapeMessage(sprintf($this->language->get('text_log_files_created'), $area, count($scaffold['created'])));
                }
                foreach ($scaffold['errors'] as $err) {
                    $log[] = $this->escapeMessage($err);
                    $hasError = true;
                }
            }

            // 3. Sync missing keys in all present files.
            foreach (['admin', 'catalog'] as $area) {
                $files = $this->model_extension_module_language_manager->getLanguageFiles($area, $directory);
                $totalKeys = 0;
                foreach ($files as $relFile) {
                    $sync = $this->model_extension_module_language_manager->syncMissingKeys($area, $directory, $reference, $relFile, false);
                    if ($sync['error']) {
                        $log[] = $this->escapeMessage($sync['error']);
                        $hasError = true;
                    } else {
                        $totalKeys += count($sync['appended']);
                    }
                }
                if ($totalKeys > 0) {
                    $log[] = $this->escapeMessage(sprintf($this->language->get('text_log_keys_added'), $area, $totalKeys));
                }
            }
        }

        // Store log in session.
        $this->session->data['success_messages'] = $log;
        if ($hasError) {
            $this->session->data['error_warning'] = $this->language->get('error_partial');
        }

        $this->response->redirect($this->url->link('extension/module/language_manager', 'user_token=' . $this->session->data['user_token'], true));
    }

    // ── Enable / Disable ─────────────────────────────────────────────────────

    public function enable() {
        $this->_setStatus(1);
    }

    public function disable() {
        $this->_setStatus(0);
    }

    private function _setStatus($status) {
        $this->load->language('extension/module/language_manager');
        $this->load->model('extension/module/language_manager');

        if (!$this->_hasPermission()) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        } else {
            $languageId = isset($this->request->get['language_id']) ? (int)$this->request->get['language_id'] : 0;
            if ($languageId) {
                $this->model_extension_module_language_manager->setLanguageStatus($languageId, $status);
                $this->session->data['success_messages'] = [
                    $status
                        ? $this->language->get('text_success_enabled')
                        : $this->language->get('text_success_disabled')
                ];
            }
        }

        $this->response->redirect($this->url->link('extension/module/language_manager', 'user_token=' . $this->session->data['user_token'], true));
    }

    // ── Coverage Scan (AJAX) ─────────────────────────────────────────────────

    public function scan() {
        $this->load->language('extension/module/language_manager');
        $this->load->model('extension/module/language_manager');

        if (!$this->_hasPermission()) {
            $this->jsonResponse(['error' => $this->language->get('error_permission')]);
            return;
        }

        $directory = $this->getInstalledDirectory();
        $reference = $this->getValidReference(isset($this->request->get['reference']) ? $this->request->get['reference'] : 'en-gb');

        if ($directory === null) {
            $this->jsonResponse(['error' => $this->language->get('error_invalid_language')]);
            return;
        }

        if ($reference === null) {
            $this->jsonResponse(['error' => $this->language->get('error_invalid_reference')]);
            return;
        }

        $report = $this->model_extension_module_language_manager->getCoverageReport($directory, $reference);

        // Build summary for display.
        $summary = [];
        foreach (['admin', 'catalog'] as $area) {
            $missingFiles = count($report[$area]['missing_files']);
            $missingKeys  = 0;
            foreach ($report[$area]['files'] as $fileData) {
                $missingKeys += count($fileData['missing_keys']);
            }
            $summary[$area] = [
                'missing_files' => $missingFiles,
                'missing_keys'  => $missingKeys,
                'details'       => $report[$area],
            ];
        }

        $this->jsonResponse(['success' => true, 'report' => $summary]);
    }

    // ── Scaffold Missing Files (AJAX) ────────────────────────────────────────

    public function scaffold() {
        $this->load->language('extension/module/language_manager');
        $this->load->model('extension/module/language_manager');

        if (!$this->_hasPermission()) {
            $this->jsonResponse(['error' => $this->language->get('error_permission')]);
            return;
        }

        $directory = $this->getInstalledDirectory();
        $reference = $this->getValidReference(isset($this->request->get['reference']) ? $this->request->get['reference'] : 'en-gb');

        if ($directory === null) {
            $this->jsonResponse(['error' => $this->language->get('error_invalid_language')]);
            return;
        }

        if ($reference === null) {
            $this->jsonResponse(['error' => $this->language->get('error_invalid_reference')]);
            return;
        }

        $results = [];
        foreach (['admin', 'catalog'] as $area) {
            $r = $this->model_extension_module_language_manager->scaffoldMissingFiles($area, $directory, $reference);
            $results[$area] = $r;
        }

        $this->jsonResponse(['success' => true, 'results' => $results]);
    }

    // ── Sync Missing Keys (AJAX) ─────────────────────────────────────────────

    public function sync_keys() {
        $this->load->language('extension/module/language_manager');
        $this->load->model('extension/module/language_manager');

        if (!$this->_hasPermission()) {
            $this->jsonResponse(['error' => $this->language->get('error_permission')]);
            return;
        }

        $directory = $this->getInstalledDirectory();
        $reference = $this->getValidReference(isset($this->request->get['reference']) ? $this->request->get['reference'] : 'en-gb');
        $override  = !empty($this->request->get['override']);

        if ($directory === null) {
            $this->jsonResponse(['error' => $this->language->get('error_invalid_language')]);
            return;
        }

        if ($reference === null) {
            $this->jsonResponse(['error' => $this->language->get('error_invalid_reference')]);
            return;
        }

        $totalAppended = 0;
        $errors        = [];
        foreach (['admin', 'catalog'] as $area) {
            $files = $this->model_extension_module_language_manager->getLanguageFiles($area, $directory);
            foreach ($files as $relFile) {
                $r = $this->model_extension_module_language_manager->syncMissingKeys($area, $directory, $reference, $relFile, $override);
                if ($r['error']) {
                    $errors[] = $r['error'];
                } else {
                    $totalAppended += count($r['appended']);
                }
            }
        }

        $this->jsonResponse([
            'success'        => empty($errors),
            'keys_appended'  => $totalAppended,
            'errors'         => $errors,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function _hasPermission() {
        return $this->user->hasPermission('modify', 'extension/module/language_manager');
    }

    private function _buildCommon() {
        $data = [];

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('extension/extension/module', 'user_token=' . $this->session->data['user_token'], true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/language_manager', 'user_token=' . $this->session->data['user_token'], true),
            ],
        ];

        // Pass error/success from session if present.
        if (isset($this->session->data['error_warning'])) {
            $data['error_warnings'] = (array)$this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        } else {
            $data['error_warnings'] = [];
        }

        return $data;
    }

    private function getLanguageData() {
        $data = [];

        foreach ($this->languageDataKeys as $key) {
            $data[$key] = $this->language->get($key);
        }

        return $data;
    }

    private function normalizeLanguageDirectory($directory) {
        $directory = is_string($directory) ? strtolower(trim($directory)) : '';

        if ($directory && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $directory)) {
            return $directory;
        }

        return null;
    }

    private function getInstalledDirectory() {
        $directory = $this->normalizeLanguageDirectory(isset($this->request->get['directory']) ? $this->request->get['directory'] : '');

        if (!$directory) {
            return null;
        }

        return $this->model_extension_module_language_manager->getLanguageByDirectory($directory) ? $directory : null;
    }

    private function getValidReference($reference) {
        $reference = $this->normalizeLanguageDirectory($reference);

        if (!$reference) {
            return null;
        }

        return $this->model_extension_module_language_manager->hasLanguageSource($reference) ? $reference : null;
    }

    private function jsonResponse(array $payload) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($payload));
    }

    private function escapeMessage($message) {
        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
