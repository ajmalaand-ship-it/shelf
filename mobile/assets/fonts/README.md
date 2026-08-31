# Bundled reader fonts

- **Vazirmatn 33.003 variable (100–900)** — official Google Fonts repository (`ofl/vazirmatn`), bundled August 30, 2026. SHA256: `696249a2c74b39ffdef55de4df2809c5b639d3ff80d618d8160a095d2fd49dca`.
- **Scheherazade New 4.500 Regular/Medium/SemiBold/Bold** — official SIL/Google Fonts distribution. SHA256 Regular: `794bac8dc9e83d1d620bc471ea694f5f31d0965ce8006490a79dfc51a2d283b3`; Medium: `060b801307ec55d996d635578fffba0d95169f63e1d0f6061402185eaf4fa6af`; SemiBold: `155056d27ea5a34ab739170abeff8d45b44a494e39be329f0a4d1e6cbd173390`; Bold: `91363517a63dbc7448b5814226d33d845b26b32a461cd159ea0b3f23c7effd2d`.
- **Noto Nastaliq Urdu variable font** — official Google Fonts repository (`ofl/notonastaliqurdu`), downloaded August 28, 2026. SHA256: `98a4787f34eb6fde57fb9a1121a8f216301196ab0da98ea110cad581be8abbcd`.

All three families are licensed under SIL Open Font License 1.1; each bundled family directory contains its required `OFL.txt`. Gulzar was evaluated but rejected because the representative Pashto corpus produced missing glyphs. Noto Naskh Arabic was a complete comparison baseline, while Scheherazade New was retained for its traditional extended-Arabic design and complete tested Pashto coverage.

Each font directory contains its upstream `OFL.txt`. The fonts are bundled locally; the application does not download fonts at runtime.
