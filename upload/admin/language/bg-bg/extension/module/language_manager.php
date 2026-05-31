<?php
// Заглавие
$_['heading_title']             = 'Мениджър на езици';

// Навигация / меню
$_['text_home']                 = 'Начало';
$_['text_extension']            = 'Разширения';

// Заглавия на секции
$_['text_installed_languages']  = 'Инсталирани езици';
$_['text_add_language']         = 'Добавяне на език(ци)';
$_['text_reference_language']   = 'Референтен език';
$_['text_reference_help']       = 'Файловете и ключовете ще бъдат генерирани от този език, ако целевите файлове липсват.';

// Колони на таблицата
$_['column_name']               = 'Наименование';
$_['column_code']               = 'Код';
$_['column_directory']          = 'Директория';
$_['column_locale']             = 'Locale';
$_['column_status']             = 'Статус';
$_['column_action']             = 'Действие';

// Бутони
$_['button_add']                = 'Добави избраните';
$_['button_enable']             = 'Активирай';
$_['button_disable']            = 'Деактивирай';
$_['button_scan']               = 'Провери покритието';
$_['button_scaffold']           = 'Генерирай липсващи файлове';
$_['button_sync_keys']          = 'Синхронизирай липсващи ключове';
$_['button_sync_keys_override'] = 'Синхронизирай и презапиши';

// Статуси
$_['text_enabled']              = 'Активен';
$_['text_disabled']             = 'Неактивен';
$_['text_installed']            = 'Инсталиран';
$_['text_not_installed']        = 'Не е инсталиран';
$_['text_no_results']            = 'Няма резултати';
$_['text_select_all']            = 'Избери всички';
$_['text_deselect_all']          = 'Размаркирай всички';
$_['text_confirm_scaffold']      = 'Да се генерират ли липсващите файлове?';
$_['text_confirm_sync_keys']     = 'Да се синхронизират ли липсващите ключове от избрания референтен език?';
$_['text_confirm_sync_keys_override'] = 'Да се презапишат ли наличните ключове с тези от избрания референтен език?';
$_['text_action_result']         = 'Резултат от операцията';
$_['text_action_success']        = 'Операцията завърши успешно.';

// Съобщения за успех
$_['text_success_enabled']      = 'Успешно: Езикът е активиран.';
$_['text_success_disabled']     = 'Успешно: Езикът е деактивиран.';
$_['text_success_add']          = 'Успешно: Езикът/езиците са обработени.';

// Шаблони за лог (%s = наименование / зона / брой)
$_['text_log_db_inserted']      = 'БД: Добавен "%s".';
$_['text_log_db_updated']       = 'БД: Обновен "%s".';
$_['text_log_db_skipped']       = 'БД: Пропуснат "%s" (вече актуален).';
$_['text_log_files_created']    = 'Файлове (%s): %d файла генерирани.';
$_['text_log_keys_added']       = 'Ключове (%s): %d ключа добавени като заготовки.';
$_['text_log_preset_missing']   = 'Внимание: Не е намерен пресет за "%s". Пропуснат.';

// Резултат от сканиране
$_['text_scan_result']          = 'Доклад за покритие';
$_['text_scan_missing_files']   = 'Липсващи файлове';
$_['text_scan_missing_keys']    = 'Липсващи ключове';
$_['text_scan_ok']              = 'Пълно покритие';
$_['text_scan_loading']         = 'Сканиране…';
$_['text_files_scaffolded']      = '%d файла генерирани.';
$_['text_keys_processed']        = '%d ключа обработени.';
$_['text_ajax_error']            = 'AJAX грешка';

// Грешки
$_['error_permission']          = 'Внимание: Нямате права за управление на езици!';
$_['error_no_selection']        = 'Внимание: Моля изберете поне един език.';
$_['error_invalid_reference']    = 'Внимание: Моля изберете валиден референтен език.';
$_['error_invalid_language']     = 'Внимание: Моля изберете валиден инсталиран език.';
$_['error_partial']             = 'Внимание: Някои операции завършиха с грешки. Вижте лога по-горе.';
