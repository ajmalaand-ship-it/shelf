import '../models/poem.dart';

enum ShareCardTheme { parchment, dark, light }

enum ShareCardScope { selection, accessiblePoem }

class ShareCardRequest {
  const ShareCardRequest({
    required this.poem,
    required this.collectionTitle,
    required this.scope,
    required this.text,
    required this.theme,
    this.fontFamily = 'Vazirmatn',
    this.fontSize = 20,
    this.includeTitle = false,
    this.isEnglish = false,
    this.isDari = false,
  });

  final PoemDetail poem;
  final String? collectionTitle;
  final ShareCardScope scope;
  final String text;
  final ShareCardTheme theme;
  final String fontFamily;
  final double fontSize;
  final bool includeTitle, isEnglish, isDari;
}

class ShareCardPage {
  const ShareCardPage({
    required this.text,
    required this.pageNumber,
    required this.pageCount,
  });

  final String text;
  final int pageNumber;
  final int pageCount;
}

class ShareLineSelection {
  const ShareLineSelection(this.start, this.end);

  final int start;
  final int end;

  int get count => end - start + 1;
}

class ShareablePoemLine {
  const ShareablePoemLine(this.text, {required this.blankLinesBefore});

  final String text;
  final int blankLinesBefore;
}

List<ShareablePoemLine> selectablePoemLines(String text) {
  final result = <ShareablePoemLine>[];
  var blankLines = 0;
  for (final line in text.split('\n')) {
    if (line.trim().isEmpty) {
      blankLines++;
      continue;
    }
    result.add(
      ShareablePoemLine(
        line,
        blankLinesBefore: result.isEmpty ? 0 : blankLines,
      ),
    );
    blankLines = 0;
  }
  return result;
}

ShareLineSelection selectContiguousLine({
  required ShareLineSelection? current,
  required int tapped,
  int maximum = 4,
}) {
  if (current == null ||
      tapped < current.start - 1 ||
      tapped > current.end + 1) {
    return ShareLineSelection(tapped, tapped);
  }
  if (tapped >= current.start && tapped <= current.end) {
    return ShareLineSelection(tapped, tapped);
  }
  final start = tapped < current.start ? tapped : current.start;
  final end = tapped > current.end ? tapped : current.end;
  if (end - start + 1 > maximum) return ShareLineSelection(tapped, tapped);
  return ShareLineSelection(start, end);
}

String selectedLineText(
  List<ShareablePoemLine> lines,
  ShareLineSelection selection,
) {
  final selected = lines.sublist(selection.start, selection.end + 1);
  final buffer = StringBuffer(selected.first.text);
  for (final line in selected.skip(1)) {
    buffer.write(List.filled(line.blankLinesBefore + 1, '\n').join());
    buffer.write(line.text);
  }
  return buffer.toString();
}
