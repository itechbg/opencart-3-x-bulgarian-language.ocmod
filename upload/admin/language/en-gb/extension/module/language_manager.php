<?php
// Heading
$_['heading_title']             = 'Language Manager';

// Breadcrumb / menu text
$_['text_home']                 = 'Home';
$_['text_extension']            = 'Extensions';

// Section headings
$_['text_installed_languages']  = 'Installed Languages';
$_['text_add_language']         = 'Add Language(s)';
$_['text_reference_language']   = 'Reference Language';
$_['text_reference_help']       = 'Files and keys will be scaffolded from this language when a target file is missing.';

// Table columns
$_['column_name']               = 'Name';
$_['column_code']               = 'Code';
$_['column_directory']          = 'Directory';
$_['column_locale']             = 'Locale';
$_['column_status']             = 'Status';
$_['column_action']             = 'Action';

// Buttons
$_['button_add']                = 'Add Selected';
$_['button_enable']             = 'Enable';
$_['button_disable']            = 'Disable';
$_['button_scan']               = 'Scan Coverage';
$_['button_scaffold']           = 'Scaffold Missing Files';
$_['button_sync_keys']          = 'Sync Missing Keys';
$_['button_sync_keys_override'] = 'Sync & Override';

// Status labels
$_['text_enabled']              = 'Enabled';
$_['text_disabled']             = 'Disabled';
$_['text_installed']            = 'Installed';
$_['text_not_installed']        = 'Not installed';
$_['text_no_results']            = 'No results';
$_['text_select_all']            = 'Select All';
$_['text_deselect_all']          = 'Deselect All';
$_['text_confirm_scaffold']      = 'Scaffold missing files?';
$_['text_confirm_sync_keys']     = 'Sync missing keys from the selected reference language?';
$_['text_confirm_sync_keys_override'] = 'Rebuild keys from the selected reference language and overwrite existing ones?';
$_['text_action_result']         = 'Action Result';
$_['text_action_success']        = 'Completed successfully.';

// Success messages
$_['text_success_enabled']      = 'Success: Language enabled.';
$_['text_success_disabled']     = 'Success: Language disabled.';
$_['text_success_add']          = 'Success: Language(s) processed.';

// Log line templates (%s = language name / area / count)
$_['text_log_db_inserted']      = 'DB: Added "%s".';
$_['text_log_db_updated']       = 'DB: Updated "%s".';
$_['text_log_db_skipped']       = 'DB: Skipped "%s" (already current).';
$_['text_log_files_created']    = 'Files (%s): %d file(s) scaffolded.';
$_['text_log_keys_added']       = 'Keys (%s): %d key(s) added as stubs.';
$_['text_log_preset_missing']   = 'Warning: No preset found for "%s". Skipped.';

// Coverage scan labels
$_['text_scan_result']          = 'Coverage Report';
$_['text_scan_missing_files']   = 'Missing files';
$_['text_scan_missing_keys']    = 'Missing keys';
$_['text_scan_ok']              = 'Complete';
$_['text_scan_loading']         = 'Scanning…';
$_['text_files_scaffolded']      = '%d file(s) scaffolded.';
$_['text_keys_processed']        = '%d key(s) processed.';
$_['text_ajax_error']            = 'AJAX error';

// Errors
$_['error_permission']          = 'Warning: You do not have permission to manage languages!';
$_['error_no_selection']        = 'Warning: Please select at least one language.';
$_['error_invalid_reference']    = 'Warning: Please choose a valid reference language.';
$_['error_invalid_language']     = 'Warning: Please choose a valid installed language.';
$_['error_partial']             = 'Warning: Some operations completed with errors. Check the log above.';
