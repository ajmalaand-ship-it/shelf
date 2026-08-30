# Ajmal Aand Professional Platform — Project Constitution v1.3

**Status:** Approved and governing  
**Original approval:** July 31, 2026  
**Amendment A-001 approval:** August 1, 2026  
**Amendment A-003 approval:** August 30, 2026
**Owner and final authority:** Ajmal Aand  
**Architecture, specification and review:** ChatGPT  
**Default real-server execution agent:** OpenAI Codex CLI  
**Boundary:** Entirely separate from the existing Private Assistant project.

## 1. Constitutional decision

Build one professional and creative platform under Ajmal Aand's domain family with **four user-facing surfaces and three isolated software systems**:

### System A — Portfolio Service

1. `ajmalaand.com` — public professional portfolio.
2. `work.ajmalaand.com` — revocable, access-controlled Employer Rooms.

### System B — Career Command Center

3. `career.ajmalaand.com` — private job-search, application-preparation and career-management system with MFA.

### System C — Pashto Poetry Platform

4. `poetry.ajmalaand.com` — Pashto poetry and audio platform, Laravel API/Filament administration, and Flutter mobile applications.

Systems A, B and C use separate repositories, runtimes, databases and database users, credentials, sessions/cookies, media/storage roots, logs, backups and application processes. They may share Ajmal's dedicated server and cPanel account only if these boundaries remain enforceable.

## 2. Permanent principles

- Truth before persuasion.
- Ajmal is final authority on facts, scope, public content, sensitive answers, production releases and job submission.
- AI may discover, analyze, tailor, prepare and track; it never invents facts and never clicks Submit.
- Every career claim must reference approved Fact IDs.
- No LinkedIn or Indeed scraping, account automation, messaging or application submission.
- Use permitted public ATS GET feeds and manual imports; Ajmal submits through the official employer interface.
- Public, career-private and poetry-paid data remain isolated.
- Collect and retain the minimum necessary personal data.
- Never store SSNs, government IDs, account passwords, payment-card data or background-check documents.
- One code-editing agent works on a repository at a time.
- Structural changes require recorded change control.
- Ajmal's poetry remains Ajmal's authored work; AI must not silently rewrite it or substitute authorship.

## 3. Product goals

- Present Ajmal's verified journalism, editorial leadership, multilingual work, digital achievements, literary work and relevant AI projects professionally.
- Share role-specific materials privately without making them searchable under his name.
- Reduce repetitive job-search work while improving accuracy, consistency and follow-up.
- Track applications, interviews, referrals and outcomes.
- Learn which lanes, sources, documents and outreach methods produce results.
- Publish Ajmal's original Pashto poetry and recorded voice through an elegant, readable and commercially sustainable product.
- Demonstrate responsible applied-AI and product design without exposing private data.

## 4. Explicit non-goals

### Portfolio and Career systems

- Autonomous mass applying.
- CAPTCHA circumvention or account impersonation.
- A public job board or multi-user recruiting SaaS.
- Native mobile apps for Systems A/B during their MVPs.
- Integration with Private Assistant.
- A React/SPA frontend, Celery, vector database or browser agent without approved need.

### Poetry Version 1

- User accounts.
- Comments, likes, social feed or public profiles.
- A general publishing marketplace or multi-poet platform.
- AI voice.
- Subscriptions, advanced DRM or external-payment bypasses.
- WordPress integration.

## 5. Technology baselines

### Systems A and B

- Python 3.12.
- Django 5.2 LTS, current security patch.
- MariaDB 10.11 with a separate database/user per system.
- Server-rendered Django templates, componentized CSS and minimal JavaScript.
- cPanel-compatible production deployment confirmed during Phase 0; do not install a competing web-server stack without approval.
- systemd timers, cron or management commands for scheduled work initially.
- OpenAI Responses API with strict Structured Outputs/Pydantic schemas, configurable models and `store=false` where appropriate.
- ATS-friendly HTML-to-PDF and editable DOCX generation.

### System C

- Laravel API backend.
- Filament administration panel.
- MariaDB/MySQL with `utf8mb4`; poem body stored as `LONGTEXT`.
- Flutter mobile client; Android first, iOS later.
- RevenueCat mapped to official Google Play Billing and Apple In-App Purchase.
- Server-side entitlement verification before returning paid text or signed audio URLs.
- Ajmal's recorded voice first; AI voice excluded from Version 1.

The approved **Ajmal Aand — Pashto Poetry App Roadmap Revision 3 — Product Completion Before Release** is System C's subordinate roadmap. Revision 2 remains historical and is superseded where Revision 3 differs. This constitution controls if the documents conflict.

## 6. System boundaries

- No shared database or database user.
- No shared secret key, `.env` file or app-store/RevenueCat credential.
- No shared authentication session or parent-domain session cookie.
- No shared private media path or unrestricted public path for career documents or paid poetry/audio.
- No direct cross-system database queries in the MVP.
- Public-safe content may move only through a deliberate export/import contract or approved public API.
- Portfolio pages may link to the Poetry Platform but may not access Poetry administration, entitlement data or secrets.

## 7. Career Truth Repository

Fact statuses: `Verified`, `User Approved`, `Needs Verification`, `Context Required`, `Prohibited`, `Retired`.

Only `Verified` and correctly qualified `User Approved` facts may become application claims. Employer, title, dates, education status and work-authorization wording are locked fields. Achievements and metrics require evidence or explicit approval.

## 8. Career lanes

- Immediate Income.
- Core Media & Editorial.
- Communications, Community & Language.
- AI & Digital Transition.
- Stretch Leadership.

No universal numeric auto-reject threshold is allowed. The system must explain hard requirements, functional fit, transferable fit, evidence strength, seniority, practical fit, transition burden and strategic value. Ajmal selects `Apply Now`, `Apply`, `Maybe`, or a reasoned `Skip`.

## 9. Human-in-the-loop career workflow

Job intake → normalize → lane-aware analysis → Ajmal verdict → resume/letter plan → claim audit → Ajmal final review → official employer page → Ajmal clicks Submit → tracker/follow-up.

## 10. Poetry Platform Version 1 workflow and gates

1. **P0 — Content, identity and store setup:** app identity, real catalogue preparation, proofread Unicode poems, free samples, backups, store account verification and testers. Ajmal's original recordings are ongoing editorial content; no fixed recording count blocks Product Completion.
2. **P1 — Laravel + Filament backend:** isolated database, collections/poems/app settings, cover/audio uploads and tested HTTPS API with Pashto intact.
3. **P2 — Flutter reader:** RTL Pashto reader, bundled fonts, themes, font controls and simple offline cache tested on a real Android device.
4. **P3 — Audio:** streaming/caching, playback controls and interruption/background behavior.
5. **P4 — Share cards:** branded PNG cards, selected couplets and paginated long poems.
6. **P5 — Payments and locked content technical foundation:** one unlock-all product contract, restore purchases, server-side RevenueCat check and signed audio URLs. Real Google Play and RevenueCat configuration and sandbox acceptance may remain deferred only until P6.
7. **PC — Product Completion & Owner Acceptance:** complete and review the intended real-content product, including protected unpublished owner preview, catalogue/content resolution, user-facing completeness and real-device acceptance. Synthetic QA proves capabilities but cannot substitute for final real-content acceptance. Ajmal must explicitly accept **Product Complete / Ready for Release Preparation**.
8. **P6 — Android Release Preparation & Launch:** only after PC passes, complete current Play/RevenueCat configuration and sandbox gates, closed testing, privacy/terms, screenshots, store materials and production approval. P6 is release work, not unfinished application development.
9. **P7 — iOS launch later:** Apple account, iOS build, entitlement mapping and review.

Technical capability is not the same as finished-product acceptance. Do not jump to Flutter before the backend/API gate passes, and do not enter P6 before PC passes through explicit Ajmal approval. Recheck current Google Play, Apple and RevenueCat requirements before store work or launch.

## 11. AI and authorship controls

- Machine-consumed career AI output uses strict schemas and validation.
- Career claims must carry source Fact IDs.
- Job descriptions and uploads are untrusted data and may contain prompt injection.
- Model names belong in configuration, not business logic.
- AI output never publishes or becomes a final application document without validation and human approval.
- AI may help implement the Poetry product or draft metadata, but may not rewrite Ajmal's poems or add AI voice to Version 1.
- Any future AI voice feature requires a separate product brief, rights review, disclosure decision and Ajmal approval.

## 12. Development roles

- **Ajmal:** product owner and final authority.
- **ChatGPT:** researcher, architect, specification writer and quality reviewer; no claimed live-server access.
- **Codex CLI:** repository/terminal execution agent under sandbox, approvals and `AGENTS.md`.
- **Claude:** optional independent reviewer only when explicitly assigned; not a simultaneous editor.

Official rule:

> Ajmal approves decisions and factual content. ChatGPT designs, specifies and reviews. Codex inspects, implements and tests on the real server.

## 13. Task protocol

Every substantial task specifies:

- Task ID/title.
- Governing constitution articles.
- Goal and current evidence.
- Approved decision.
- In scope and out of scope.
- Data/migration rules.
- Security/privacy constraints.
- Acceptance criteria.
- Required tests/checks.
- Report format.
- Commit rule.

Structural work starts with inspection/report unless implementation is explicitly authorized. Codex reports files, migrations, commands, tests, failures and risks. Production deployment requires backups, migration review, checks, smoke tests and rollback readiness.

## 14. Required validation by stack

### Django repositories

- Formatter/linter/type checks configured by the repository.
- `python manage.py check`.
- `python manage.py check --deploy` when requested with production-like settings.
- Migration status and `makemigrations --check`.
- Targeted tests and relevant broader suite.
- Anonymous/authenticated smoke tests.

### Laravel backend

- `composer validate` and dependency/security checks appropriate to the task.
- Code style/static analysis configured by the repository.
- Migration review and targeted `php artisan test` coverage.
- Filament authorization and upload tests.
- Pashto `utf8mb4` round-trip tests.
- Locked/excerpt, entitlement-failure and signed-media tests.

### Flutter mobile

- `flutter analyze`.
- `flutter test` and relevant integration/widget tests.
- RTL/font coverage and real-device smoke review.
- Audio, offline cache, share-card and purchase/restore tests where affected.

Do not claim success for checks not run.

## 15. Roadmap

### Professional and Career track

0. Constitution and baseline.
1. Truth foundation.
2. Career Command MVP.
3. Public Portfolio MVP.
4. Employer Rooms.
5. Tailoring quality and evaluation.
6. Compliant discovery connectors.
7. Contacts, follow-up and interview workflow.
8. Analytics and optimization.
9. Optional assistance expansion only through amendment.

### Poetry track

P0 through P7, including the mandatory PC gate between P5 technical foundations and P6, follow Section 10 and the approved Poetry Roadmap Revision 3.

Career application relief remains urgent. Poetry work may proceed in parallel, but it must not cancel Truth Foundation, weaken security gates or silently take resources assigned to the active approved priority.

## 16. Security and data rules

- HTTPS on every host.
- MFA for Career Command Center.
- No public registration unless later approved.
- Host-only, system-specific cookies.
- Rate limiting and login security controls.
- Private career media and paid poetry/audio require application authorization.
- Upload allowlists, MIME detection, size limits and randomized storage names.
- Environment-only secrets; never print or commit them.
- App-store, RevenueCat and signing credentials never appear in client code, Git, logs, screenshots or chat.
- Original poem text and audio receive verified off-server backups and restore tests.
- Backups remain encrypted/access-restricted and separated by system.

## 17. Change control

- **Constitutional:** human-submit rule, truth policy, system separation, restricted-platform policy or final authority — formal amendment and major version when principle changes.
- **Architectural:** new framework/service/provider, browser automation, multi-user mode or shared database — Architecture Decision Record, security/data review and Ajmal approval.
- **Product:** new module/page/lifecycle or Poetry feature — approved brief and roadmap placement; amendment if a boundary changes.
- **Operational:** CSS refinement, internal refactor, copy edit or test improvement — normal task process.
- **Emergency:** minimum safe stabilization, then documentation and review.

Version 1.1 incorporated **Amendment A-001**, approved by Ajmal Aand on August 1, 2026:

> “I approve Amendment A-001, adding the Pashto Poetry Platform at poetry.ajmalaand.com as System C and updating the Ajmal Aand Professional Platform Constitution to version 1.1.”

Approved **Amendment A-002 / Constitution v1.2** remains valid. Its approved authority is recorded in this repository, but its exact full authoritative text is not locally available and is not reconstructed here.

Version 1.3 incorporates **Amendment A-003 — System C Product Completion Gate and Release Sequencing**, approved by Ajmal Aand on August 30, 2026 with the statement:

> “I approve A-003 and Roadmap Revision 3.”

A-003 is **APPROVED AND GOVERNING**. It preserves P1–P5 technical work, inserts mandatory PC Product Completion & Owner Acceptance before P6, preserves the P5 real-provider deferral until P6, and removes a fixed minimum voice-recording count as a launch blocker. It changes no architecture, security, privacy, authorship, isolation or paid-content protection boundary.

## 18. Universal Definition of Done

A feature is done only when all applicable conditions are true:

- It matches an approved brief and this constitution.
- Acceptance criteria are demonstrated.
- Code is narrowly scoped and contains no secrets.
- Migrations are reviewed and tested.
- Required automated tests pass; skips/failures are disclosed.
- Authentication, authorization, privacy and prompt-injection effects are considered.
- User-visible behavior is manually reviewed on applicable devices.
- Career AI output passes schema and claim audit.
- Career documents are checked for layout, facts and ATS simplicity.
- Poetry text/RTL and audio are tested on a real target device where applicable.
- The intended real-content Poetry product passes protected owner preview and Ajmal explicitly accepts it as Product Complete / Ready for Release Preparation before P6.
- Paid Poetry denial, purchase/reinstall and restore behavior are demonstrated before release.
- Current app-store policies, wording and disclosures are verified before submission.
- Poetry source text/audio backups are confirmed before production content changes.
- Backup, rollback, deployment and smoke-test requirements are satisfied.
- Changed files, commands, tests and limitations are reported accurately.
- Decision/changelog documentation is updated and commits are authorized.
