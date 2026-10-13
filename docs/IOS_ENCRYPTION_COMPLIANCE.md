# Shelf iOS encryption compliance — procedure and draft packet

**STOPPED by owner instruction, 13 October 2026: France is excluded from planned
Apple App Store availability. Do not pursue or submit this French paperwork, send
the enquiry, request address/phone, or resume this research without new authorization.
The material below is historical preparation, superseded by that decision.**

Prepared 13 October 2026 from 7d8d5eb. **DRAFT / NOT SUBMITTED.** The earlier France-included plan is superseded. No declaration, acknowledgement, ANSSI decision or Apple approval exists
in this packet. Build 4's last verified state remains MISSING_EXPORT_COMPLIANCE.

## Applicable procedure and content protection

ANSSI's current [control page](https://cyber.gouv.fr/reglementation/reglementation-identite-confiance-numerique/controles-reglementaires-cryptographie/controle-moyen-de-cryptologie/)
points to decree 2007-663. Its Annex I, category 6 exempts equipment specially
limited to protecting software/data against illicit copying/use, with cryptographic
capability inaccessible to users. The official [government Annex I copy](https://juridoc.gouv.nc/juridoc/jdjopj.nsf/0/61D4B16F48AE5CC74B257D6D00018EB0/$File/annexe_1_au%20decret_2007-663_du_02-05-2007_CG.pdf?OpenElement=)
and [French regulator's decree copy](https://www.arcep.fr/fileadmin/reprise/textes/decrets/d2007-663.pdf)
contain that exemption. The former is dated 2014; the current ANSSI page still
identifies this decree, but a direct consolidated Legifrance fetch returned 403.
Do not infer that every application using AES is exempt.

Shelf's downloaded-book AES layer has a credible category-6 rationale: it protects
licensed content/manifests, offers no user-selected files, cipher settings or key
export, and serves account-isolated book access. This is an assessment of verified
function, **not a regulator-issued classification**. The whole app also uses HTTPS,
Keychain and SDK transport; their interaction with the category's limited-purpose
condition must be included in the enquiry. Don't classify the whole app solely from
its bookstore label. The audio-video-only category 7 does not describe encrypted
book text and Shelf also encrypts; don't claim it. A development/personal-import
exception would not establish future public supply in France.

**Correction to 7d8d5eb:** a French government filing is not necessarily required
if the content-protection exemption covers Shelf. The earlier statement based on
Apple's generic algorithm table was incomplete. If exempt, French law requires no
prior filing for the covered operation; a confirmation enquiry is prudent evidence,
not an invented statutory permission requirement. No exception is saved to Apple.

## What Apple asks for, and what is still not specified

[Apple's documentation table](https://developer.apple.com/help/app-store-connect/reference/app-information/export-compliance-documentation-for-encryption)
labels the standard non-OS algorithm / France case a French encryption declaration.
Its [submission instructions](https://developer.apple.com/help/app-store-connect/manage-app-information/determine-and-upload-app-encryption-documentation/)
require Apple document review before attaching approved documentation. Neither page
states that our unsigned draft qualifies, nor precisely whether a deposit receipt,
ANSSI final attestation or exemption letter is sufficient in the exemption case.
The exact acceptable substitute for an exempt product therefore requires Apple
confirmation; it is not established by the earlier builds' false flags.

If ANSSI says a declaration is required, the official source of the document is
[ANSSI's online declaration procedure](https://demarche.numerique.gouv.fr/commencer/declaration-relative-a-un-moyen-de-cryptologie).
The current forms page directs applicants there, rather than an obsolete paper
form. Download the portal's **260331_Notice_V3.pdf** via its notice link. Read on
13 October; SHA256 823096c6eb6c50a64220fad320410bbbda9ca9085e900256245567d19dc2360c.
It says filing produces a deposit acknowledgement first, then notification of
acceptance and a downloadable **attestation de déclaration** from ANSSI. That
final attestation, with the submitted form/technical description, is the sensible
Apple evidence packet; the claim that Apple specifically mandates only that final
attestation is not present in the public Apple pages.

| Item | Who creates it | What it establishes |
| --- | --- | --- |
| Draft form / technical annex / unsigned sign-off | Shelf | Proposed statements; no filing or official acceptance |
| Signed accuracy attestation | Owner | Owner certification of the completed application; not ANSSI approval |
| Attestation de dépôt / acknowledgement | Official portal | Receipt of a submitted file; not final accepted declaration |
| Attestation de déclaration | ANSSI through portal | Accepted declaration, identified by dossier number |
| Written exemption reply | ANSSI, if supplied | Agency's response for described scope; not automatically Apple approval |
| Apple approved encryption document/code | Apple | Apple acceptance of submitted evidence; not public-launch approval |

An accurate declaration must actually be submitted before an official deposit
acknowledgement/final attestation exists. No submission is needed under a confirmed
applicable French exemption. ANSSI's current notice describes the acceptance
sequence; do not promise immediate acceptance or turn a draft into a receipt.
Apple's separate review occurs **after** evidence is uploaded, before approved
attachment. Don't claim French export authorization or mass-market approval is
needed merely to supply the iOS app into France; that is a different operation.

## Prepared material and one practical next action

- [Technical/product annex](ios-compliance/SHELF_TECHNICAL_DRAFT.md): verified
  French technical text, commercial description, short reader/admin guide.
- [Form draft](ios-compliance/SHELF_FORM_DRAFT.md): field mapping to current notice
  and unsigned accuracy-attestation draft. It is not a copied authenticated form.
- [French enquiry draft](ios-compliance/ANSSI_ENQUIRY_DRAFT.md): asks whether category
  6 covers the app and what document ANSSI can issue for Apple.

**Next action:** after reviewing the prepared enquiry, obtain a written scope/
exemption determination from ANSSI's official procedure help contact
**controle@ssi.gouv.fr**, shown on the [official procedure landing page](https://demarche.numerique.gouv.fr/commencer/declaration-relative-a-un-moyen-de-cryptologie).
Attach the technical draft, with owner contact completed privately. Do not submit
an unnecessary declaration first. No email or enquiry is sent in this task; a
later authorized submission can be automated where access permits. If ANSSI
requires filing, use the prepared field/attachment packet at the same portal.
If exempt, obtain Apple's acceptance of the exemption evidence through its
[App Store Connect support destination](https://developer.apple.com/contact/app-store/)
before claiming the missing French declaration requirement is satisfied.

Administrative gaps are consolidated in the form draft. Owner confirmed use of
Ajmal Aand, existing owner email and country privately. Supplied address/phone text
was placeholders, not an actual address or number; those remain blank. Never put
final personal details, signatures, credentials or authentication tokens in git.
No further encryption/France decision is requested from the owner.

All purchase, real checkout and Apple sign-in/relay gates OFF; pricing-sync and
second-phone restore deferred; Apple authentication/relay checks deferred;
Android/UI pause and joint public-launch hold unchanged. No filing/provider write,
assignment, build/upload, encryption change, deployment or transaction.
