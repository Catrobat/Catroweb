package org.catrobat.web.codeview;

import org.catrobat.catroid.content.bricks.Brick;
import org.catrobat.catroid.content.bricks.FormulaBrick;
import org.catrobat.catroid.formulaeditor.FormulaSpannableStringBuilder;
import org.catrobat.catroid.formulaeditor.InternToExternGenerator;
import org.catrobat.catroid.formulaeditor.InternToken;
import org.catrobat.catroid.formulaeditor.InternTokenType;
import org.catrobat.web.android.WebContext;

import java.util.ArrayList;
import java.util.List;

final class CodeFormulaParts {
	private CodeFormulaParts() {
	}

	private record Source(String value, CodeSymbols.Symbol symbol) {
		void write(CodeExport.Json json, String displayed) {
			if (symbol == null) {
				json.open('{').key("kind").value("string").key("text").value(displayed)
						.key("source").value(value).close('}');
			} else {
				symbol.writeSegment(json, displayed);
			}
		}
	}

	static void addSegments(CodeExport.Json json, Brick brick, int viewId, String displayed, CodeSymbols symbols) {
		if (!(brick instanceof FormulaBrick formulaBrick)) {
			return;
		}
		try {
			Brick.FormulaField field = formulaBrick.getBrickFieldFromTextViewId(viewId);
			if (field == null) {
				return;
			}
			List<InternToken> tokens = formulaBrick.getFormulaWithBrickField(field).getRoot().getInternTokenList();
			String prefix = "CATROWEB_TEXT_";
			while (displayed.contains(prefix)) {
				prefix += "X";
			}
			List<Source> sources = new ArrayList<>();
			for (InternToken token : tokens) {
				String value = token.getTokenStringValue();
				CodeSymbols.Symbol symbol = symbols.resolve(token);
				boolean projectText = token.getInternTokenType() == InternTokenType.STRING
						&& (field == Brick.BrickField.NOTE || field == Brick.BrickField.SPEAK)
						&& !value.isBlank() && !value.matches("#.{6}");
				if (symbol != null || projectText) {
					token.setTokenStringValue(prefix + sources.size() + "_END");
					sources.add(new Source(value, symbol));
				}
			}
			if (!sources.isEmpty()) {
				addMatchingSegments(json, displayed, prefix, tokens, sources);
			}
		} catch (RuntimeException ignored) {
			// Some bricks put their own text into the field. Keep what getView rendered.
		}
	}

	private static void addMatchingSegments(CodeExport.Json json, String displayed, String prefix,
			List<InternToken> tokens, List<Source> sources) {
		InternToExternGenerator generator = new InternToExternGenerator(WebContext.INSTANCE);
		generator.trimExternString(tokens);
		String marked = CodeExport.trim(FormulaSpannableStringBuilder.buildSpannableFormulaString(
				WebContext.INSTANCE, generator.getGeneratedExternFormulaString(), 0).toString());
		CodeExport.Json segments = new CodeExport.Json().open('[');
		StringBuilder reconstructed = new StringBuilder();
		int start = 0;
		for (int i = 0; i < sources.size(); i++) {
			String placeholder = prefix + i + "_END";
			int end = marked.indexOf(placeholder, start);
			if (end == -1) {
				return;
			}
			appendFixed(segments, reconstructed, marked.substring(start, end));
			Source source = sources.get(i);
			// Catroid's spannable formula renderer collapses runs of ASCII spaces.
			String text = source.value().replaceAll(" +", " ");
			source.write(segments, text);
			reconstructed.append(text);
			start = end + placeholder.length();
		}
		appendFixed(segments, reconstructed, marked.substring(start));
		if (displayed.contentEquals(reconstructed)) {
			json.key("segments").raw(segments.close(']'));
		}
	}

	private static void appendFixed(CodeExport.Json segments, StringBuilder reconstructed, String text) {
		if (!text.isEmpty()) {
			segments.open('{').key("kind").value("formula").key("text").value(text).close('}');
			reconstructed.append(text);
		}
	}
}
