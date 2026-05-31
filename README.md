## Language Manager Module

This package now includes a **Language Manager** OpenCart module that lets you
add, enable and disable storefront languages without touching *Localisations → Languages* manually.

### Features

| Feature | Details |
|---------|---------|
| **Language presets** | 40+ languages pre-configured with correct locale strings, UTF-8 charset, date formats, decimal/thousand separators, RTL flag and flag image. |
| **One-click add** | Select any language(s) from the preset list and click *Add Selected*. The module inserts/updates the `oc_language` DB record, scaffolds missing admin & catalog language files from the reference language (default `en-gb`), and appends any missing translation keys as untranslated stubs. |
| **Enable / Disable** | Toggle language visibility without deleting the record. |
| **Coverage Scan** | Per-language AJAX report showing how many files and keys are missing compared to the reference language. |
| **Scaffold missing files** | Copy files that exist in the reference but are absent in the target. |
| **Sync missing keys** | Append missing `$_[...]` keys from the reference as stubs so nothing is broken in production. |

### Installation

1. Upload the `upload/` folder contents to your OpenCart root.
2. In the Admin panel go to **Extensions → Extensions → Modules**.
3. Find **Language Manager** and click **Install**, then **Edit**.

### File structure added

```
upload/
  admin/
    config/
      language_presets.php          ← 40+ language presets
    controller/extension/module/
      language_manager.php          ← module controller
    model/extension/module/
      language_manager.php          ← DB + filesystem logic
    view/template/extension/module/
      language_manager.twig         ← admin UI template
    language/
      en-gb/extension/module/
        language_manager.php        ← UI strings (English)
      bg-bg/extension/module/
        language_manager.php        ← UI strings (Bulgarian)
```

### Supported language presets

European: Albanian, Bulgarian, Croatian, Czech, Danish, Dutch, Estonian,
Finnish, French (FR/BE), German (DE/AT/CH), Greek, Hungarian, Italian,
Latvian, Lithuanian, Macedonian, Norwegian, Polish, Portuguese (PT/BR),
Romanian, Russian, Serbian, Slovak, Slovenian, Spanish (ES/MX), Swedish,
Turkish, Ukrainian, English (GB/US).

Middle-East / RTL: Arabic, Hebrew, Persian.

Asian: Chinese (Simplified/Traditional), Indonesian, Japanese, Korean,
Malay, Thai, Vietnamese.
