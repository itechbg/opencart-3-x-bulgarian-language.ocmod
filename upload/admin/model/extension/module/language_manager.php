<?php
/**
 * Language Manager - Admin Model
 *
 * Handles all database and filesystem operations for the Language Manager module.
 *
 * @package  LanguageManager
 */
class ModelExtensionModuleLanguageManager extends Model {

    // ── Database helpers ─────────────────────────────────────────────────────

    /**
     * Return all rows from oc_language ordered by sort_order.
     */
    public function getAllLanguages() {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "language`
            ORDER BY `sort_order` ASC, `name` ASC
        ");
        return $query->rows;
    }

    /**
     * Return a single language row by its directory value (e.g. 'bg-bg').
     */
    public function getLanguageByDirectory($directory) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "language`
            WHERE `directory` = '" . $this->db->escape($directory) . "'
            LIMIT 1
        ");
        return $query->row;
    }

    /**
     * Return a single language row by language_id.
     */
    public function getLanguageById($language_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "language`
            WHERE `language_id` = '" . (int)$language_id . "'
            LIMIT 1
        ");
        return $query->row;
    }

    /**
     * Insert or update an oc_language record from preset data.
     *
     * @param  string $directory  e.g. 'bg-bg'
     * @param  array  $preset     Entry from language_presets.php
     * @return array  ['action' => 'inserted'|'updated'|'skipped', 'language_id' => int]
     */
    public function syncLanguageRecord($directory, array $preset) {
        $existing = $this->getLanguageByDirectory($directory);

        $data = [
            'name'              => $this->db->escape($preset['name']),
            'code'              => $this->db->escape($preset['code']),
            'locale'            => $this->db->escape($preset['locale']),
            'image'             => $this->db->escape($preset['image']),
            'directory'         => $this->db->escape($directory),
            'filename'          => $this->db->escape($directory),
            'sort_order'        => (int)$preset['sort_order'],
            'status'            => (int)$preset['status'],
        ];

        if (!$existing) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "language`
                SET
                  `name`       = '" . $data['name'] . "',
                  `code`       = '" . $data['code'] . "',
                  `locale`     = '" . $data['locale'] . "',
                  `image`      = '" . $data['image'] . "',
                  `directory`  = '" . $data['directory'] . "',
                  `filename`   = '" . $data['filename'] . "',
                  `sort_order` = '" . $data['sort_order'] . "',
                  `status`     = '" . $data['status'] . "'
            ");
            return ['action' => 'inserted', 'language_id' => $this->db->getLastId()];
        }

        // Update only fields that may be stale (preserve existing status / sort_order if already set).
        $this->db->query("
            UPDATE `" . DB_PREFIX . "language`
            SET
              `name`       = '" . $data['name'] . "',
              `code`       = '" . $data['code'] . "',
              `locale`     = '" . $data['locale'] . "',
              `image`      = '" . $data['image'] . "',
              `filename`   = '" . $data['filename'] . "'
            WHERE `language_id` = '" . (int)$existing['language_id'] . "'
        ");
        return ['action' => 'updated', 'language_id' => (int)$existing['language_id']];
    }

    /**
     * Enable or disable a language (set status 0/1).
     *
     * @param  int  $language_id
     * @param  int  $status  1 = enabled, 0 = disabled
     */
    public function setLanguageStatus($language_id, $status) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "language`
            SET `status` = '" . (int)$status . "'
            WHERE `language_id` = '" . (int)$language_id . "'
        ");
    }

    // ── File-system helpers ──────────────────────────────────────────────────

    /**
     * Return the absolute path to the OpenCart root.
     * Works whether the admin is at /admin or a custom path.
     */
    private function getOcRoot() {
        return dirname(rtrim(DIR_APPLICATION, '/\\')) . '/';
    }

    /**
     * Collect all .php file paths (relative to language dir) for a language.
     *
     * @param  string $area       'admin' or 'catalog'
     * @param  string $directory  language directory name, e.g. 'en-gb'
     * @return array  [relative_path, ...]
     */
    public function getLanguageFiles($area, $directory) {
        $base = $this->getOcRoot() . $area . '/language/' . $directory . '/';
        if (!is_dir($base)) {
            return [];
        }
        $files = [];
        // Use RecursiveIterator for full depth.
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $rel = str_replace($base, '', $file->getPathname());
                $files[] = str_replace('\\', '/', $rel);
            }
        }
        sort($files);
        return $files;
    }

    public function hasLanguageSource($directory) {
        foreach (['admin', 'catalog'] as $area) {
            if (is_dir($this->getOcRoot() . $area . '/language/' . $directory)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scan a language directory and return an array of missing files compared
     * to a reference language (default en-gb for admin, en-gb for catalog).
     *
     * @param  string $area        'admin' or 'catalog'
     * @param  string $directory   target language directory
     * @param  string $reference   reference language directory (default 'en-gb')
     * @return array  [relative_path, ...]  files in reference but not in target
     */
    public function getMissingFiles($area, $directory, $reference = 'en-gb') {
        $refFiles    = $this->getLanguageFiles($area, $reference);
        $targetFiles = $this->getLanguageFiles($area, $directory);
        return array_values(array_diff($refFiles, $targetFiles));
    }

    /**
     * Read all $_[...] keys defined in a PHP language file.
     *
     * @param  string $filePath  Absolute path to file
     * @return array  ['key' => 'value', ...]
     */
    public function readLanguageFileKeys($filePath) {
        if (!is_readable($filePath)) {
            return [];
        }
        $_ = [];
        // Include the file in isolated scope.
        (function () use ($filePath, &$_) {
            include $filePath;
        })();
        return $_;
    }

    /**
     * Scan a single language file and return keys missing compared to reference.
     *
     * @param  string $area
     * @param  string $directory   target
     * @param  string $reference
     * @param  string $relFile     relative path, e.g. 'common/header.php'
     * @return array  [key, ...]
     */
    public function getMissingKeys($area, $directory, $reference, $relFile) {
        $root    = $this->getOcRoot();
        $refPath = $root . $area . '/language/' . $reference . '/' . $relFile;
        $tgtPath = $root . $area . '/language/' . $directory . '/' . $relFile;

        $refKeys = array_keys($this->readLanguageFileKeys($refPath));
        if (!is_readable($tgtPath)) {
            return $refKeys;
        }
        $tgtKeys = array_keys($this->readLanguageFileKeys($tgtPath));
        return array_values(array_diff($refKeys, $tgtKeys));
    }

    /**
     * Build a full coverage report for a language across admin and catalog.
     *
     * @param  string $directory
     * @param  string $reference
     * @return array  [
     *     'admin'   => ['missing_files' => [...], 'files' => ['rel' => ['missing_keys' => [...]], ...]],
     *     'catalog' => [...],
     * ]
     */
    public function getCoverageReport($directory, $reference = 'en-gb') {
        $report = [];
        foreach (['admin', 'catalog'] as $area) {
            $missingFiles = $this->getMissingFiles($area, $directory, $reference);
            $presentFiles = $this->getLanguageFiles($area, $directory);
            $fileDetails  = [];
            foreach ($presentFiles as $relFile) {
                $missing = $this->getMissingKeys($area, $directory, $reference, $relFile);
                $fileDetails[$relFile] = ['missing_keys' => $missing];
            }
            $report[$area] = [
                'missing_files' => $missingFiles,
                'files'         => $fileDetails,
            ];
        }
        return $report;
    }

    /**
     * Scaffold missing language files by copying the reference versions.
     * Only files that do NOT exist yet in the target are created.
     *
     * @param  string $area
     * @param  string $directory  target
     * @param  string $reference  source (e.g. 'en-gb')
     * @return array  ['created' => [...], 'errors' => [...]]
     */
    public function scaffoldMissingFiles($area, $directory, $reference = 'en-gb') {
        $root        = $this->getOcRoot();
        $missingFiles = $this->getMissingFiles($area, $directory, $reference);
        $created     = [];
        $errors      = [];

        foreach ($missingFiles as $relFile) {
            $src = $root . $area . '/language/' . $reference . '/' . $relFile;
            $dst = $root . $area . '/language/' . $directory . '/' . $relFile;

            // Ensure directory exists.
            $dstDir = dirname($dst);
            if (!is_dir($dstDir)) {
                if (!mkdir($dstDir, 0755, true)) {
                    $errors[] = 'Cannot create directory: ' . $dstDir;
                    continue;
                }
            }

            if (!is_readable($src)) {
                $errors[] = 'Source not readable: ' . $src;
                continue;
            }

            if (copy($src, $dst)) {
                $created[] = $relFile;
            } else {
                $errors[] = 'Copy failed: ' . $relFile;
            }
        }
        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * Append missing keys (from reference) to an existing target file.
     * Keys are written with the reference language value as a placeholder.
     *
     * @param  string $area
     * @param  string $directory
     * @param  string $reference
     * @param  string $relFile
     * @param  bool   $override   If true, overwrite existing keys too
     * @return array  ['appended' => [...], 'error' => string|null]
     */
    public function syncMissingKeys($area, $directory, $reference, $relFile, $override = false) {
        $root    = $this->getOcRoot();
        $refPath = $root . $area . '/language/' . $reference . '/' . $relFile;
        $tgtPath = $root . $area . '/language/' . $directory . '/' . $relFile;

        $refKeys = $this->readLanguageFileKeys($refPath);
        if (!$refKeys) {
            return ['appended' => [], 'error' => 'Cannot read reference file: ' . $relFile];
        }

        // Ensure target file exists.
        if (!is_file($tgtPath)) {
            $tgtDir = dirname($tgtPath);
            if (!is_dir($tgtDir)) {
                mkdir($tgtDir, 0755, true);
            }
            file_put_contents($tgtPath, "<?php\n");
        }

        if (!is_writable($tgtPath)) {
            return ['appended' => [], 'error' => 'File not writable: ' . $relFile];
        }

        $tgtKeys  = $this->readLanguageFileKeys($tgtPath);
        $appended = [];

        if ($override) {
            $lines = ["<?php\n"];

            foreach ($refKeys as $key => $refVal) {
                $lines[] = '$_[\'' . addslashes($key) . '\'] = \'' . addslashes($refVal) . "';\n";
                $appended[] = $key;
            }

            foreach ($tgtKeys as $key => $value) {
                if (!array_key_exists($key, $refKeys)) {
                    $lines[] = '$_[\'' . addslashes($key) . '\'] = \'' . addslashes($value) . "';\n";
                }
            }
        } else {
            $missingKeys = [];

            foreach ($refKeys as $key => $refVal) {
                if (!array_key_exists($key, $tgtKeys)) {
                    $missingKeys[$key] = $refVal;
                }
            }

            if (!$missingKeys) {
                return ['appended' => [], 'error' => null];
            }

            $existingContent = file_get_contents($tgtPath);
            if ($existingContent === false) {
                return ['appended' => [], 'error' => 'Cannot read target file: ' . $relFile];
            }

            $lines = [$existingContent];
            if ($existingContent !== '' && substr($existingContent, -1) !== "\n") {
                $lines[] = "\n";
            }

            $lines[] = "\n// --- auto-generated stubs (untranslated) ---\n";

            foreach ($missingKeys as $key => $refVal) {
                $lines[] = '$_[\'' . addslashes($key) . '\'] = \'' . addslashes($refVal) . "';\n";
                $appended[] = $key;
            }
        }

        if (file_put_contents($tgtPath, implode('', $lines)) === false) {
            return ['appended' => [], 'error' => 'Cannot write target file: ' . $relFile];
        }

        return ['appended' => $appended, 'error' => null];
    }

    // ── Preset helpers ───────────────────────────────────────────────────────

    /**
     * Load all language presets from the config file.
     *
     * @return array
     */
    public function getPresets() {
        $presetFile = DIR_APPLICATION . 'config/language_presets.php';
        if (is_readable($presetFile)) {
            return include $presetFile;
        }
        return [];
    }

    /**
     * Return a single preset by directory code or null if not found.
     *
     * @param  string $directory
     * @return array|null
     */
    public function getPreset($directory) {
        $presets = $this->getPresets();
        return isset($presets[$directory]) ? $presets[$directory] : null;
    }

    // ── Module install / uninstall ───────────────────────────────────────────

    public function install() {
        // Nothing to create — module uses existing oc_language table.
    }

    public function uninstall() {
        // Nothing destructive — language records are kept in oc_language.
    }
}
