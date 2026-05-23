# Maiêutica — Product Overview

**Maiêutica** is a web-based clinical platform for psychological clinics and associated therapies, focused on cognitive assessment of children, progress tracking, professional management, and report generation.

**Production URL:** maieuticavaliacom.br  
**Version:** 1.0.18  
**Language:** Portuguese (pt-BR) — all UI text, validation messages, and user-facing content must be in Brazilian Portuguese.

## Core Capabilities

- **Cognitive Assessment (Denver):** Checklists with competences scored 0–3, radar charts, analysis by level/domain, longitudinal cloning for progress tracking.
- **Medical Records:** Polymorphic records (children and adults) with versioning.
- **Document Generation:** 6 document models, HTML stored in DB, PDF on demand via DomPDF.
- **Development Plans:** Auto-generated from checklist results.
- **Professional Management:** Registration, patient assignment, activation/deactivation, provisional password email.
- **User & Permission Management:** Permission-based system (93 permissions, 10 policies).
- **Dashboard:** Metrics, interactive charts, progress summaries.

## Patient Model

All patients are stored in the `kids` table. Age classification is computed from `birth_date`:
- Children: age < `Kid::ADULT_AGE_YEARS` (13)
- Adults: age >= `Kid::ADULT_AGE_YEARS`

Never use a separate "adults" table — the unified `kids` model handles both.

## Design Identity

- **Primary color:** Rose `#AD6E9B`
- **Font:** Nunito (Google Fonts), base 16px (1rem)
- **Emails:** Clean institutional templates — rose header, neutral body, no emojis.
- **PDF:** DejaVu Sans (DomPDF requirement).
