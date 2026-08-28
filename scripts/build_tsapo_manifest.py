#!/usr/bin/env python3
"""Recover the private څپو کې انځورونه import manifest from its legacy-font PDF."""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import subprocess
import tempfile
import unicodedata
from pathlib import Path

SOURCE_SHA256 = "cb226ca44ce948ccd2c376df349eb5d11bae5e66285ecc27d77f7eded063580b"

# (start page, end page, authored title or None, number of trailing source-note lines)
STRUCTURE = [
    (4, 4, None, 0), (5, 5, None, 0), (6, 6, None, 0),
    (7, 7, "يوې شپنې ته", 0), (8, 8, None, 0), (9, 9, None, 1),
    (10, 10, None, 0), (11, 11, "ټپه", 0), (12, 12, None, 1),
    (13, 13, None, 0), (14, 14, None, 2), (15, 15, "د غاټولو ژبه", 2),
    (16, 16, None, 2), (17, 17, None, 2), (18, 18, "ښامار", 2),
    (19, 19, None, 2), (20, 20, None, 2), (21, 21, None, 2),
    (22, 22, None, 0), (23, 23, None, 2), (24, 24, "تر كوكنارګله", 2),
    (25, 25, None, 1), (26, 26, None, 0), (27, 27, None, 0),
    (28, 28, "يو ښكلي ته", 0), (29, 29, None, 2), (30, 30, None, 2),
    (31, 31, None, 2), (32, 32, None, 2), (33, 33, None, 2),
    (34, 34, None, 2), (35, 36, "بې لپې دعا", 2),
    (37, 38, "ګلاب ته ـ", 2), (39, 39, None, 2), (40, 40, None, 2),
    (41, 41, None, 2), (42, 42, None, 0), (43, 43, None, 2),
    (44, 44, None, 2), (45, 45, "ښېرې", 2), (46, 46, "خوبهـ", 2),
    (47, 47, None, 0), (48, 48, None, 2), (49, 49, None, 0),
    (50, 50, None, 0), (51, 51, None, 0), (52, 52, None, 2),
    (53, 54, "يوه پوښتنه", 2), (55, 55, None, 0), (56, 57, "هستي", 2),
    (58, 58, None, 0), (59, 59, None, 0), (60, 60, None, 0),
    (61, 61, "تور", 0), (62, 62, None, 1), (63, 63, None, 2),
    (64, 64, None, 0), (65, 65, None, 2), (66, 66, None, 0),
    (67, 67, "پېټى", 2), (68, 68, None, 1), (69, 69, None, 2),
    (70, 71, None, 2), (72, 72, None, 2), (73, 73, None, 2),
    (74, 74, None, 2), (75, 75, None, 0), (76, 76, "ژوند", 2),
    (77, 77, None, 0), (78, 78, None, 2), (79, 81, "ټوټې", 0),
    (82, 82, None, 0), (83, 83, None, 0), (84, 84, None, 0),
    (85, 87, "ماڼۍ او كيږدۍ", 1), (88, 88, "تنده", 0),
    (89, 89, None, 0), (90, 90, None, 1), (91, 91, None, 0),
    (92, 92, None, 0), (93, 93, None, 0),
]

SOURCE_NOTE_OVERRIDES = {
    12: "۱/کب/۱۳۷۷\nخوكياڼي",
    15: "۲۹/لړم/۱۳۷۸\nكوزبيار خوګياڼي",
    65: "۴/۷/۱۳۷۶\nجلال كوټ",
    70: "۴/حمل/۱۳۷۹\nپېښور",
}

VERIFIED_REPLACEMENTS = {
    "لل": "((",
    "عع": "))",
    "اهللا": "الله",
    "االله": "الله",
    "ارعنده": "ارغنده",
    "نعمې": "نغمې",
    "افعان": "افغان",
    "ښاعلو": "ښاغلو",
    "زعملى": "زغملى",
    "ځعليده": "ځغليده",
    "هغعه": "هغـه",
}


def sha256(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def font_map(pdf: bytes) -> dict[int, str]:
    obj = re.search(rb"\b494 0 obj\b(.*?)endobj", pdf, re.S)
    if not obj:
        raise RuntimeError("Pokhto font encoding object was not found")
    differences = re.search(r"Differences\[(.*?)\]", obj.group(1).decode("latin1"), re.S)
    if not differences:
        raise RuntimeError("Pokhto font differences table was not found")
    names = {
        "space": " ", "colon": ":", "hyphen": "-", "endash": "–",
        "exclam": "!", "comma": "،", "period": ".", "question": "؟",
        "semicolon": "؛", "parenright": ")", "parenleft": "(",
        "slash": "/", "asterisk": "*", "underscore": "_",
    }
    result: dict[int, str] = {}
    code = 0
    for token in re.findall(r"\d+|/[A-Za-z0-9_.]+", differences.group(1)):
        if token.isdigit():
            code = int(token)
            continue
        base = token[1:].split(".")[0]
        char = chr(int(base[1:], 16)) if re.fullmatch(r"u[0-9A-Fa-f]{4}", base) else names.get(base, "�")
        result[code] = unicodedata.normalize("NFKC", char)
        code += 1
    return result


def decode_line(raw: bytes, mapping: dict[int, str]) -> str:
    chars: list[str] = []
    index = 0
    while index < len(raw):
        byte = raw[index]
        if byte < 128:
            chars.append(chr(byte) if byte == 32 else mapping.get(byte, chr(byte)))
            index += 1
            continue
        decoded = None
        used = 1
        for length in (4, 3, 2):
            try:
                candidate = raw[index:index + length].decode("utf-8")
                if len(candidate) == 1:
                    decoded, used = candidate, length
                    break
            except UnicodeDecodeError:
                pass
        decoded = decoded or chr(byte)
        chars.append(mapping.get(ord(decoded), decoded) if ord(decoded) <= 255 else decoded)
        index += used

    text = "".join(reversed(chars)).strip()
    text = re.sub(r"[ \t]+", " ", text)
    text = re.sub(r"[٠-٩۰-۹]+", lambda match: match.group(0)[::-1], text)
    if text and set(text) == {"ج"}:
        text = "*" * len(text)
    for old, new in VERIFIED_REPLACEMENTS.items():
        text = text.replace(old, new)
    text = text.replace(" ږ ", " : ")
    for phrase in ("چېږ", "ماوېږ", "ويل يېږ", "ګورهږ", "ډالۍږ"):
        text = text.replace(phrase, phrase[:-1] + ":")
    if text == "ن":
        text = "."
    elif text.endswith(" ن"):
        text = text[:-2] + "."
    return text


def recover_pages(source: Path) -> dict[int, list[str]]:
    mapping = font_map(source.read_bytes())
    pages: dict[int, list[str]] = {}
    with tempfile.TemporaryDirectory(prefix="poetry-source-") as directory:
        for page in range(1, 95):
            output = Path(directory) / f"{page:03}.txt"
            subprocess.run([
                "gs", "-q", "-dNOPAUSE", "-dBATCH", "-sDEVICE=txtwrite",
                f"-dFirstPage={page}", f"-dLastPage={page}", f"-sOutputFile={output}", str(source),
            ], check=True)
            lines = [decode_line(line, mapping) for line in output.read_bytes().split(b"\r\n")]
            pages[page] = [line for line in lines if line and set(line) != {"ش"}]
    return pages


def poem_records(pages: dict[int, list[str]]) -> list[dict]:
    poems = []
    for sequence, (start, end, title, note_count) in enumerate(STRUCTURE, 1):
        lines = [line for page in range(start, end + 1) for line in pages[page]]
        expected_heading = title or "۞"
        if not lines or (title is not None and lines[0] != expected_heading):
            raise RuntimeError(f"Unexpected heading on page {start}: {lines[:1]}")
        if title is not None or lines[0] == "۞":
            lines = lines[1:]
        elif "۞" in lines[0]:
            lines[0] = lines[0].replace("۞", "").strip()
        else:
            raise RuntimeError(f"Untitled marker not found on page {start}: {lines[:1]}")
        source_notes = lines[-note_count:] if note_count else []
        body_lines = lines[:-note_count] if note_count else lines

        # Page 60 has a source footnote after its date/location; keep the footnote with the poem.
        if sequence == 53:
            source_notes = lines[-3:-1]
            body_lines = lines[:-3] + lines[-1:]

        source_note = SOURCE_NOTE_OVERRIDES.get(sequence, "\n".join(source_notes) or None)
        poems.append({
            "sequence": sequence,
            "title": title,
            "untitled": title is None,
            "body": "\n".join(body_lines).strip(),
            "source_page_start": start,
            "source_page_end": end,
            "source_date_place_text": source_note,
            "review_status": "verified_from_rendered_source",
            "extraction_uncertainty": None,
        })
    return poems


def build_manifest(source: Path) -> dict:
    if sha256(source) != SOURCE_SHA256:
        raise RuntimeError("Source PDF checksum does not match the approved import source")
    pages = recover_pages(source)
    poems = poem_records(pages)
    introduction = "\n".join(pages[3])
    return {
        "manifest_version": 1,
        "source": {"filename": source.name, "sha256": SOURCE_SHA256, "pdf_pages": 94},
        "collection": {
            "title": "څپو کې انځورونه",
            "author": "اجمل اند",
            "publisher": "دانش خپرندويه ټولنه",
            "collection_identity": "دويمه شعري ټولګه",
            "print_run": "۱۰۰۰ ټوکه",
            "printings": ["لومړی چاپ: ۱۳۷۹ لمريز — وږى", "دويم چاپ: ۱۳۸۱ لمريز — وږى", "دريم چاپ: ۱۳۸۷ لمريز"],
            "printing_location": "دانش كتابتون — قصه خواني بازار — پېښور",
            "dedication": "يوازې ښكلا تهـ",
            "introduction": introduction,
            "source_pages": {"bibliographic": 2, "dedication": 2, "introduction": 3},
        },
        "structure_decision": "The authored ټوټې section is one titled record spanning PDF pages 79–81; its internal ****** separators remain in the body.",
        "poems": poems,
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--source", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()
    manifest = build_manifest(args.source)
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    args.output.chmod(0o600)
    print(f"Wrote private manifest with {len(manifest['poems'])} poems.")


if __name__ == "__main__":
    main()
